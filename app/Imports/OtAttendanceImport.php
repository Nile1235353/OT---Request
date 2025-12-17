<?php

namespace App\Imports;

use App\Models\OtAttendance;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class OtAttendanceImport implements ToModel, WithHeadingRow
{
    protected $officeStartTime;
    protected $officeEndTime;
    protected $location; // Location property အသစ်ထည့်သည်

    // Controller မှ Location ပါ ပို့ပေးရမည်
    public function __construct($startTime, $endTime, $location)
    {
        $this->officeStartTime = Carbon::parse($startTime)->format('H:i:s');
        $this->officeEndTime   = Carbon::parse($endTime)->format('H:i:s');
        $this->location        = $location;
    }

    public function model(array $row)
    {
        // ID မပါရင် (သို့) Date မပါရင် ကျော်သွားမယ်
        if (!isset($row['emp_id']) || !isset($row['date'])) {
            return null;
        }

        // --- User Matching Logic Start ---
        $importedId = $row['emp_id'];

        // ၁။ အရင်ဆုံး အတိအကျတူမတူ ရှာပါ (Location ပါ တိုက်စစ်သည်)
        // ဥပမာ - Excel မှာ YTG326 လို့ပါပြီး Location မှာ Yangon ရွေးထားရင် Match ဖြစ်မယ်
        $user = User::where('finger_print_id', $importedId)
                    ->where('location', $this->location) 
                    ->first();

        // ၂။ အတိအကျမတွေ့ပါက ဂဏန်းတူညီမှု ရှိမရှိ စစ်ဆေးပါ
        if (!$user) {
            $importNum = preg_replace('/[^0-9]/', '', $importedId);

            if ($importNum) {
                // [ပြင်ဆင်ချက်] ရွေးချယ်ထားသော Location ရှိ User များထဲမှသာ ရှာဖွေခြင်း
                // ဥပမာ - Location 'Yangon' ရွေးထားရင် 'MDY326' ကို ထည့်တွက်မှာ မဟုတ်တော့ပါ
                $candidates = User::where('location', $this->location)
                                  ->where('finger_print_id', 'LIKE', '%' . $importNum)
                                  ->get();

                foreach ($candidates as $candidate) {
                    $dbNum = preg_replace('/[^0-9]/', '', $candidate->finger_print_id);

                    if (intval($dbNum) == intval($importNum)) {
                        $user = $candidate;
                        break; 
                    }
                }
            }
        }
        // --- User Matching Logic End ---
        
        $allowMorningOt = $user ? $user->morning_ot : false;

        $date = $this->transformDate($row['date']);
        $checkIn  = $this->transformTime($row['check_in']);
        $checkOut = $this->transformTime($row['check_out']);

        $otHours = $this->calculateOtHours($checkIn, $checkOut, $allowMorningOt);

        // Database တွင်ရှိသော ID (YTG326) ကို ဦးစားပေးသိမ်းမည်
        $employeeIdToSave = $user ? $user->finger_print_id : $row['emp_id'];

        return OtAttendance::updateOrCreate(
            [
                'date'        => $date,
                'employee_id' => $employeeIdToSave,
            ],
            [
                'check_in_time'   => $checkIn,
                'check_out_time'  => $checkOut,
                'actual_ot_hours' => $otHours,
                'updated_at'      => Carbon::now(),
            ]
        );
    }

    private function transformDate($value)
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformTime($value)
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    // === OT Calculation Logic (Minute-based) ===
    private function calculateOtHours($in, $out, $allowMorningOt)
    {
        if (!$in || !$out) return 0;

        $officeStartMin = $this->timeToMinutes($this->officeStartTime);
        $officeEndMin   = $this->timeToMinutes($this->officeEndTime);
        
        $checkInMin     = $this->timeToMinutes($in);
        $checkOutMin    = $this->timeToMinutes($out);

        if ($checkOutMin < $checkInMin) {
            $checkOutMin += 1440; 
        }

        $morningOt = 0;
        $eveningOt = 0;

        if ($allowMorningOt) {
            if ($checkInMin < $officeStartMin) {
                $morningOt = ($officeStartMin - $checkInMin) / 60;
            }
        }

        if ($checkOutMin > $officeEndMin) {
            $eveningOt = ($checkOutMin - $officeEndMin) / 60;
        }

        $totalOt = $morningOt + $eveningOt;

        return max(0, round($totalOt, 2));
    }

    private function timeToMinutes($timeStr)
    {
        if (!$timeStr) return 0;
        $parts = explode(':', $timeStr);
        $hours = intval($parts[0]);
        $minutes = isset($parts[1]) ? intval($parts[1]) : 0;
        
        return ($hours * 60) + $minutes;
    }
}
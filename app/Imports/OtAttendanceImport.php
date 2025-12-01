<?php

namespace App\Imports;

use App\Models\OtAttendance;
use App\Models\User; // [NEW] User Model ထည့်ရန်
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class OtAttendanceImport implements ToModel, WithHeadingRow
{
    protected $officeStartTime;
    protected $officeEndTime;

    public function __construct($startTime, $endTime)
    {
        $this->officeStartTime = Carbon::parse($startTime)->format('H:i:s');
        $this->officeEndTime   = Carbon::parse($endTime)->format('H:i:s');
    }

    public function model(array $row)
    {
        // ID မပါရင် (သို့) Date မပါရင် ကျော်သွားမယ်
        if (!isset($row['emp_id']) || !isset($row['date'])) {
            return null;
        }

        // [NEW] User ကို Employee ID ဖြင့်ရှာပြီး Morning OT ခွင့်ပြုချက် ရှိ/မရှိ စစ်ဆေးခြင်း
        $user = User::where('finger_print_id', $row['emp_id'])->first();
        
        // User မရှိရင် (သို့) morning_ot က 0 (false) ဖြစ်နေရင် Morning OT မတွက်ဘူး
        $allowMorningOt = $user ? $user->morning_ot : false;

        $date = $this->transformDate($row['date']);
        $checkIn  = $this->transformTime($row['check_in']);
        $checkOut = $this->transformTime($row['check_out']);

        // [UPDATED] calculateOtHours သို့ $allowMorningOt ပါ ထည့်ပေးလိုက်သည်
        $otHours = $this->calculateOtHours($checkIn, $checkOut, $allowMorningOt);

        return OtAttendance::updateOrCreate(
            [
                'date'        => $date,
                'employee_id' => $row['emp_id'],
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

        // ည ၁၂ ကျော်သွားတဲ့ အခြေအနေ (Check Out < Check In)
        if ($checkOutMin < $checkInMin) {
            $checkOutMin += 1440; 
        }

        $morningOt = 0;
        $eveningOt = 0;

        // ၁။ မနက်ပိုင်း OT (Permission ရှိမှ တွက်မည်)
        if ($allowMorningOt) {
            // CheckIn က OfficeStart ထက် စောရောက်နေရင်
            if ($checkInMin < $officeStartMin) {
                $morningOt = ($officeStartMin - $checkInMin) / 60;
            }
        }

        // ၂။ ညနေပိုင်း OT (ရုံးဆင်းချိန် ကျော်လွန်သော အချိန်များ)
        if ($checkOutMin > $officeEndMin) {
            $eveningOt = ($checkOutMin - $officeEndMin) / 60;
        }

        // စုစုပေါင်း OT
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
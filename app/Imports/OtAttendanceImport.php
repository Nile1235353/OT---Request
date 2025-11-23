<?php

namespace App\Imports;

use App\Models\OtAttendance;
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
        // Excel Header များနှင့် Database Field များကို ချိတ်ဆက်ခြင်း
        // CSV Header: "Emp ID" -> Slug: "emp_id"
        
        // ID မပါရင် (သို့) Date မပါရင် ကျော်သွားမယ်
        if (!isset($row['emp_id']) || !isset($row['date'])) {
            return null;
        }

        $date = $this->transformDate($row['date']);
        $checkIn  = $this->transformTime($row['check_in']);
        $checkOut = $this->transformTime($row['check_out']);

        $otHours = $this->calculateOtHours($checkIn, $checkOut);

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
            // Excel Serial Number (e.g., 45352)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            // CSV String Format
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformTime($value)
    {
        if (!$value) return null;
        try {
            // Excel Serial Number
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            // String Time
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    // === OT Calculation Logic (Minute-based) ===
    private function calculateOtHours($in, $out)
    {
        if (!$in || !$out) return 0;

        // $officeStartMin = $this->timeToMinutes($this->officeStartTime); // Morning OT မတွက်တော့လို့ မလိုတော့ပါ
        $officeEndMin   = $this->timeToMinutes($this->officeEndTime);
        
        $checkInMin     = $this->timeToMinutes($in);
        $checkOutMin    = $this->timeToMinutes($out);

        // ည ၁၂ ကျော်သွားတဲ့ အခြေအနေ (Check Out < Check In)
        if ($checkOutMin < $checkInMin) {
            $checkOutMin += 1440; 
        }

        $eveningOt = 0;

        /* ၁။ မနက်ပိုင်း OT (ဖယ်ရှားထားသည်)
           Requirement: "Check in မှာ ရှိတဲ့ ပိုတဲ့ အချိန်ကို မတွက်ချင်ပါဘူး"
           ထို့ကြောင့် Morning OT logic ကို ပိတ်ထားလိုက်ပါပြီ။
        */

        // ၂။ ညနေပိုင်း OT (ရုံးဆင်းချိန် ကျော်လွန်သော အချိန်များ)
        if ($checkOutMin > $officeEndMin) {
            $eveningOt = ($checkOutMin - $officeEndMin) / 60;
        }

        // စုစုပေါင်း OT (ညနေပိုင်း OT သီးသန့်)
        $totalOt = $eveningOt;

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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'employee_id',
        'check_in_time',
        'check_out_time',
        'actual_ot_hours',
    ];

    // employee_id အချင်းချင်း ချိတ်ဆက်ခြင်း
    public function user()
    {
        // (Related Model, Foreign Key on Current Model, Local Key on Related Model)
        return $this->belongsTo(User::class, 'employee_id', 'finger_print_id');
    }

    /**
     * Decimal OT ကို HH:MM ပုံစံသို့ ပြောင်းလဲပေးသော Accessor
     * ဥပမာ - 1.50 -> 01:30
     */

    public function getOtDurationAttribute()
    {
        // actual_ot_hours သည် database ထဲက column နာမည်နှင့် တူရပါမည်
        $hoursDecimal = (float) $this->actual_ot_hours;

        if ($hoursDecimal <= 0) {
            return '00:00';
        }

        $totalMinutes = round($hoursDecimal * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}

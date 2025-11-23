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

}

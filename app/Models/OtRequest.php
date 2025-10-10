<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtRequest extends Model
{
    //

    protected $fillable = [
        'request_id',
        'supervisor_id',
        'ot_date',
        'total_hours',
        'requirement_type',
        'reason',
        'status',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function assignedUsers()
    {
        return $this->hasMany(AssignTeam::class, 'ot_requests_id');
    }
}

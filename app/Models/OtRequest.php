<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtRequest extends Model
{
    //

    protected $fillable = [
        'request_id',
        'job_code',
        'supervisor_id',
        'ot_date',
        'start_time',
        'end_time',
        'total_hours',
        'requirement_type',
        'reason',
        'status',
        'reject_remark',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function assignedUsers()
    {
        return $this->hasMany(AssignTeam::class, 'ot_requests_id');
    }

    public function employees()
    {
        // This creates a direct many-to-many relationship to the User model,
        // using 'assign_teams' as the pivot table.
        return $this->belongsToMany(User::class, 'assign_teams', 'ot_requests_id', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\assignTeam;

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
        'customer_name',
        'reason',
        'status',
        'reject_remark',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function assignTeams()
    {
        // 'ot_requests_id' is the foreign key in the 'assign_teams' table
        return $this->hasMany(assignTeam::class, 'ot_requests_id', 'id');
    }

    public function assignedUsers()
    {
        return $this->hasMany(assignTeam::class, 'ot_requests_id');
    }

    public function employees()
    {
        // This creates a direct many-to-many relationship to the User model,
        // using 'assign_teams' as the pivot table.
        return $this->belongsToMany(User::class, 'assign_teams', 'ot_requests_id', 'user_id');
    }
}

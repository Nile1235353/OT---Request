<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class assignTeam extends Model
{
    //
    protected $fillable = [
        'ot_requests_id',
        'user_id',
        'task_description',
        'employee_status',
    ];

    // ဒီ assigned job က ဘယ် OtRequest နဲ့ဆိုင်လဲဆိုတာကို သိဖို့
    public function otRequest()
    {
        return $this->belongsTo(OtRequest::class, 'ot_requests_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

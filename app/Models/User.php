<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'password',
        'employee_id',
        'finger_print_id',
        'phone',
        'location',
        'role',
        'department',
        'position',
        'can_request_ot',
        'morning_ot',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The approvers that belong to the user.
     * ဒီ User ရဲ့ Approver များကို ရယူရန်
     */
    public function approvers(): BelongsToMany
    {
        // 'approver_user' table ကိုကြားခံပြီး user_id နဲ့ approver_id ကို ချိတ်ဆက်ပါမယ်
        return $this->belongsToMany(User::class, 'approver_user', 'user_id', 'approver_id')
                    ->withTimestamps();
    }

    /**
     * Users that this user is an approver for (Optional).
     * ဒီ User က ဘယ်သူတွေအတွက် Approver ဖြစ်နေလဲဆိုတာ ပြန်ကြည့်ချင်ရင် သုံးရန် (Reverse Relationship)
     */
    public function approvables(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'approver_user', 'approver_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Scope for filtering potential approvers.
     * Approver ဖြစ်နိုင်သူများ (Admin သို့မဟုတ် Supervisor အဆင့်အထက်) ကို ရွေးထုတ်ရန်
     */
    public function scopePotentialApprovers($query)
    {
        // Status သည် active ဖြစ်ရမည်။
        // Role သည် Admin ဖြစ်ရမည် (သို့မဟုတ်) Position သည် သတ်မှတ်ထားသော ရာထူးများ ဖြစ်ရမည်။
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->where('role', 'Admin')
                           ->orWhereIn('position', ['Supervisor', 'Assistant Supervisor', 'Manager']);
                     });
    }
}

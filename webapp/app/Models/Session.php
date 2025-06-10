<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Session extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'last_activity',
    ];

    /**
     * Get the user associated with the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the session data for the user.
     *
     * @return array<string, mixed>
     */
    public function getSessionData(): array
    {
        return [
            'user_id' => $this->user_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'last_activity' => $this->last_activity,
        ];
    }
}

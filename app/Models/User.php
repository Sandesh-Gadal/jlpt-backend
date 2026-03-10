<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\Auth\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasUuids, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'email',
        'password_hash',
        'full_name',
        'role',
        'jlpt_target_level',
        'ui_language',
        'avatar_url',
        'email_verified',
        'email_verified_at',
        'free_trial_started_at',
        'free_trial_ends_at',
        'last_login_at',
        'is_active',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'email_verified'        => 'boolean',
        'is_active'             => 'boolean',
        'email_verified_at'     => 'datetime',
        'free_trial_started_at' => 'datetime',
        'free_trial_ends_at'    => 'datetime',
        'last_login_at'         => 'datetime',
        'password_reset_expires_at' => 'datetime',
    ];

    // Tell Laravel our password field is password_hash
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ── Relationships ──────────────────────────────────────
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function streak()
    {
        return $this->hasOne(Streak::class);
    }

    // Use our custom verification email
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}

<?php

namespace Modules\Users\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Modules\Core\Application\Traits\Auditable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, Auditable, \Laravel\Sanctum\HasApiTokens;

    public function supportGroups()
    {
        return $this->belongsToMany(\Modules\Tickets\Domain\Models\TicketGroup::class, 'tickets.ticket_group_members', 'user_id', 'group_id')
            ->withPivot('is_leader')
            ->withTimestamps();
    }

    protected $guard_name = 'web';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'avatar',
        'phone',
        'language',
        'theme_mode',
        'timezone',
        'status',
        'status_reason',
        'joined_at',
        'activated_at',
        'blocked_at',
        'last_login_at',
        'two_factor_enabled',
        'profile_completion_score',
        'security_risk_level',
        'scheduled_for_deletion_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
        'security_status',
        'role_names_list',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get user initials.
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);
        return mb_strtoupper($first . $last);
    }

    /**
     * Get security status data based on score.
     */
    public function getSecurityStatusAttribute(): array
    {
        $settings = app(\Modules\Core\Application\Services\SettingsService::class);
        $score = $this->profile_completion_score;

        $thresholdLow = (int) $settings->get('profile_risk_threshold_low', 80);
        $thresholdMed = (int) $settings->get('profile_risk_threshold_medium', 50);

        if ($score >= $thresholdLow)
            return ['label' => __('core::profile.low_risk'), 'color' => 'success', 'class' => 'bg-success-subtle text-success'];
        if ($score >= $thresholdMed)
            return ['label' => __('core::profile.medium_risk'), 'color' => 'warning', 'class' => 'bg-warning-subtle text-warning'];
        return ['label' => __('core::profile.high_risk'), 'color' => 'danger', 'class' => 'bg-danger-subtle text-danger'];
    }

    /**
     * Get the user's roles display names as a comma-separated list.
     */
    public function getRoleNamesListAttribute(): string
    {
        return $this->roles->map(function ($role) {
            return $role->display_name ?? $role->name;
        })->implode(', ');
    }

    /**
     * Calculate and update the profile completion score.
     */
    public function updateSecurityScore(): void
    {
        $settings = app(\Modules\Core\Application\Services\SettingsService::class);

        $weights = [
            'first_name' => (int) $settings->get('profile_weight_first_name', 5),
            'last_name' => (int) $settings->get('profile_weight_last_name', 5),
            'email_verified_at' => (int) $settings->get('profile_weight_email_verified', 20),
            'two_factor_confirmed_at' => (int) $settings->get('profile_weight_2fa_active', 20),
            'phone' => (int) $settings->get('profile_weight_phone', 10),
            'avatar' => (int) $settings->get('profile_weight_avatar', 5),
            'language' => (int) $settings->get('profile_weight_language', 5),
            'timezone' => (int) $settings->get('profile_weight_timezone', 5),
            'last_login_at' => 5,
            'sessions_count' => (int) $settings->get('profile_weight_sessions_count', 25),
        ];

        $score = 0;
        if ($this->first_name)
            $score += $weights['first_name'];
        if ($this->last_name)
            $score += $weights['last_name'];
        if ($this->email_verified_at)
            $score += $weights['email_verified_at'];
        if ($this->two_factor_confirmed_at)
            $score += $weights['two_factor_confirmed_at'];
        if ($this->phone)
            $score += $weights['phone'];
        if ($this->avatar)
            $score += $weights['avatar'];
        if ($this->language)
            $score += $weights['language'];
        if ($this->timezone)
            $score += $weights['timezone'];
        if ($this->last_login_at)
            $score += $weights['last_login_at'];

        $sessionsCount = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->id)
            ->count();
        if ($sessionsCount > 0)
            $score += $weights['sessions_count'];

        $this->profile_completion_score = min(100, $score);

        $thresholdLow = (int) $settings->get('profile_risk_threshold_low', 80);
        $thresholdMed = (int) $settings->get('profile_risk_threshold_medium', 50);

        $this->security_risk_level = $score >= $thresholdLow ? 'low' : ($score >= $thresholdMed ? 'medium' : 'high');

        $this->saveQuietly();
    }


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
            'joined_at' => 'datetime',
            'activated_at' => 'datetime',
            'blocked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'scheduled_for_deletion_at' => 'datetime',
        ];
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences()
    {
        return $this->hasMany(\Modules\Core\Domain\Models\NotificationPreference::class);
    }

    /**
     * Get enabled notification channels for a specific event type.
     */
    public function getEnabledChannels(string $eventType): array
    {
        $preference = $this->notificationPreferences()
            ->where([
                'event_type' => $eventType,
                'enabled' => true
            ])
            ->first();

        if (!$preference) {
            // Return default channels if no preference exists
            $defaults = \Modules\Core\Domain\Models\NotificationPreference::getDefaultPreferences();
            return $defaults[$eventType] ?? ['database'];
        }

        return $preference->channels ?? [];
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.' . $this->id;
    }
}
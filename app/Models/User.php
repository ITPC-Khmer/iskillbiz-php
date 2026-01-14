<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'phone',
        'gender',
        'dob',
        'profile',
        'email',
        'password',
        'facebook_id',
        'last_login_at',
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
            'dob' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Check if the user is connected to Facebook.
     *
     * @return bool
     */
    public function isConnectedToFacebook(): bool
    {
        return !is_null($this->facebook_id);
    }

    /**
     * Get the user's last login date formatted.
     *
     * @return string|null
     */
    public function getLastLoginFormatted(): ?string
    {
        return $this->last_login_at?->diffForHumans();
    }

    /**
     * Update the user's last login timestamp.
     *
     * @return bool
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login_at' => now()]);
    }

    /**
     * Validate user creation data.
     *
     * @param array $data
     * @return array<string, string>
     */
    public static function getValidationRules(string $context = 'create'): array
    {
        $baseRules = [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ];

        if ($context === 'update') {
            $baseRules['email'] = 'required|email|unique:users,email';
        }

        return $baseRules;
    }
}

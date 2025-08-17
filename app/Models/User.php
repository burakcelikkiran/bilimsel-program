<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Kullanıcının bağlı olduğu organizasyonlar
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Kullanıcının oluşturduğu etkinlikler
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Admin kontrolü
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Organizatör kontrolü
     */
    public function isOrganizer(): bool
    {
        return $this->role === 'organizer' || $this->isAdmin();
    }

    /**
     * Editör kontrolü
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor' || $this->isOrganizer();
    }

    /**
     * Aktif kullanıcılar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Belirli bir organizasyonda yetkili mi?
     */
    public function hasAccessToOrganization(Organization $organization): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->organizations()->where('organizations.id', $organization->id)->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Organizasyona bağlı kullanıcılar
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_users')
                    ->withPivot('role')
                    ->withTimestamps()
                    ->wherePivotNull('deleted_at');
    }

    /**
     * Organizasyona bağlı etkinlikler
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Organizasyona bağlı katılımcılar
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Organizasyona bağlı sponsorlar
     */
    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    /**
     * SCOPES
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('contact_email', 'like', "%{$term}%");
        });
    }

    /**
     * RELATIONSHIP SCOPES
     */
    public function organizers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'organizer');
    }

    public function editors(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'editor');
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst(trim($value));
    }

    /**
     * HELPER METHODS
     */
    public function hasActiveEvents(): bool
    {
        return $this->events()->where('is_published', true)->exists();
    }

    public function getTotalEventsCount(): int
    {
        return $this->events()->count();
    }

    public function getActiveEventsCount(): int
    {
        return $this->events()->where('is_published', true)->count();
    }

    /**
     * Route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['logo_url'] = $this->logo_url;
        $array['total_events'] = $this->getTotalEventsCount();
        $array['active_events'] = $this->getActiveEventsCount();
        return $array;
    }
}
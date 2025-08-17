<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'logo',
        'website',
        'contact_email',
        'sponsor_level',
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
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
    }

    /**
     * RELATIONSHIPS
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function programSessions(): HasMany
    {
        return $this->hasMany(ProgramSession::class);
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(Presentation::class);
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

    public function scopeByLevel($query, $level)
    {
        return $query->where('sponsor_level', $level);
    }

    public function scopePlatinum($query)
    {
        return $query->where('sponsor_level', 'platinum');
    }

    public function scopeGold($query)
    {
        return $query->where('sponsor_level', 'gold');
    }

    public function scopeSilver($query)
    {
        return $query->where('sponsor_level', 'silver');
    }

    public function scopeBronze($query)
    {
        return $query->where('sponsor_level', 'bronze');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('contact_email', 'like', "%{$term}%")
              ->orWhere('website', 'like', "%{$term}%");
        });
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getFormattedLevelAttribute(): string
    {
        return match($this->sponsor_level) {
            'platinum' => 'Platin',
            'gold' => 'Altın',
            'silver' => 'Gümüş',
            'bronze' => 'Bronz',
            default => ucfirst($this->sponsor_level)
        };
    }

    public function getLevelColorAttribute(): string
    {
        return match($this->sponsor_level) {
            'platinum' => '#E5E7EB', // Gray-200
            'gold' => '#FCD34D',     // Yellow-300
            'silver' => '#9CA3AF',   // Gray-400
            'bronze' => '#CD7C2F',   // Orange-600
            default => '#6B7280'     // Gray-500
        };
    }

    public function getLevelOrderAttribute(): int
    {
        return match($this->sponsor_level) {
            'platinum' => 1,
            'gold' => 2,
            'silver' => 3,
            'bronze' => 4,
            default => 5
        };
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper(trim($value));
    }

    public function setWebsiteAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            $value = 'https://' . $value;
        }
        $this->attributes['website'] = $value;
    }

    /**
     * HELPER METHODS
     */
    public function canBeDeleted(): bool
    {
        return !$this->programSessions()->exists() && !$this->presentations()->exists();
    }

    public function duplicate(array $overrides = []): self
    {
        $data = $this->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        
        $data = array_merge($data, $overrides);
        $data['name'] = $data['name'] . ' (Kopya)';
        
        return static::create($data);
    }

    public static function generateUniqueSlug(string $name, int $organizationId, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('organization_id', $organizationId)
                    ->where('slug', $slug)
                    ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
                    ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
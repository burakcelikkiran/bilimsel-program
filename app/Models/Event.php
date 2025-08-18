<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'title', 
        'slug',
        'description',
        'start_date',
        'end_date',
        'timezone',
        'venue_address',
        'contact_email',
        'contact_phone',
        'website_url',
        'is_published',
        'registration_enabled',
        'max_attendees',
        'user_id',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
        'registration_enabled' => 'boolean',
        'max_attendees' => 'integer',
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

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->getOriginal('slug'));
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function eventDays(): HasMany
    {
        return $this->hasMany(EventDay::class)->orderBy('date');
    }

    public function programSessionCategories(): HasMany
    {
        return $this->hasMany(ProgramSessionCategory::class)->orderBy('sort_order');
    }

    /**
     * Get all venues through event days
     */
    public function venues(): HasManyThrough
    {
        return $this->hasManyThrough(Venue::class, EventDay::class)
            ->orderBy('event_days.date')
            ->orderBy('venues.sort_order');
    }

    /**
     * Get all program sessions through event days and venues
     */
    public function programSessions()
    {
        return ProgramSession::query()
            ->whereIn('venue_id', function ($query) {
                $query->select('venues.id')
                    ->from('venues')
                    ->join('event_days', 'venues.event_day_id', '=', 'event_days.id')
                    ->where('event_days.event_id', $this->id);
            })
            ->orderBy('start_time')
            ->orderBy('sort_order');
    }

    /**
     * SCOPES
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_published) {
            if ($this->isPast()) {
                return 'completed';
            } elseif ($this->isOngoing()) {
                return 'ongoing';
            } else {
                return 'upcoming';
            }
        }
        return 'draft';
    }

    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('d M Y');
        }

        return $this->start_date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst(trim($value));
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = static::createSlugFromTurkish($value);
    }

    /**
     * HELPER METHODS
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function isPast(): bool
    {
        return $this->end_date < now();
    }

    public function canBePublished(): bool
    {
        return $this->eventDays()->exists() &&
            $this->eventDays()->whereHas('venues')->exists();
    }

    public function getTotalSessions(): int
    {
        return $this->programSessions()->count();
    }

    public function getTotalPresentations(): int
    {
        return $this->programSessions()
            ->withCount('presentations')
            ->get()
            ->sum('presentations_count');
    }

    public function getTotalVenues(): int
    {
        return $this->venues()->count();
    }

    public function getTotalParticipants(): int
    {
        $moderatorIds = $this->programSessions()
            ->with('moderators')
            ->get()
            ->flatMap->moderators
            ->pluck('id')
            ->unique();

        $speakerIds = $this->programSessions()
            ->with('presentations.speakers')
            ->get()
            ->flatMap->presentations
            ->flatMap->speakers
            ->pluck('id')
            ->unique();

        return $moderatorIds->merge($speakerIds)->unique()->count();
    }

    /**
     * Route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Generate unique slug
     */
    public static function generateUniqueSlug(string $name, ?string $currentSlug = null): string
    {
        $slug = static::createSlugFromTurkish($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($currentSlug, fn($q) => $q->where('slug', '!=', $currentSlug))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create URL-friendly slug from Turkish text
     */
    public static function createSlugFromTurkish(string $text): string
    {
        // Türkçe karakterleri dönüştür
        $turkishChars = [
            'ş' => 's', 'Ş' => 'S',
            'ğ' => 'g', 'Ğ' => 'G',
            'ü' => 'u', 'Ü' => 'U',
            'ö' => 'o', 'Ö' => 'O',
            'ı' => 'i', 'İ' => 'I',
            'ç' => 'c', 'Ç' => 'C'
        ];

        $text = strtr($text, $turkishChars);
        
        // Laravel'in slug metodunu kullan
        return Str::slug($text);
    }

    /**
     * Generate event days data array for bulk insert
     */
    public function generateEventDaysData(): array
    {
        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $eventDays = [];
        $currentDate = $startDate->copy();
        
        for ($dayNumber = 1; $dayNumber <= $totalDays; $dayNumber++) {
            $eventDays[] = [
                'event_id' => $this->id,
                'display_name' => $totalDays > 1 ? "{$dayNumber}. Gün" : $this->name,
                'date' => $currentDate->toDateString(),
                'sort_order' => $dayNumber,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $currentDate->addDay();
        }
        
        return $eventDays;
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['status'] = $this->status;
        $array['duration'] = $this->duration;
        $array['formatted_date_range'] = $this->formatted_date_range;
        $array['total_sessions'] = $this->getTotalSessions();
        $array['total_presentations'] = $this->getTotalPresentations();
        $array['total_venues'] = $this->getTotalVenues();
        $array['total_participants'] = $this->getTotalParticipants();
        return $array;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}

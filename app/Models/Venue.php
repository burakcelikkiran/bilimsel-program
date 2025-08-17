<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_day_id',
        'name',
        'display_name',
        'capacity',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
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
            // UUID kısmını kaldırdık - normal auto-increment ID kullanıyoruz
            if (is_null($model->sort_order)) {
                $model->sort_order = static::where('event_day_id', $model->event_day_id)->max('sort_order') + 1;
            }
            if (empty($model->color)) {
                $model->color = static::generateRandomColor();
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function eventDay(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function programSessions(): HasMany
    {
        return $this->hasMany(ProgramSession::class)->orderBy('start_time')->orderBy('sort_order');
    }

    /**
     * SCOPES
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByEventDay($query, $eventDayId)
    {
        return $query->where('event_day_id', $eventDayId);
    }

    public function scopeWithCapacity($query)
    {
        return $query->whereNotNull('capacity');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('display_name', 'like', "%{$term}%");
        });
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getFormattedCapacityAttribute(): string
    {
        return $this->capacity ? number_format($this->capacity) . ' kişi' : 'Belirtilmemiş';
    }

    public function getHasCapacityAttribute(): bool
    {
        return !is_null($this->capacity) && $this->capacity > 0;
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = ucfirst(trim($value));
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::slug(trim($value));
    }

    public function setColorAttribute($value)
    {
        // Ensure color is valid hex
        $color = ltrim($value, '#');
        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        if (strlen($color) === 6 && ctype_xdigit($color)) {
            $this->attributes['color'] = '#' . strtoupper($color);
        } else {
            $this->attributes['color'] = '#3B82F6'; // Default blue
        }
    }

    /**
     * HELPER METHODS
     */
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

    public function getSessionsDuration(): int
    {
        return $this->programSessions->sum(function ($session) {
            return $session->getDurationInMinutes();
        });
    }

    public function hasActiveSessions(): bool
    {
        return $this->programSessions()->exists();
    }

    public function getOccupancyRate(): float
    {
        if (!$this->capacity) {
            return 0;
        }

        $totalCapacityNeeded = $this->programSessions->sum(function ($session) {
            return $session->estimated_attendance ?? 0;
        });

        return min(($totalCapacityNeeded / $this->capacity) * 100, 100);
    }

    /**
     * Generate random color for venue
     */
    public static function generateRandomColor(): string
    {
        $colors = [
            '#3B82F6', // Blue
            '#10B981', // Emerald
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#8B5CF6', // Violet
            '#06B6D4', // Cyan
            '#84CC16', // Lime
            '#F97316', // Orange
            '#EC4899', // Pink
            '#6366F1', // Indigo
        ];

        return $colors[array_rand($colors)];
    }

    /**
     * Reorder venues
     */
    public static function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            static::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Check time conflicts for sessions
     */
    public function hasTimeConflict($startTime, $endTime, $excludeSessionId = null): bool
    {
        return $this->programSessions()
                   ->when($excludeSessionId, fn($q) => $q->where('id', '!=', $excludeSessionId))
                   ->where(function ($query) use ($startTime, $endTime) {
                       $query->where(function ($q) use ($startTime, $endTime) {
                           // New session starts during existing session
                           $q->where('start_time', '<=', $startTime)
                             ->where('end_time', '>', $startTime);
                       })->orWhere(function ($q) use ($startTime, $endTime) {
                           // New session ends during existing session
                           $q->where('start_time', '<', $endTime)
                             ->where('end_time', '>=', $endTime);
                       })->orWhere(function ($q) use ($startTime, $endTime) {
                           // New session encompasses existing session
                           $q->where('start_time', '>=', $startTime)
                             ->where('end_time', '<=', $endTime);
                       });
                   })
                   ->exists();
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['formatted_capacity'] = $this->formatted_capacity;
        $array['has_capacity'] = $this->has_capacity;
        $array['total_sessions'] = $this->getTotalSessions();
        $array['total_presentations'] = $this->getTotalPresentations();
        $array['sessions_duration'] = $this->getSessionsDuration();
        $array['occupancy_rate'] = $this->getOccupancyRate();
        return $array;
    }
}
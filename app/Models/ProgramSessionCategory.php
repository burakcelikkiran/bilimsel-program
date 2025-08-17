<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProgramSessionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'color',
        'sort_order',
    ];

    protected $casts = [
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
                $model->sort_order = static::where('event_id', $model->event_id)->max('sort_order') + 1;
            }
            if (empty($model->color)) {
                $model->color = static::generateRandomColor();
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function programSessions(): BelongsToMany
    {
        return $this->belongsToMany(ProgramSession::class, 'program_session_category_assignments')
                    ->withTimestamps();
    }

    /**
     * SCOPES
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeWithSessions($query)
    {
        return $query->whereHas('programSessions');
    }

    public function scopeWithoutSessions($query)
    {
        return $query->whereDoesntHave('programSessions');
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst(trim($value));
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
            $this->attributes['color'] = '#10B981'; // Default emerald
        }
    }

    public function getSessionsCountAttribute(): int
    {
        return $this->programSessions()->count();
    }

    public function getPresentationsCountAttribute(): int
    {
        return $this->programSessions()
                   ->withCount('presentations')
                   ->get()
                   ->sum('presentations_count');
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

    public function hasActiveSessions(): bool
    {
        return $this->programSessions()->exists();
    }

    public function getSessionsByDay(): array
    {
        $sessionsByDay = [];
        
        $this->programSessions()
             ->with(['venue.eventDay'])
             ->get()
             ->groupBy('venue.event_day_id')
             ->each(function ($sessions, $dayId) use (&$sessionsByDay) {
                 $day = $sessions->first()->venue->eventDay;
                 $sessionsByDay[$day->display_name] = $sessions;
             });

        return $sessionsByDay;
    }

    public function canBeDeleted(): bool
    {
        return !$this->hasActiveSessions();
    }

    /**
     * Generate random color for category
     */
    public static function generateRandomColor(): string
    {
        $colors = [
            '#10B981', // Emerald
            '#3B82F6', // Blue
            '#8B5CF6', // Violet
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#06B6D4', // Cyan
            '#84CC16', // Lime
            '#F97316', // Orange
            '#EC4899', // Pink
            '#6366F1', // Indigo
            '#14B8A6', // Teal
            '#A855F7', // Purple
        ];

        return $colors[array_rand($colors)];
    }

    /**
     * Reorder categories
     */
    public static function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            static::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Predefined categories for common events
     */
    public static function getPredefinedCategories(): array
    {
        return [
            [
                'name' => 'Ana Oturumlar',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Uydu Sempozyumları',
                'color' => '#10B981',
            ],
            [
                'name' => 'Sözlü Bildiri Oturumları',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Turkish Archives of Pediatrics',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Genç Pediatristler',
                'color' => '#EF4444',
            ],
            [
                'name' => 'Konsültasyon Saati',
                'color' => '#06B6D4',
            ],
            [
                'name' => 'En İyi Bildiriler',
                'color' => '#F97316',
            ],
        ];
    }

    /**
     * Create predefined categories for an event
     */
    public static function createPredefinedForEvent($eventId): void
    {
        $predefined = static::getPredefinedCategories();
        
        foreach ($predefined as $index => $category) {
            static::create([
                'event_id' => $eventId,
                'name' => $category['name'],
                'color' => $category['color'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['sessions_count'] = $this->sessions_count;
        $array['presentations_count'] = $this->presentations_count;
        $array['total_sessions'] = $this->getTotalSessions();
        $array['total_presentations'] = $this->getTotalPresentations();
        return $array;
    }
}
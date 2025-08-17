<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'first_name',
        'last_name',
        'title',
        'affiliation',
        'email',
        'phone',
        'bio',
        'photo',
        'is_speaker',
        'is_moderator',
    ];

    protected $casts = [
        'is_speaker' => 'boolean',
        'is_moderator' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * RELATIONSHIPS
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function moderatedSessions(): BelongsToMany
    {
        return $this->belongsToMany(ProgramSession::class, 'program_session_moderators')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('program_session_moderators.sort_order'); // Tablo adını belirttik
    }

    public function presentations(): BelongsToMany
    {
        return $this->belongsToMany(Presentation::class, 'presentation_speakers')
            ->withPivot(['speaker_role', 'sort_order'])
            ->withTimestamps()
            ->orderBy('presentation_speakers.sort_order'); // Tablo adını belirttik
    }

    /**
     * SCOPES
     */
    public function scopeSpeakers($query)
    {
        return $query->where('is_speaker', true);
    }

    public function scopeModerators($query)
    {
        return $query->where('is_moderator', true);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('affiliation', 'like', "%{$term}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
                ->orWhereRaw("CONCAT(title, ' ', first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
        });
    }

    public function scopeWithTitle($query, $title)
    {
        return $query->where('title', 'like', "%{$title}%");
    }

    public function scopeFromAffiliation($query, $affiliation)
    {
        return $query->where('affiliation', 'like', "%{$affiliation}%");
    }

    /**
     * ACCESSORS & MUTATORS
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim($this->first_name . ' ' . $this->last_name);
        return $this->title ? $this->title . ' ' . $fullName : $fullName;
    }

    public function getNameWithTitleAttribute(): string
    {
        return $this->full_name;
    }

    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_name ? substr($this->first_name, 0, 1) : '';
        $lastInitial = $this->last_name ? substr($this->last_name, 0, 1) : '';
        return strtoupper($firstInitial . $lastInitial);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getHasPhotoAttribute(): bool
    {
        return !empty($this->photo);
    }

    public function getShortBioAttribute(): string
    {
        if (!$this->bio) {
            return '';
        }
        return Str::limit($this->bio, 150);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst(trim($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst(trim($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value ? ucwords(trim($value)) : null;
    }

    public function setAffiliationAttribute($value)
    {
        $this->attributes['affiliation'] = $value ? ucfirst(trim($value)) : null;
    }

    /**
     * HELPER METHODS
     */
    public function getTotalSessions(): int
    {
        return $this->moderatedSessions()->count();
    }

    public function getTotalPresentations(): int
    {
        return $this->presentations()->count();
    }

    public function getTotalParticipations(): int
    {
        return $this->getTotalSessions() + $this->getTotalPresentations();
    }

    public function getPrimaryPresentations(): int
    {
        return $this->presentations()->wherePivot('speaker_role', 'primary')->count();
    }

    public function getCoSpeakerPresentations(): int
    {
        return $this->presentations()->wherePivot('speaker_role', 'co_speaker')->count();
    }

    public function getDiscussantPresentations(): int
    {
        return $this->presentations()->wherePivot('speaker_role', 'discussant')->count();
    }

    public function hasParticipationInEvent($eventId): bool
    {
        $hasSessions = $this->moderatedSessions()
            ->whereHas('venue.eventDay', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->exists();

        $hasPresentations = $this->presentations()
            ->whereHas('programSession.venue.eventDay', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->exists();

        return $hasSessions || $hasPresentations;
    }

    public function getParticipationsByEvent($eventId): array
    {
        $sessions = $this->moderatedSessions()
            ->whereHas('venue.eventDay', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->with(['venue.eventDay'])
            ->get();

        $presentations = $this->presentations()
            ->whereHas('programSession.venue.eventDay', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->with(['programSession.venue.eventDay'])
            ->get();

        return [
            'moderated_sessions' => $sessions,
            'presentations' => $presentations,
            'total_count' => $sessions->count() + $presentations->count()
        ];
    }

    public function hasParticipations(): bool
    {
        return $this->moderatedSessions()->exists() || $this->presentations()->exists();
    }

    public function canBeDeleted(): bool
    {
        return !$this->hasParticipations();
    }

    /**
     * Static helper methods
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', strtolower($email))->first();
    }

    public static function findByName(string $firstName, string $lastName): ?self
    {
        return static::where('first_name', 'like', "%{$firstName}%")
            ->where('last_name', 'like', "%{$lastName}%")
            ->first();
    }

    /**
     * Import/Export helpers
     */
    public function toExportArray(): array
    {
        return [
            'full_name' => $this->full_name,
            'email' => $this->email,
            'affiliation' => $this->affiliation,
            'phone' => $this->phone,
            'is_speaker' => $this->is_speaker ? 'Evet' : 'Hayır',
            'is_moderator' => $this->is_moderator ? 'Evet' : 'Hayır',
            'total_sessions' => $this->getTotalSessions(),
            'total_presentations' => $this->getTotalPresentations(),
        ];
    }

    /**
     * JSON representation
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['full_name'] = $this->full_name;
        $array['name_with_title'] = $this->name_with_title;
        $array['initials'] = $this->initials;
        $array['photo_url'] = $this->photo_url;
        $array['has_photo'] = $this->has_photo;
        $array['short_bio'] = $this->short_bio;
        $array['total_sessions'] = $this->getTotalSessions();
        $array['total_presentations'] = $this->getTotalPresentations();
        $array['total_participations'] = $this->getTotalParticipations();
        return $array;
    }
}
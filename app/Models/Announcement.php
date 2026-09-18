<?php

namespace App\Models;

use App\Support\ProgramSessionTypeMapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'body',
        'program_session_id',
        'published_at',
        'push_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'push_sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function programSession(): BelongsTo
    {
        return $this->belongsTo(ProgramSession::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * @return array{id: string, Baslik: string, Mesaj: string, Tarih: string|null, OturumID: string|null}
     */
    public function toMobilePayload(): array
    {
        return [
            'id' => (string) $this->id,
            'Baslik' => $this->title,
            'Mesaj' => $this->body,
            'Tarih' => $this->published_at?->toIso8601String(),
            'OturumID' => $this->program_session_id
                ? ProgramSessionTypeMapper::sessionUuid((int) $this->program_session_id)
                : null,
        ];
    }
}

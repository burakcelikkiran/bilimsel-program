<?php

namespace App\Models;

use App\Enums\EventPageKey;
use Database\Factories\EventPageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPage extends Model
{
    /** @use HasFactory<EventPageFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'key',
        'content',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'key' => EventPageKey::class,
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

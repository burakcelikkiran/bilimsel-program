<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EventDay extends Model
{
    use HasFactory, SoftDeletes;

    // Gerçek veritabanı kolonlarına göre güncellendi
    protected $fillable = [
        'event_id',
        'date',
        'display_name',  // Veritabanında bu alan var ve zorunlu
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Model başlatma
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->sort_order)) {
                $model->sort_order = static::where('event_id', $model->event_id)->max('sort_order') + 1;
            }
            if (is_null($model->is_active)) {
                $model->is_active = true;
            }
        });

        static::deleting(function ($model) {
            // Salonlar ve program oturumları cascade delete
            $model->venues()->delete();
        });
    }

    /**
     * İLİŞKİLER
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class)->orderBy('sort_order');
    }

    // EventDay'e bağlı program oturumları (salonlar üzerinden)
    public function programSessions()
    {
        return $this->hasManyThrough(
            ProgramSession::class,
            Venue::class,
            'event_day_id', // venues tablosundaki foreign key
            'venue_id',     // program_sessions tablosundaki foreign key  
            'id',           // event_days tablosundaki local key
            'id'            // venues tablosundaki local key
        )->orderBy('start_time')->orderBy('sort_order');
    }

    // EventDay'e bağlı sunumlar (salonlar ve oturumlar üzerinden)
    public function presentations()
    {
        return Presentation::whereHas('programSession.venue', function ($query) {
            $query->where('event_day_id', $this->id);
        });
    }

    // Alternatif olarak daha performanslı versiyon:
    public function presentationsOptimized()
    {
        return Presentation::query()
            ->join('program_sessions', 'presentations.program_session_id', '=', 'program_sessions.id')
            ->join('venues', 'program_sessions.venue_id', '=', 'venues.id')
            ->where('venues.event_day_id', $this->id)
            ->whereNull('presentations.deleted_at')
            ->whereNull('program_sessions.deleted_at')
            ->whereNull('venues.deleted_at')
            ->select('presentations.*');
    }

    /**
     * SCOPE'LAR
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('date');
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBugun($query)
    {
        return $query->where('date', now()->toDateString());
    }

    public function scopeGelecek($query)
    {
        return $query->where('date', '>', now()->toDateString());
    }

    public function scopeGecmis($query)
    {
        return $query->where('date', '<', now()->toDateString());
    }

    public function scopeAra($query, $terim)
    {
        return $query->where(function ($q) use ($terim) {
            $q->where('date', 'like', "%{$terim}%");
        });
    }

    /**
     * ACCESSOR & MUTATOR'LAR
     */
    public function getGoruntulemeAdiAttribute(): string
    {
        return $this->display_name ?: "Gün " . ($this->sort_order ?: 1);
    }

    public function getFormatliBolumAttribute(): string
    {
        return $this->date ? $this->date->format('d M Y') : '';
    }

    public function getHaftaGunuAttribute(): string
    {
        if (!$this->date) return '';
        
        $gunler = [
            'Monday' => 'Pazartesi',
            'Tuesday' => 'Salı', 
            'Wednesday' => 'Çarşamba',
            'Thursday' => 'Perşembe',
            'Friday' => 'Cuma',
            'Saturday' => 'Cumartesi',
            'Sunday' => 'Pazar'
        ];
        
        return $gunler[$this->date->format('l')] ?? '';
    }

    public function getTamTarihAttribute(): string
    {
        if (!$this->date) return '';
        
        $aylar = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
        ];
        
        return $this->date->day . ' ' . $aylar[$this->date->month] . ' ' . $this->date->year;
    }

    /**
     * YARDIMCI METODLAR
     */
    public function bugunMu(): bool
    {
        return $this->date && $this->date->isToday();
    }

    public function gelecekteMi(): bool
    {
        return $this->date && $this->date->isFuture();
    }

    public function gecmisteMi(): bool
    {
        return $this->date && $this->date->isPast();
    }

    public function toplamOturumSayisi(): int
    {
        return $this->programSessions()->count();
    }

    public function toplamSunumSayisi(): int
    {
        return $this->programSessions()
                    ->withCount('presentations')
                    ->get()
                    ->sum('presentations_count');
    }

    public function salonSayisi(): int
    {
        return $this->venues()->count();
    }

    public function zamanDilimleri()
    {
        return $this->programSessions()
                    ->select('start_time')
                    ->distinct()
                    ->orderBy('start_time')
                    ->pluck('start_time')
                    ->map(function ($time) {
                        return Carbon::parse($time)->format('H:i');
                    });
    }

    /**
     * Türkçe durum metinleri
     */
    public function getDurumMetniAttribute(): string
    {
        if ($this->bugunMu()) {
            return 'Bugün';
        } elseif ($this->gelecekteMi()) {
            return 'Yaklaşan';
        } else {
            return 'Tamamlandı';
        }
    }

    public function getAktiflikDurumuAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Pasif';
    }
}
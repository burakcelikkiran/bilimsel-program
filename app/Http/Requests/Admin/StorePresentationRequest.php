<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProgramSession;
use App\Models\Participant;

class StorePresentationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Admin can create presentations for any session
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the session's event
        if ($this->program_session_id) {
            $session = ProgramSession::with('venue.eventDay.event')->find($this->program_session_id);
            if ($session) {
                return $user->organizations()
                           ->where('organizations.id', $session->venue->eventDay->event->organization_id)
                           ->exists();
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'program_session_id' => [
                'required',
                'exists:program_sessions,id',
                function ($attribute, $value, $fail) {
                    $session = ProgramSession::with('venue.eventDay.event')->find($value);
                    if (!$session) {
                        $fail('Seçilen oturum bulunamadı.');
                        return;
                    }
                    
                    // Check if session is a break
                    if ($session->is_break) {
                        $fail('Ara oturumlarına sunum eklenemez.');
                        return;
                    }
                    
                    // Check if user has access to this session's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $session->venue->eventDay->event->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu oturuma sunum ekleme yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'title' => [
                'required',
                'string',
                'max:500',
                'min:5',
            ],
            
            'abstract' => [
                'nullable',
                'string',
                'max:5000',
            ],
            
            'start_time' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (!$value || !$this->program_session_id) return;
                    
                    $session = ProgramSession::find($this->program_session_id);
                    if (!$session) return;
                    
                    $sessionStart = \Carbon\Carbon::createFromFormat('H:i', $session->start_time->format('H:i'));
                    $sessionEnd = \Carbon\Carbon::createFromFormat('H:i', $session->end_time->format('H:i'));
                    $presentationStart = \Carbon\Carbon::createFromFormat('H:i', $value);
                    
                    if ($presentationStart->lt($sessionStart) || $presentationStart->gte($sessionEnd)) {
                        $fail('Sunum başlangıç saati oturum zaman aralığında olmalıdır (' . 
                              $session->start_time->format('H:i') . ' - ' . 
                              $session->end_time->format('H:i') . ').');
                    }
                }
            ],
            
            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    if (!$value || !$this->start_time || !$this->program_session_id) return;
                    
                    $session = ProgramSession::find($this->program_session_id);
                    if (!$session) return;
                    
                    $sessionEnd = \Carbon\Carbon::createFromFormat('H:i', $session->end_time->format('H:i'));
                    $presentationEnd = \Carbon\Carbon::createFromFormat('H:i', $value);
                    
                    if ($presentationEnd->gt($sessionEnd)) {
                        $fail('Sunum bitiş saati oturum bitiş saatinden sonra olamaz (' . 
                              $session->end_time->format('H:i') . ').');
                    }
                    
                    // Check for time conflicts with other presentations in the same session
                    $start = $this->start_time;
                    $conflicts = \App\Models\Presentation::where('program_session_id', $this->program_session_id)
                        ->where(function ($query) use ($start, $value) {
                            $query->where(function ($q) use ($start, $value) {
                                $q->where('start_time', '<', $value)
                                  ->where('end_time', '>', $start);
                            });
                        })
                        ->exists();
                    
                    if ($conflicts) {
                        $fail('Bu zaman diliminde aynı oturumda başka bir sunum bulunmaktadır.');
                    }
                }
            ],
            
            'presentation_type' => [
                'required',
                'in:keynote,oral,case_presentation,symposium,poster,workshop,panel',
            ],
            
            'language' => [
                'nullable',
                'string',
                'max:10',
                'in:tr,en,fr,de,es,it,ar',
            ],
            
            'sponsor_id' => [
                'nullable',
                'exists:sponsors,id',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    $sponsor = \App\Models\Sponsor::find($value);
                    if (!$sponsor || !$sponsor->is_active) {
                        $fail('Seçilen sponsor aktif değil.');
                        return;
                    }
                    
                    // Check if sponsor belongs to the same organization
                    $user = $this->user();
                    if (!$user->isAdmin() && $this->program_session_id) {
                        $session = ProgramSession::with('venue.eventDay.event')->find($this->program_session_id);
                        if ($session && $sponsor->organization_id !== $session->venue->eventDay->event->organization_id) {
                            $fail('Sponsor aynı organizasyona ait olmalıdır.');
                        }
                    }
                }
            ],
            
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],
            
            'speakers' => [
                'nullable',
                'array',
                'min:1',
                'max:8', // Maximum 8 speakers per presentation
            ],
            
            'speakers.*.participant_id' => [
                'required',
                'exists:participants,id',
                function ($attribute, $value, $fail) {
                    $participant = Participant::find($value);
                    if (!$participant || !$participant->is_speaker) {
                        $fail('Seçilen katılımcı konuşmacı olarak tanımlanmamış.');
                        return;
                    }
                    
                    // Check if participant belongs to the same organization
                    $user = $this->user();
                    if (!$user->isAdmin() && $this->program_session_id) {
                        $session = ProgramSession::with('venue.eventDay.event')->find($this->program_session_id);
                        if ($session && $participant->organization_id !== $session->venue->eventDay->event->organization_id) {
                            $fail('Konuşmacı aynı organizasyona ait olmalıdır.');
                        }
                    }
                }
            ],
            
            'speakers.*.speaker_role' => [
                'required',
                'in:primary,secondary,moderator',
            ],
            
            'speakers.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:99',
            ],
            
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            
            'is_published' => [
                'boolean',
            ],
            
            'duration_minutes' => [
                'nullable',
                'integer',
                'min:5',
                'max:240', // Max 4 hours
                function ($attribute, $value, $fail) {
                    if (!$value || !$this->start_time || !$this->end_time) return;
                    
                    $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
                    $calculatedDuration = $start->diffInMinutes($end);
                    
                    if (abs($calculatedDuration - $value) > 5) {
                        $fail('Süre, başlangıç ve bitiş saatleriyle uyumlu olmalıdır (hesaplanan: ' . 
                              $calculatedDuration . ' dakika).');
                    }
                }
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'program_session_id.required' => 'Oturum seçimi zorunludur.',
            'program_session_id.exists' => 'Seçilen oturum bulunamadı.',
            
            'title.required' => 'Sunum başlığı zorunludur.',
            'title.string' => 'Sunum başlığı metin formatında olmalıdır.',
            'title.max' => 'Sunum başlığı en fazla 500 karakter olabilir.',
            'title.min' => 'Sunum başlığı en az 5 karakter olmalıdır.',
            
            'abstract.string' => 'Özet metin formatında olmalıdır.',
            'abstract.max' => 'Özet en fazla 5000 karakter olabilir.',
            
            'start_time.date_format' => 'Başlangıç saati SS:DD formatında olmalıdır.',
            'end_time.date_format' => 'Bitiş saati SS:DD formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            
            'presentation_type.required' => 'Sunum türü seçimi zorunludur.',
            'presentation_type.in' => 'Geçersiz sunum türü seçildi.',
            
            'language.string' => 'Dil kodu metin formatında olmalıdır.',
            'language.max' => 'Dil kodu en fazla 10 karakter olabilir.',
            'language.in' => 'Geçersiz dil seçildi.',
            
            'sponsor_id.exists' => 'Seçilen sponsor bulunamadı.',
            
            'sort_order.integer' => 'Sıralama sayı formatında olmalıdır.',
            'sort_order.min' => 'Sıralama 0\'dan küçük olamaz.',
            'sort_order.max' => 'Sıralama 999\'dan büyük olamaz.',
            
            'speakers.array' => 'Konuşmacı listesi dizi formatında olmalıdır.',
            'speakers.min' => 'En az 1 konuşmacı seçilmelidir.',
            'speakers.max' => 'En fazla 8 konuşmacı seçilebilir.',
            'speakers.*.participant_id.required' => 'Konuşmacı seçimi zorunludur.',
            'speakers.*.participant_id.exists' => 'Seçilen konuşmacı bulunamadı.',
            'speakers.*.speaker_role.required' => 'Konuşmacı rolü seçimi zorunludur.',
            'speakers.*.speaker_role.in' => 'Geçersiz konuşmacı rolü seçildi.',
            'speakers.*.sort_order.integer' => 'Konuşmacı sıralaması sayı formatında olmalıdır.',
            
            'notes.string' => 'Notlar metin formatında olmalıdır.',
            'notes.max' => 'Notlar en fazla 1000 karakter olabilir.',
            
            'is_published.boolean' => 'Yayın durumu doğru/yanlış değer olmalıdır.',
            
            'duration_minutes.integer' => 'Süre sayı formatında olmalıdır.',
            'duration_minutes.min' => 'Süre en az 5 dakika olmalıdır.',
            'duration_minutes.max' => 'Süre en fazla 240 dakika (4 saat) olabilir.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'program_session_id' => 'oturum',
            'title' => 'sunum başlığı',
            'abstract' => 'özet',
            'start_time' => 'başlangıç saati',
            'end_time' => 'bitiş saati',
            'presentation_type' => 'sunum türü',
            'language' => 'dil',
            'sponsor_id' => 'sponsor',
            'sort_order' => 'sıralama',
            'speakers' => 'konuşmacılar',
            'notes' => 'notlar',
            'is_published' => 'yayın durumu',
            'duration_minutes' => 'süre',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data before validation
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'abstract' => $this->abstract ? trim($this->abstract) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
            'language' => $this->language ?: 'tr', // Default to Turkish
            'is_published' => $this->boolean('is_published', true),
            'sort_order' => $this->sort_order ? (int) $this->sort_order : null,
            'duration_minutes' => $this->duration_minutes ? (int) $this->duration_minutes : null,
        ]);

        // Calculate duration from start/end times if not provided
        if ($this->start_time && $this->end_time && !$this->duration_minutes) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
            $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
            $this->merge(['duration_minutes' => $start->diffInMinutes($end)]);
        }

        // Remove empty values from speakers array
        if ($this->has('speakers') && is_array($this->speakers)) {
            $speakers = array_filter($this->speakers, function ($speaker) {
                return !empty($speaker['participant_id']);
            });
            
            // Reset array keys and add sort order if missing
            $speakers = array_values($speakers);
            foreach ($speakers as $index => &$speaker) {
                if (!isset($speaker['sort_order']) || is_null($speaker['sort_order'])) {
                    $speaker['sort_order'] = $index + 1;
                }
            }
            
            $this->merge(['speakers' => $speakers]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateSpeakerRoles($validator);
            $this->validatePresentationType($validator);
            $this->validateSessionCapacity($validator);
            $this->validateTimeConstraints($validator);
        });
    }

    /**
     * Validate speaker role constraints
     */
    protected function validateSpeakerRoles($validator): void
    {
        if (!$this->has('speakers') || empty($this->speakers)) {
            return;
        }

        $speakers = $this->speakers;
        $roles = array_column($speakers, 'speaker_role');
        $participantIds = array_column($speakers, 'participant_id');

        // Check for duplicate participants
        if (count($participantIds) !== count(array_unique($participantIds))) {
            $validator->errors()->add('speakers', 'Aynı katılımcı birden fazla kez konuşmacı olarak eklenemez.');
        }

        // Check primary speaker requirement
        $primarySpeakers = array_filter($roles, fn($role) => $role === 'primary');
        if (count($primarySpeakers) === 0) {
            $validator->errors()->add('speakers', 'En az bir ana konuşmacı seçilmelidir.');
        }

        // Limit primary speakers
        if (count($primarySpeakers) > 3) {
            $validator->errors()->add('speakers', 'En fazla 3 ana konuşmacı seçilebilir.');
        }

        // Check moderator limits for certain presentation types
        $moderators = array_filter($roles, fn($role) => $role === 'moderator');
        if (in_array($this->presentation_type, ['keynote', 'oral']) && count($moderators) > 2) {
            $validator->errors()->add('speakers', 'Bu sunum türü için en fazla 2 moderatör seçilebilir.');
        }
    }

    /**
     * Validate presentation type constraints
     */
    protected function validatePresentationType($validator): void
    {
        if (!$this->presentation_type) {
            return;
        }

        $type = $this->presentation_type;
        $speakerCount = $this->has('speakers') ? count($this->speakers) : 0;

        // Type-specific validations
        switch ($type) {
            case 'keynote':
                if ($speakerCount > 2) {
                    $validator->errors()->add('speakers', 'Açılış konuşması için en fazla 2 konuşmacı seçilebilir.');
                }
                if ($this->duration_minutes && $this->duration_minutes < 30) {
                    $validator->errors()->add('duration_minutes', 'Açılış konuşması en az 30 dakika olmalıdır.');
                }
                break;
                
            case 'oral':
                if ($speakerCount > 4) {
                    $validator->errors()->add('speakers', 'Sözlü sunum için en fazla 4 konuşmacı seçilebilir.');
                }
                if ($this->duration_minutes && ($this->duration_minutes < 15 || $this->duration_minutes > 45)) {
                    $validator->errors()->add('duration_minutes', 'Sözlü sunum 15-45 dakika arasında olmalıdır.');
                }
                break;
                
            case 'poster':
                if ($speakerCount > 6) {
                    $validator->errors()->add('speakers', 'Poster sunum için en fazla 6 konuşmacı seçilebilir.');
                }
                break;
                
            case 'symposium':
                if ($speakerCount < 2) {
                    $validator->errors()->add('speakers', 'Sempozyum için en az 2 konuşmacı seçilmelidir.');
                }
                if ($this->duration_minutes && $this->duration_minutes < 60) {
                    $validator->errors()->add('duration_minutes', 'Sempozyum en az 60 dakika olmalıdır.');
                }
                break;
        }
    }

    /**
     * Validate session capacity constraints
     */
    protected function validateSessionCapacity($validator): void
    {
        if (!$this->program_session_id) {
            return;
        }

        $session = ProgramSession::find($this->program_session_id);
        if (!$session) {
            return;
        }

        // Check maximum presentations per session
        $currentPresentationCount = $session->presentations()->count();
        $maxPresentations = match($session->session_type) {
            'keynote' => 1,
            'main' => 6,
            'satellite' => 4,
            'oral_presentation' => 8,
            'workshop' => 3,
            'panel' => 5,
            default => 10
        };

        if ($currentPresentationCount >= $maxPresentations) {
            $validator->errors()->add('program_session_id', 
                "Bu oturum türü için maksimum sunum sayısına ({$maxPresentations}) ulaşılmış.");
        }

        // Check total duration doesn't exceed session time
        $existingDuration = $session->presentations()->sum('duration_minutes') ?? 0;
        $sessionDuration = $session->start_time->diffInMinutes($session->end_time);
        $newDuration = $this->duration_minutes ?? 0;

        if (($existingDuration + $newDuration) > ($sessionDuration * 0.9)) { // Leave 10% buffer
            $validator->errors()->add('duration_minutes', 
                'Toplam sunum süresi oturum süresini aşıyor. Kalan süre: ' . 
                ($sessionDuration - $existingDuration) . ' dakika.');
        }
    }

    /**
     * Validate time constraints
     */
    protected function validateTimeConstraints($validator): void
    {
        if (!$this->start_time || !$this->end_time || !$this->program_session_id) {
            return;
        }

        $session = ProgramSession::find($this->program_session_id);
        if (!$session) {
            return;
        }

        // Check if presentation times are within session bounds
        $sessionStart = $session->start_time;
        $sessionEnd = $session->end_time;
        $presentationStart = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
        $presentationEnd = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);

        // Convert session times to same format for comparison
        $sessionStartFormatted = \Carbon\Carbon::createFromFormat('H:i:s', $sessionStart->format('H:i:s'));
        $sessionEndFormatted = \Carbon\Carbon::createFromFormat('H:i:s', $sessionEnd->format('H:i:s'));

        if ($presentationStart->lt($sessionStartFormatted) || $presentationEnd->gt($sessionEndFormatted)) {
            $validator->errors()->add('start_time', 
                'Sunum zamanı oturum zaman aralığının dışında (' . 
                $sessionStart->format('H:i') . ' - ' . $sessionEnd->format('H:i') . ').');
        }

        // Check minimum gap between presentations (5 minutes)
        $conflictingPresentations = \App\Models\Presentation::where('program_session_id', $this->program_session_id)
            ->where(function ($query) {
                $query->whereBetween('start_time', [
                    \Carbon\Carbon::createFromFormat('H:i', $this->start_time)->subMinutes(5)->format('H:i'),
                    \Carbon\Carbon::createFromFormat('H:i', $this->end_time)->addMinutes(5)->format('H:i')
                ])
                ->orWhereBetween('end_time', [
                    \Carbon\Carbon::createFromFormat('H:i', $this->start_time)->subMinutes(5)->format('H:i'),
                    \Carbon\Carbon::createFromFormat('H:i', $this->end_time)->addMinutes(5)->format('H:i')
                ]);
            })
            ->exists();

        if ($conflictingPresentations) {
            $validator->errors()->add('start_time', 
                'Sunumlar arasında en az 5 dakika ara olmalıdır.');
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Set default sort order if not provided
        if (!isset($validated['sort_order']) || is_null($validated['sort_order'])) {
            $maxOrder = \App\Models\Presentation::where('program_session_id', $this->program_session_id)
                                               ->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
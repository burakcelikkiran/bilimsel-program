<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Presentation;
use App\Models\ProgramSession;
use App\Models\Participant;

class UpdatePresentationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $presentation = $this->route('presentation');
        
        if (!$presentation) {
            return false;
        }

        $user = $this->user();
        
        // Admin can update any presentation
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the presentation's event
        return $user->organizations()
                   ->where('organizations.id', $presentation->programSession->venue->eventDay->event->organization_id)
                   ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $presentation = $this->route('presentation');
        
        return [
            'program_session_id' => [
                'required',
                'exists:program_sessions,id',
                function ($attribute, $value, $fail) use ($presentation) {
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
                    
                    // If moving to different session, check constraints
                    if ($value != $presentation->program_session_id) {
                        $currentSessionPresentationCount = $session->presentations()->count();
                        $maxPresentations = match($session->session_type) {
                            'keynote' => 1,
                            'main' => 6,
                            'satellite' => 4,
                            'oral_presentation' => 8,
                            'workshop' => 3,
                            'panel' => 5,
                            default => 10
                        };

                        if ($currentSessionPresentationCount >= $maxPresentations) {
                            $fail("Hedef oturum için maksimum sunum sayısına ({$maxPresentations}) ulaşılmış.");
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
                function ($attribute, $value, $fail) use ($presentation) {
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
                function ($attribute, $value, $fail) use ($presentation) {
                    if (!$value || !$this->start_time || !$this->program_session_id) return;
                    
                    $session = ProgramSession::find($this->program_session_id);
                    if (!$session) return;
                    
                    $sessionEnd = \Carbon\Carbon::createFromFormat('H:i', $session->end_time->format('H:i'));
                    $presentationEnd = \Carbon\Carbon::createFromFormat('H:i', $value);
                    
                    if ($presentationEnd->gt($sessionEnd)) {
                        $fail('Sunum bitiş saati oturum bitiş saatinden sonra olamaz (' . 
                              $session->end_time->format('H:i') . ').');
                    }
                    
                    // Check for time conflicts with other presentations in the same session (excluding current)
                    $start = $this->start_time;
                    $conflicts = Presentation::where('program_session_id', $this->program_session_id)
                        ->where('id', '!=', $presentation->id)
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
                'in:primary,co_speaker,discussant',
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
            
            'files' => [
                'nullable',
                'array',
                'max:5', // Maximum 5 files per presentation
            ],
            
            'files.*.type' => [
                'required_with:files',
                'in:presentation,document,image,video,audio',
            ],
            
            'files.*.file' => [
                'required_with:files',
                'file',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $fileType = $this->input("files.{$index}.type");
                    
                    if (!$fileType) return;
                    
                    $allowedMimes = match($fileType) {
                        'presentation' => ['pdf', 'ppt', 'pptx'],
                        'document' => ['pdf', 'doc', 'docx', 'txt'],
                        'image' => ['jpeg', 'jpg', 'png', 'gif'],
                        'video' => ['mp4', 'avi', 'mov', 'wmv'],
                        'audio' => ['mp3', 'wav', 'aac', 'ogg'],
                        default => []
                    };
                    
                    $extension = $value->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), $allowedMimes)) {
                        $fail("Bu dosya türü için geçersiz format: {$extension}. İzin verilen formatlar: " . implode(', ', $allowedMimes));
                    }
                    
                    // File size limits
                    $maxSize = match($fileType) {
                        'presentation' => 10240, // 10MB
                        'document' => 5120,      // 5MB
                        'image' => 2048,         // 2MB
                        'video' => 51200,        // 50MB
                        'audio' => 10240,        // 10MB
                        default => 2048
                    };
                    
                    if ($value->getSize() > ($maxSize * 1024)) {
                        $fail("Dosya boyutu çok büyük. Maksimum: " . ($maxSize / 1024) . "MB");
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
            
            'files.array' => 'Dosya listesi dizi formatında olmalıdır.',
            'files.max' => 'En fazla 5 dosya yüklenebilir.',
            'files.*.type.required_with' => 'Dosya türü seçimi zorunludur.',
            'files.*.type.in' => 'Geçersiz dosya türü seçildi.',
            'files.*.file.required_with' => 'Dosya seçimi zorunludur.',
            'files.*.file.file' => 'Geçerli bir dosya seçiniz.',
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
            'files' => 'dosyalar',
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
            'language' => $this->language ?: 'tr',
            'is_published' => $this->boolean('is_published'),
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

        // Clean files array
        if ($this->has('files') && is_array($this->files)) {
            $files = array_filter($this->files, function ($file) {
                return !empty($file['type']) && !empty($file['file']);
            });
            
            $this->merge(['files' => array_values($files)]);
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
            $this->validateSessionMovement($validator);
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
    }

    /**
     * Validate presentation type constraints
     */
    protected function validatePresentationType($validator): void
    {
        if (!$this->presentation_type) {
            return;
        }

        $presentation = $this->route('presentation');
        $type = $this->presentation_type;
        $speakerCount = $this->has('speakers') ? count($this->speakers) : 0;

        // If changing presentation type, validate against current session constraints
        if ($type !== $presentation->presentation_type) {
            $session = ProgramSession::find($this->program_session_id);
            if ($session) {
                // Check if the new type is appropriate for the session type
                $validTypes = match($session->session_type) {
                    'keynote' => ['keynote'],
                    'main' => ['keynote', 'oral', 'symposium'],
                    'oral_presentation' => ['oral', 'case_presentation'],
                    'workshop' => ['workshop'],
                    'panel' => ['panel', 'oral'],
                    default => ['oral', 'case_presentation', 'poster']
                };

                if (!in_array($type, $validTypes)) {
                    $validator->errors()->add('presentation_type', 
                        'Bu sunum türü seçilen oturum türü için uygun değil.');
                }
            }
        }

        // Type-specific validations (same as store request)
        switch ($type) {
            case 'keynote':
                if ($speakerCount > 2) {
                    $validator->errors()->add('speakers', 'Açılış konuşması için en fazla 2 konuşmacı seçilebilir.');
                }
                break;
                
            case 'oral':
                if ($speakerCount > 4) {
                    $validator->errors()->add('speakers', 'Sözlü sunum için en fazla 4 konuşmacı seçilebilir.');
                }
                break;
                
            case 'symposium':
                if ($speakerCount < 2) {
                    $validator->errors()->add('speakers', 'Sempozyum için en az 2 konuşmacı seçilmelidir.');
                }
                break;
        }
    }

    /**
     * Validate session capacity constraints
     */
    protected function validateSessionCapacity($validator): void
    {
        $presentation = $this->route('presentation');
        
        // Only check if moving to a different session
        if ($this->program_session_id === $presentation->program_session_id) {
            return;
        }

        $newSession = ProgramSession::find($this->program_session_id);
        if (!$newSession) {
            return;
        }

        // Check total duration constraint
        $existingDuration = $newSession->presentations()->sum('duration_minutes') ?? 0;
        $sessionDuration = $newSession->start_time->diffInMinutes($newSession->end_time);
        $newDuration = $this->duration_minutes ?? $presentation->duration_minutes ?? 0;

        if (($existingDuration + $newDuration) > ($sessionDuration * 0.9)) {
            $validator->errors()->add('duration_minutes', 
                'Hedef oturumda yeterli süre bulunmuyor. Kalan süre: ' . 
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

        $presentation = $this->route('presentation');
        $session = ProgramSession::find($this->program_session_id);
        if (!$session) {
            return;
        }

        // Check minimum gap between presentations (5 minutes)
        $conflictingPresentations = Presentation::where('program_session_id', $this->program_session_id)
            ->where('id', '!=', $presentation->id)
            ->where(function ($query) {
                $startWithBuffer = \Carbon\Carbon::createFromFormat('H:i', $this->start_time)->subMinutes(5);
                $endWithBuffer = \Carbon\Carbon::createFromFormat('H:i', $this->end_time)->addMinutes(5);
                
                $query->where(function ($q) use ($startWithBuffer, $endWithBuffer) {
                    $q->whereBetween('start_time', [
                        $startWithBuffer->format('H:i'),
                        $endWithBuffer->format('H:i')
                    ])
                    ->orWhereBetween('end_time', [
                        $startWithBuffer->format('H:i'),
                        $endWithBuffer->format('H:i')
                    ]);
                });
            })
            ->exists();

        if ($conflictingPresentations) {
            $validator->errors()->add('start_time', 
                'Sunumlar arasında en az 5 dakika ara olmalıdır.');
        }
    }

    /**
     * Validate session movement constraints
     */
    protected function validateSessionMovement($validator): void
    {
        $presentation = $this->route('presentation');
        
        // If moving to a different session, perform additional checks
        if ($this->program_session_id !== $presentation->program_session_id) {
            $currentSession = $presentation->programSession;
            $newSession = ProgramSession::find($this->program_session_id);

            if (!$newSession) {
                return;
            }

            // Check if moving between different events
            if ($currentSession->venue->eventDay->event_id !== $newSession->venue->eventDay->event_id) {
                $validator->errors()->add('program_session_id', 
                    'Sunum farklı bir etkinliğe taşınamaz.');
            }

            // Check if moving between different days with speaker conflicts
            if ($currentSession->venue->eventDay->id !== $newSession->venue->eventDay->id) {
                $speakerIds = array_column($this->speakers ?? [], 'participant_id');
                
                if (!empty($speakerIds)) {
                    // Check if speakers have other commitments on the new day
                    $conflicts = Presentation::whereHas('programSession.venue.eventDay', function ($query) use ($newSession) {
                        $query->where('id', $newSession->venue->eventDay->id);
                    })
                    ->whereHas('speakers', function ($query) use ($speakerIds) {
                        $query->whereIn('participant_id', $speakerIds);
                    })
                    ->where('id', '!=', $presentation->id)
                    ->exists();

                    if ($conflicts) {
                        $validator->errors()->add('program_session_id', 
                            'Konuşmacılar aynı günde başka sunumlarda yer alıyor.');
                    }
                }
            }

            // Log the session movement
            \Log::info('Presentation Session Movement', [
                'presentation_id' => $presentation->id,
                'from_session' => $currentSession->id,
                'to_session' => $newSession->id,
                'user_id' => $this->user()->id,
            ]);
        }
    }

    /**
     * Handle any post-validation logic
     */
    protected function passedValidation(): void
    {
        $presentation = $this->route('presentation');
        
        // Track significant changes
        $changes = [];
        
        if ($this->title !== $presentation->title) {
            $changes[] = "Başlık değişti: '{$presentation->title}' → '{$this->title}'";
        }
        
        if ($this->program_session_id !== $presentation->program_session_id) {
            $changes[] = "Oturum değişti: {$presentation->program_session_id} → {$this->program_session_id}";
        }
        
        if ($this->presentation_type !== $presentation->presentation_type) {
            $changes[] = "Tür değişti: {$presentation->presentation_type} → {$this->presentation_type}";
        }

        if (!empty($changes)) {
            \Log::info('Presentation Updated', [
                'presentation_id' => $presentation->id,
                'user_id' => $this->user()->id,
                'changes' => $changes,
            ]);
        }
    }
}
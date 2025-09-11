<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProgramSession;
use App\Models\Venue;

class UpdateProgramSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $session = $this->route('program_session') ?? $this->route('programSession');
        
        \Log::info('🔍 Route parameter debug', [
            'program_session' => $this->route('program_session') ? 'found' : 'not found',
            'programSession' => $this->route('programSession') ? 'found' : 'not found',
            'all_route_params' => $this->route()->parameters()
        ]);
        
        if (!$session) {
            \Log::warning('🚨 UpdateProgramSessionRequest: No session found in route');
            return false;
        }

        $user = $this->user();
        
        \Log::info('🔍 UpdateProgramSessionRequest@authorize called', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_isAdmin' => $user->isAdmin(),
            'session_id' => $session->id,
        ]);
        
        // Admin can update any session
        if ($user->isAdmin()) {
            \Log::info('✅ UpdateProgramSessionRequest: User is admin, allowing update');
            return true;
        }

        // Check if user belongs to the organization that owns the event
        $eventOrganizationId = $session->venue->eventDay->event->organization_id;
        $hasAccess = $user->organizations()
                   ->where('organizations.id', $eventOrganizationId)
                   ->exists();
                   
        \Log::info('🔍 UpdateProgramSessionRequest: Organization check', [
            'event_organization_id' => $eventOrganizationId,
            'user_organizations' => $user->organizations()->get()->toArray(),
            'has_access' => $hasAccess
        ]);
        
        return $hasAccess;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $session = $this->route('program_session') ?? $this->route('programSession');
        $venue = Venue::find($this->venue_id);
        
        return [
            'venue_id' => [
                'required',
                'exists:venues,id',
                function ($attribute, $value, $fail) {
                    $venue = Venue::find($value);
                    if (!$venue) {
                        $fail('Seçilen salon bulunamadı.');
                        return;
                    }
                    
                    // Check if user has access to this venue's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $venue->eventDay->event->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu salona erişim yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'title' => [
                'required',
                'string',
                'max:500',
                'min:3',
            ],
            
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            
            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (!$this->venue_id || !$this->end_time) return;
                    
                    $venue = Venue::find($this->venue_id);
                    if (!$venue) return;
                    
                    $session = $this->route('program_session') ?? $this->route('programSession');
                    
                    // Skip conflict check if session not found (should not happen, but safety)
                    if (!$session) {
                        \Log::warning('🚨 Time conflict check: Session not found for conflict validation');
                        return;
                    }
                    
                    // Check for time conflicts with other sessions in the same venue
                    $endTime = $this->end_time;
                    $conflicts = ProgramSession::where('venue_id', $this->venue_id)
                        ->where('id', '!=', $session->id)
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time')
                        ->where(function ($query) use ($value, $endTime) {
                            $query->where(function ($q) use ($value, $endTime) {
                                // Check if new time overlaps with existing sessions
                                $q->where('start_time', '<', $endTime)
                                  ->where('end_time', '>', $value);
                            });
                        })
                        ->exists();
                    
                    if ($conflicts) {
                        $fail('Bu zaman diliminde aynı salonda başka bir oturum bulunmaktadır.');
                    }
                }
            ],
            
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    if (!$this->start_time) return;
                    
                    try {
                        // Create Carbon instances for time comparison
                        $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                        $end = \Carbon\Carbon::createFromFormat('H:i', $value);
                        
                        // If end time is earlier than start time, assume it's next day
                        if ($end < $start) {
                            $end->addDay();
                        }
                        
                        $durationMinutes = $start->diffInMinutes($end);
                        
                        // Check maximum session duration (8 hours = 480 minutes)
                        if ($durationMinutes > 480) {
                            $fail('Oturum süresi en fazla 8 saat olabilir.');
                        }
                        
                        // Check minimum session duration (15 minutes) 
                        if ($durationMinutes < 15) {
                            $fail('Oturum süresi en az 15 dakika olmalıdır.');
                        }
                    } catch (\Exception $e) {
                        \Log::warning('🚨 Time validation error', [
                            'start_time' => $this->start_time,
                            'end_time' => $value,
                            'error' => $e->getMessage()
                        ]);
                        // Don't fail on parsing errors, let basic validation handle it
                    }
                }
            ],
            
            'session_type' => [
                'required',
                'in:main,satellite,oral_presentation,break,special,workshop,panel,poster',
            ],
            
            'moderator_title' => [
                'nullable',
                'string',
                'max:100',
                'required_unless:is_break,true',
            ],
            
            'sponsor_id' => [
                'nullable',
                'exists:sponsors,id',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    $sponsor = \App\Models\Sponsor::find($value);
                    if (!$sponsor) {
                        $fail('Seçilen sponsor bulunamadı.');
                        return;
                    }
                    
                    // Check if sponsor belongs to the same organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $venue = Venue::find($this->venue_id);
                        if ($venue && $sponsor->organization_id !== $venue->eventDay->event->organization_id) {
                            $fail('Sponsor aynı organizasyona ait olmalıdır.');
                        }
                    }
                }
            ],
            
            'is_break' => [
                'boolean',
            ],
            
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],
            
            'moderator_ids' => [
                'nullable',
                'array',
                'max:5', // Maximum 5 moderators
            ],
            
            'moderator_ids.*' => [
                'exists:participants,id',
                function ($attribute, $value, $fail) {
                    $participant = \App\Models\Participant::find($value);
                    if (!$participant || !$participant->is_moderator) {
                        $fail('Seçilen katılımcı moderatör olarak tanımlanmamış.');
                    }
                    
                    // Check if participant belongs to the same organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $venue = Venue::find($this->venue_id);
                        if ($venue && $participant->organization_id !== $venue->eventDay->event->organization_id) {
                            $fail('Moderatör aynı organizasyona ait olmalıdır.');
                        }
                    }
                }
            ],
            
            'category_ids' => [
                'nullable',
                'array',
                'max:3', // Maximum 3 categories
            ],
            
            'category_ids.*' => [
                'exists:program_session_categories,id',
                function ($attribute, $value, $fail) {
                    $category = \App\Models\ProgramSessionCategory::find($value);
                    if (!$category) {
                        $fail('Seçilen kategori bulunamadı.');
                        return;
                    }
                    
                    // Check if category belongs to the same event
                    $venue = Venue::find($this->venue_id);
                    if ($venue && $category->event_id !== $venue->eventDay->event->id) {
                        $fail('Kategori aynı etkinliğe ait olmalıdır.');
                    }
                }
            ],
            
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Salon seçimi zorunludur.',
            'venue_id.exists' => 'Seçilen salon bulunamadı.',
            
            'title.required' => 'Oturum başlığı zorunludur.',
            'title.string' => 'Oturum başlığı metin formatında olmalıdır.',
            'title.max' => 'Oturum başlığı en fazla 500 karakter olabilir.',
            'title.min' => 'Oturum başlığı en az 3 karakter olmalıdır.',
            
            'description.string' => 'Açıklama metin formatında olmalıdır.',
            'description.max' => 'Açıklama en fazla 2000 karakter olabilir.',
            
            'start_time.required' => 'Başlangıç saati zorunludur.',
            'start_time.date_format' => 'Başlangıç saati SS:DD formatında olmalıdır.',
            
            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.date_format' => 'Bitiş saati SS:DD formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            
            'session_type.required' => 'Oturum türü seçimi zorunludur.',
            'session_type.in' => 'Geçersiz oturum türü seçildi.',
            
            'moderator_title.required_unless' => 'Moderatör ünvanı zorunludur.',
            'moderator_title.string' => 'Moderatör ünvanı metin formatında olmalıdır.',
            'moderator_title.max' => 'Moderatör ünvanı en fazla 100 karakter olabilir.',
            
            'sponsor_id.exists' => 'Seçilen sponsor bulunamadı.',
            
            'is_break.boolean' => 'Ara oturum seçimi doğru/yanlış değer olmalıdır.',
            
            'sort_order.integer' => 'Sıralama sayı formatında olmalıdır.',
            'sort_order.min' => 'Sıralama 0\'dan küçük olamaz.',
            'sort_order.max' => 'Sıralama 999\'dan büyük olamaz.',
            
            'moderator_ids.array' => 'Moderatör listesi dizi formatında olmalıdır.',
            'moderator_ids.max' => 'En fazla 5 moderatör seçilebilir.',
            'moderator_ids.*.exists' => 'Seçilen moderatör bulunamadı.',
            
            'category_ids.array' => 'Kategori listesi dizi formatında olmalıdır.',
            'category_ids.max' => 'En fazla 3 kategori seçilebilir.',
            'category_ids.*.exists' => 'Seçilen kategori bulunamadı.',
            
            'notes.string' => 'Notlar metin formatında olmalıdır.',
            'notes.max' => 'Notlar en fazla 1000 karakter olabilir.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'venue_id' => 'salon',
            'title' => 'oturum başlığı',
            'description' => 'açıklama',
            'start_time' => 'başlangıç saati',
            'end_time' => 'bitiş saati',
            'session_type' => 'oturum türü',
            'moderator_title' => 'moderatör ünvanı',
            'sponsor_id' => 'sponsor',
            'is_break' => 'ara oturum',
            'sort_order' => 'sıralama',
            'moderator_ids' => 'moderatörler',
            'category_ids' => 'kategoriler',
            'notes' => 'notlar',
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
            'description' => $this->description ? trim($this->description) : null,
            'moderator_title' => $this->moderator_title ? trim($this->moderator_title) : 'Oturum Başkanı',
            'is_break' => $this->boolean('is_break'),
            'sort_order' => $this->sort_order ? (int) $this->sort_order : 0,
            'notes' => $this->notes ? trim($this->notes) : null,
        ]);

        // Remove empty values from arrays
        if ($this->has('moderator_ids') && is_array($this->moderator_ids)) {
            $this->merge([
                'moderator_ids' => array_filter($this->moderator_ids, function ($value) {
                    return !empty($value);
                })
            ]);
        }

        if ($this->has('category_ids') && is_array($this->category_ids)) {
            $this->merge([
                'category_ids' => array_filter($this->category_ids, function ($value) {
                    return !empty($value);
                })
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic after basic rules
            $this->validateSessionTiming($validator);
            $this->validateBreakSession($validator);
            $this->validateModerators($validator);
        });
    }

    /**
     * Validate session timing constraints
     */
    protected function validateSessionTiming($validator): void
    {
        if (!$this->start_time || !$this->end_time || !$this->venue_id) {
            return;
        }

        $venue = Venue::find($this->venue_id);
        if (!$venue) {
            return;
        }

        $eventDay = $venue->eventDay;
        $session = $this->route('program_session') ?? $this->route('programSession');

        // Check if there are any presentations that would conflict with the new timing
        $existingPresentations = $session->presentations()
            ->where(function ($query) {
                $query->where('start_time', '<', $this->start_time)
                      ->orWhere('end_time', '>', $this->end_time);
            })
            ->exists();

        if ($existingPresentations) {
            $validator->errors()->add('start_time', 'Oturum zamanı değiştirildiğinde mevcut sunumlar zaman aralığının dışında kalacak.');
        }
    }

    /**
     * Validate break session constraints
     */
    protected function validateBreakSession($validator): void
    {
        if (!$this->boolean('is_break')) {
            return;
        }

        // Break sessions should not have moderators
        if ($this->has('moderator_ids') && !empty($this->moderator_ids)) {
            $validator->errors()->add('moderator_ids', 'Ara oturumlarında moderatör atanamaz.');
        }

        // Break sessions should not have categories
        if ($this->has('category_ids') && !empty($this->category_ids)) {
            $validator->errors()->add('category_ids', 'Ara oturumlarında kategori atanamaz.');
        }

        // Break sessions should have specific session types
        if (!in_array($this->session_type, ['break', 'special'])) {
            $validator->errors()->add('session_type', 'Ara oturumları için uygun oturum türü seçiniz.');
        }
    }

    /**
     * Validate moderator constraints
     */
    protected function validateModerators($validator): void
    {
        if (!$this->has('moderator_ids') || empty($this->moderator_ids)) {
            return;
        }

        // Check for duplicate moderators
        $moderatorIds = array_filter($this->moderator_ids);
        if (count($moderatorIds) !== count(array_unique($moderatorIds))) {
            $validator->errors()->add('moderator_ids', 'Aynı moderatör birden fazla kez seçilemez.');
        }

        // Removed: Time slot availability check for moderators
        // Moderators can be assigned to multiple sessions at the same time if needed
    }
}
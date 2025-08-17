<?php

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ProgramSession;
use App\Models\Venue;

/**
 * @OA\Schema(
 *     schema="StoreProgramSessionRequest",
 *     type="object",
 *     required={"venue_id", "title", "start_time", "end_time", "session_type"},
 *     description="Program session creation request schema",
 *     @OA\Property(
 *         property="venue_id",
 *         type="integer",
 *         minimum=1,
 *         description="ID of the venue where the session will be held. Must belong to the event.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         minLength=3,
 *         maxLength=500,
 *         description="Title of the program session",
 *         example="Opening Ceremony"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=2000,
 *         nullable=true,
 *         description="Detailed description of the session",
 *         example="Welcome ceremony for the annual conference with keynote speakers"
 *     ),
 *     @OA\Property(
 *         property="start_time",
 *         type="string",
 *         format="time",
 *         pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$",
 *         description="Start time in HH:MM format",
 *         example="09:00"
 *     ),
 *     @OA\Property(
 *         property="end_time",
 *         type="string",
 *         format="time",
 *         pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$",
 *         description="End time in HH:MM format. Must be after start_time and session must be at least 15 minutes, maximum 8 hours.",
 *         example="10:30"
 *     ),
 *     @OA\Property(
 *         property="session_type",
 *         type="string",
 *         enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social", "keynote", "panel"},
 *         description="Type of the session",
 *         example="plenary"
 *     ),
 *     @OA\Property(
 *         property="max_participants",
 *         type="integer",
 *         minimum=1,
 *         maximum=10000,
 *         nullable=true,
 *         description="Maximum number of participants allowed",
 *         example=150
 *     ),
 *     @OA\Property(
 *         property="is_break",
 *         type="boolean",
 *         description="Whether this session is a break",
 *         example=false,
 *         default=false
 *     ),
 *     @OA\Property(
 *         property="is_featured",
 *         type="boolean",
 *         description="Whether this session should be featured prominently",
 *         example=true,
 *         default=false
 *     ),
 *     @OA\Property(
 *         property="requires_registration",
 *         type="boolean",
 *         description="Whether registration is required for this session",
 *         example=false,
 *         default=false
 *     ),
 *     @OA\Property(
 *         property="registration_limit",
 *         type="integer",
 *         minimum=1,
 *         nullable=true,
 *         description="Registration limit (required if requires_registration is true)",
 *         example=50
 *     ),
 *     @OA\Property(
 *         property="sponsor_id",
 *         type="integer",
 *         nullable=true,
 *         description="ID of the sponsoring organization. Must belong to the same organization as the event.",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="category_ids",
 *         type="array",
 *         maxItems=5,
 *         nullable=true,
 *         description="Array of category IDs (maximum 5 categories per session). Categories must belong to the event.",
 *         @OA\Items(
 *             type="integer",
 *             minimum=1
 *         ),
 *         example={1, 3, 5}
 *     ),
 *     @OA\Property(
 *         property="moderator_ids",
 *         type="array",
 *         maxItems=3,
 *         nullable=true,
 *         description="Array of moderator participant IDs (maximum 3 moderators per session). Moderators must belong to the same organization.",
 *         @OA\Items(
 *             type="integer",
 *             minimum=1
 *         ),
 *         example={12, 23}
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         maxLength=1000,
 *         nullable=true,
 *         description="Internal notes for the session",
 *         example="VIP guests will be seated in front rows"
 *     ),
 *     @OA\Property(
 *         property="sort_order",
 *         type="integer",
 *         minimum=0,
 *         nullable=true,
 *         description="Sort order for display purposes",
 *         example=1,
 *         default=0
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="StoreProgramSessionValidationError",
 *     type="object",
 *     description="Validation error response for program session creation",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Doğrulama hatası"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Field-specific validation errors",
 *         @OA\Property(
 *             property="venue_id",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Salon seçimi zorunludur.", "Seçilen salon bu etkinliğe ait değil."}
 *         ),
 *         @OA\Property(
 *             property="title",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Oturum başlığı zorunludur.", "Oturum başlığı en az 3 karakter olmalıdır."}
 *         ),
 *         @OA\Property(
 *             property="start_time",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Başlangıç saati zorunludur.", "Bu salon ve saatte başka bir oturum bulunuyor."}
 *         ),
 *         @OA\Property(
 *             property="end_time",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Bitiş saati başlangıç saatinden sonra olmalıdır.", "Oturum en az 15 dakika sürmeli."}
 *         ),
 *         @OA\Property(
 *             property="session_type",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Geçersiz oturum türü seçildi."}
 *         ),
 *         @OA\Property(
 *             property="max_participants",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Maksimum katılımcı sayısı sayı olmalıdır."}
 *         ),
 *         @OA\Property(
 *             property="registration_limit",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Kayıt gerekli seçildiğinde kayıt limiti zorunludur."}
 *         ),
 *         @OA\Property(
 *             property="sponsor_id",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Seçilen sponsor bu organizasyona ait değil."}
 *         ),
 *         @OA\Property(
 *             property="category_ids",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Oturum başına en fazla 5 kategori seçilebilir."}
 *         ),
 *         @OA\Property(
 *             property="moderator_ids",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Oturum başına en fazla 3 moderatör seçilebilir."}
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="SessionTimeConflictError",
 *     type="object",
 *     description="Time conflict error response",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Bu salon ve saatte başka bir oturum bulunuyor."
 *     ),
 *     @OA\Property(
 *         property="conflict_details",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="venue_id", type="integer", example=1),
 *         @OA\Property(property="requested_start", type="string", example="09:00"),
 *         @OA\Property(property="requested_end", type="string", example="10:30"),
 *         @OA\Property(property="conflicting_session", type="string", example="Existing Session Title")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="SessionTypeEnum",
 *     type="string",
 *     enum={"plenary", "parallel", "workshop", "poster", "break", "lunch", "social", "keynote", "panel"},
 *     description="Available session types",
 *     example="plenary"
 * )
 */

/**
 * @OA\Schema(
 *     schema="SessionDurationInfo",
 *     type="object",
 *     description="Session duration information",
 *     @OA\Property(
 *         property="duration_minutes",
 *         type="integer",
 *         description="Session duration in minutes",
 *         example=90
 *     ),
 *     @OA\Property(
 *         property="formatted_time_range",
 *         type="string",
 *         description="Formatted time range for display",
 *         example="09:00 - 10:30"
 *     ),
 *     @OA\Property(
 *         property="is_valid_duration",
 *         type="boolean",
 *         description="Whether the duration meets minimum/maximum requirements",
 *         example=true
 *     )
 * )
 */

class StoreProgramSessionRequest extends FormRequest
{
    /**
     * @OA\Property(
     *     property="authorization",
     *     type="object",
     *     description="Authorization requirements for creating program sessions",
     *     @OA\Property(
     *         property="required_permission",
     *         type="string",
     *         example="create ProgramSession"
     *     ),
     *     @OA\Property(
     *         property="organization_access",
     *         type="boolean",
     *         description="User must have access to the venue's organization",
     *         example=true
     *     )
     * )
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ProgramSession::class);
    }

    /**
     * @OA\Property(
     *     property="validation_rules",
     *     type="object",
     *     description="Detailed validation rules with business logic",
     *     @OA\Property(
     *         property="venue_validation",
     *         type="object",
     *         @OA\Property(property="belongs_to_event", type="boolean", description="Venue must belong to the specified event"),
     *         @OA\Property(property="user_has_access", type="boolean", description="User must have access to venue's organization"),
     *         @OA\Property(property="venue_exists", type="boolean", description="Venue must exist in database")
     *     ),
     *     @OA\Property(
     *         property="time_validation",
     *         type="object",
     *         @OA\Property(property="no_conflicts", type="boolean", description="No time conflicts with existing sessions in same venue"),
     *         @OA\Property(property="minimum_duration", type="integer", description="Minimum session duration in minutes", example=15),
     *         @OA\Property(property="maximum_duration", type="integer", description="Maximum session duration in minutes", example=480),
     *         @OA\Property(property="end_after_start", type="boolean", description="End time must be after start time")
     *     ),
     *     @OA\Property(
     *         property="relationship_validation",
     *         type="object",
     *         @OA\Property(property="sponsor_same_organization", type="boolean", description="Sponsor must belong to same organization"),
     *         @OA\Property(property="categories_belong_to_event", type="boolean", description="Categories must belong to the event"),
     *         @OA\Property(property="moderators_same_organization", type="boolean", description="Moderators must belong to same organization"),
     *         @OA\Property(property="max_categories", type="integer", example=5),
     *         @OA\Property(property="max_moderators", type="integer", example=3)
     *     )
     * )
     */
    public function rules(): array
    {
        return [
            'venue_id' => [
                'required',
                'exists:venues,id',
                function ($attribute, $value, $fail) {
                    // Venue belongs to event validation
                    $event = $this->route('event');
                    $venue = Venue::with('eventDay')->find($value);
                    if (!$venue || $venue->eventDay->event_id !== $event->id) {
                        $fail('Seçilen salon bu etkinliğe ait değil.');
                    }

                    // User access validation
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                        if (!$userOrganizationIds->contains($venue->eventDay->event->organization_id)) {
                            $fail('Bu salona erişim yetkiniz bulunmuyor.');
                        }
                    }
                },
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
                    // Time conflict validation
                    if ($this->venue_id && $this->end_time) {
                        $conflicts = ProgramSession::where('venue_id', $this->venue_id)
                            ->where(function ($query) use ($value) {
                                $query->where(function ($q) use ($value) {
                                    $q->where('start_time', '<=', $value)
                                      ->where('end_time', '>', $value);
                                })->orWhere(function ($q) use ($value) {
                                    $q->where('start_time', '<', $this->end_time)
                                      ->where('end_time', '>=', $this->end_time);
                                })->orWhere(function ($q) use ($value) {
                                    $q->where('start_time', '>=', $value)
                                      ->where('end_time', '<=', $this->end_time);
                                });
                            })
                            ->exists();

                        if ($conflicts) {
                            $fail('Bu salon ve saatte başka bir oturum bulunuyor.');
                        }
                    }
                },
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    // Duration validation
                    if ($this->start_time) {
                        $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                        $endTime = \Carbon\Carbon::createFromFormat('H:i', $value);
                        $duration = $endTime->diffInMinutes($startTime);
                        
                        if ($duration < 15) {
                            $fail('Oturum en az 15 dakika sürmeli.');
                        }
                        
                        if ($duration > 480) { // 8 hours
                            $fail('Oturum 8 saatten uzun olamaz.');
                        }
                    }
                },
            ],
            'session_type' => [
                'required',
                'in:plenary,parallel,workshop,poster,break,lunch,social,keynote,panel',
            ],
            'max_participants' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'is_break' => [
                'boolean',
            ],
            'is_featured' => [
                'boolean',
            ],
            'requires_registration' => [
                'boolean',
            ],
            'registration_limit' => [
                'nullable',
                'integer',
                'min:1',
                'required_if:requires_registration,true',
            ],
            'sponsor_id' => [
                'nullable',
                'exists:sponsors,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Sponsor organization validation
                        $event = $this->route('event');
                        $sponsor = \App\Models\Sponsor::find($value);
                        if ($sponsor && $sponsor->organization_id !== $event->organization_id) {
                            $fail('Seçilen sponsor bu organizasyona ait değil.');
                        }
                    }
                },
            ],
            'category_ids' => [
                'nullable',
                'array',
                'max:5', // Maximum 5 categories per session
            ],
            'category_ids.*' => [
                'exists:program_session_categories,id',
                function ($attribute, $value, $fail) {
                    // Category belongs to event validation
                    $event = $this->route('event');
                    $category = \App\Models\ProgramSessionCategory::find($value);
                    if ($category && $category->event_id !== $event->id) {
                        $fail('Seçilen kategori bu etkinliğe ait değil.');
                    }
                },
            ],
            'moderator_ids' => [
                'nullable',
                'array',
                'max:3', // Maximum 3 moderators per session
            ],
            'moderator_ids.*' => [
                'exists:participants,id',
                function ($attribute, $value, $fail) {
                    // Moderator organization validation
                    $event = $this->route('event');
                    $participant = \App\Models\Participant::find($value);
                    if ($participant && $participant->organization_id !== $event->organization_id) {
                        $fail('Seçilen moderatör bu organizasyona ait değil.');
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * @OA\Property(
     *     property="custom_attributes",
     *     type="object",
     *     description="Turkish field names for validation error messages",
     *     @OA\Property(property="venue_id", type="string", example="salon"),
     *     @OA\Property(property="title", type="string", example="oturum başlığı"),
     *     @OA\Property(property="description", type="string", example="açıklama"),
     *     @OA\Property(property="start_time", type="string", example="başlangıç saati"),
     *     @OA\Property(property="end_time", type="string", example="bitiş saati"),
     *     @OA\Property(property="session_type", type="string", example="oturum türü"),
     *     @OA\Property(property="max_participants", type="string", example="maksimum katılımcı"),
     *     @OA\Property(property="sponsor_id", type="string", example="sponsor"),
     *     @OA\Property(property="category_ids", type="string", example="kategoriler"),
     *     @OA\Property(property="moderator_ids", type="string", example="moderatörler")
     * )
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
            'max_participants' => 'maksimum katılımcı',
            'is_break' => 'ara oturumu',
            'is_featured' => 'öne çıkarılmış',
            'requires_registration' => 'kayıt gerekli',
            'registration_limit' => 'kayıt limiti',
            'sponsor_id' => 'sponsor',
            'category_ids' => 'kategoriler',
            'moderator_ids' => 'moderatörler',
            'notes' => 'notlar',
            'sort_order' => 'sıra numarası',
        ];
    }

    /**
     * @OA\Property(
     *     property="error_messages",
     *     type="object",
     *     description="Custom validation error messages in Turkish",
     *     @OA\Property(property="venue_id.required", type="string", example="Salon seçimi zorunludur."),
     *     @OA\Property(property="title.required", type="string", example="Oturum başlığı zorunludur."),
     *     @OA\Property(property="start_time.required", type="string", example="Başlangıç saati zorunludur."),
     *     @OA\Property(property="end_time.after", type="string", example="Bitiş saati başlangıç saatinden sonra olmalıdır."),
     *     @OA\Property(property="session_type.in", type="string", example="Geçersiz oturum türü seçildi."),
     *     @OA\Property(property="category_ids.max", type="string", example="Oturum başına en fazla 5 kategori seçilebilir."),
     *     @OA\Property(property="moderator_ids.max", type="string", example="Oturum başına en fazla 3 moderatör seçilebilir.")
     * )
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Salon seçimi zorunludur.',
            'venue_id.exists' => 'Seçilen salon bulunamadı.',
            
            'title.required' => 'Oturum başlığı zorunludur.',
            'title.min' => 'Oturum başlığı en az 3 karakter olmalıdır.',
            'title.max' => 'Oturum başlığı en fazla 500 karakter olabilir.',
            
            'description.max' => 'Açıklama en fazla 2000 karakter olabilir.',
            
            'start_time.required' => 'Başlangıç saati zorunludur.',
            'start_time.date_format' => 'Başlangıç saati SS:DD formatında olmalıdır.',
            
            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.date_format' => 'Bitiş saati SS:DD formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            
            'session_type.required' => 'Oturum türü seçimi zorunludur.',
            'session_type.in' => 'Geçersiz oturum türü seçildi.',
            
            'max_participants.integer' => 'Maksimum katılımcı sayısı sayı olmalıdır.',
            'max_participants.min' => 'Maksimum katılımcı sayısı en az 1 olmalıdır.',
            'max_participants.max' => 'Maksimum katılımcı sayısı 10.000\'i geçemez.',
            
            'is_break.boolean' => 'Ara oturumu değeri doğru/yanlış olmalıdır.',
            'is_featured.boolean' => 'Öne çıkarılmış değeri doğru/yanlış olmalıdır.',
            'requires_registration.boolean' => 'Kayıt gerekli değeri doğru/yanlış olmalıdır.',
            
            'registration_limit.required_if' => 'Kayıt gerekli seçildiğinde kayıt limiti zorunludur.',
            'registration_limit.integer' => 'Kayıt limiti sayı olmalıdır.',
            'registration_limit.min' => 'Kayıt limiti en az 1 olmalıdır.',
            
            'sponsor_id.exists' => 'Seçilen sponsor bulunamadı.',
            
            'category_ids.array' => 'Kategoriler liste formatında olmalıdır.',
            'category_ids.max' => 'Oturum başına en fazla 5 kategori seçilebilir.',
            'category_ids.*.exists' => 'Seçilen kategorilerden biri veya birkaçı bulunamadı.',
            
            'moderator_ids.array' => 'Moderatörler liste formatında olmalıdır.',
            'moderator_ids.max' => 'Oturum başına en fazla 3 moderatör seçilebilir.',
            'moderator_ids.*.exists' => 'Seçilen moderatörlerden biri veya birkaçı bulunamadı.',
            
            'notes.string' => 'Notlar metin formatında olmalıdır.',
            'notes.max' => 'Notlar en fazla 1.000 karakter olabilir.',
            
            'sort_order.integer' => 'Sıra numarası sayı olmalıdır.',
            'sort_order.min' => 'Sıra numarası sıfır veya pozitif olmalıdır.',
        ];
    }

    /**
     * @OA\Property(
     *     property="error_response_format",
     *     type="object",
     *     description="Format of the error response returned on validation failure",
     *     @OA\Property(property="success", type="boolean", example=false),
     *     @OA\Property(property="message", type="string", example="Doğrulama hatası"),
     *     @OA\Property(property="errors", type="object", description="Field-specific validation errors")
     * )
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * @OA\Property(
     *     property="data_preparation",
     *     type="object",
     *     description="Data preprocessing before validation",
     *     @OA\Property(property="boolean_defaults", type="object", description="Default boolean values"),
     *     @OA\Property(property="array_filtering", type="boolean", description="Filters empty values from arrays"),
     *     @OA\Property(property="text_trimming", type="boolean", description="Trims whitespace from text fields")
     * )
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_break' => $this->boolean('is_break', false),
            'is_featured' => $this->boolean('is_featured', false),
            'requires_registration' => $this->boolean('requires_registration', false),
            'sort_order' => $this->sort_order ?? 0,
        ]);

        // Clean empty values from arrays
        if ($this->has('category_ids') && is_array($this->category_ids)) {
            $categories = array_filter($this->category_ids, function($id) {
                return !empty($id) && is_numeric($id);
            });
            $this->merge(['category_ids' => array_values($categories)]);
        }

        if ($this->has('moderator_ids') && is_array($this->moderator_ids)) {
            $moderators = array_filter($this->moderator_ids, function($id) {
                return !empty($id) && is_numeric($id);
            });
            $this->merge(['moderator_ids' => array_values($moderators)]);
        }

        // Trim text fields
        if ($this->has('title')) {
            $this->merge(['title' => trim($this->title)]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => trim($this->description)]);
        }

        if ($this->has('notes')) {
            $this->merge(['notes' => trim($this->notes)]);
        }
    }

    /**
     * @OA\Property(
     *     property="helper_methods",
     *     type="object",
     *     description="Additional helper methods for session data",
     *     @OA\Property(
     *         property="getDurationMinutes",
     *         type="object",
     *         @OA\Property(property="return_type", type="string", example="integer|null"),
     *         @OA\Property(property="description", type="string", example="Calculate session duration in minutes")
     *     ),
     *     @OA\Property(
     *         property="getFormattedTimeRange",
     *         type="object",
     *         @OA\Property(property="return_type", type="string", example="string|null"),
     *         @OA\Property(property="description", type="string", example="Get formatted time range for display")
     *     )
     * )
     */
    public function getDurationMinutes(): ?int
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
            $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
            return $end->diffInMinutes($start);
        }

        return null;
    }

    public function getFormattedTimeRange(): ?string
    {
        if ($this->start_time && $this->end_time) {
            return "{$this->start_time} - {$this->end_time}";
        }

        return null;
    }
}
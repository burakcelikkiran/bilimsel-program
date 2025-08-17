<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProgramSession;

class StoreProgramSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // FIX: Use policy-based authorization instead of canManageOrganization
        return $this->user()->can('create', ProgramSession::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'venue_id' => [
                'required',
                'exists:venues,id',
                function ($attribute, $value, $fail) {
                    $venue = \App\Models\Venue::with('eventDay.event')->find($value);
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
                            $fail('Bu salonu kullanamazsınız.');
                        }
                    }
                },
            ],
            'category_ids' => 'nullable|array',
            'category_ids.*' => [
                'exists:program_session_categories,id',
                function ($attribute, $value, $fail) {
                    $category = \App\Models\ProgramSessionCategory::with('event')->find($value);
                    if (!$category) return;
                    
                    // Check if user has access to this category's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $category->event->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu kategoriyi kullanamazsınız.');
                        }
                    }
                },
            ],
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:2000',
            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // Check for time conflicts with other sessions in same venue
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
                            $fail('Bu salon ve saatte başka bir oturum var.');
                        }
                    }
                },
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'session_type' => 'required|in:main,satellite,oral_presentation,special,break',
            'moderator_title' => 'nullable|string|max:255',
            'sponsor_id' => [
                'nullable',
                'exists:sponsors,id',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    $sponsor = \App\Models\Sponsor::find($value);
                    if (!$sponsor) return;
                    
                    // Check if user has access to this sponsor's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $sponsor->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu sponsoru kullanamazsınız.');
                        }
                    }
                },
            ],
            'is_break' => 'boolean',
            'moderator_ids' => 'nullable|array',
            'moderator_ids.*' => [
                'exists:participants,id',
                function ($attribute, $value, $fail) {
                    $participant = \App\Models\Participant::find($value);
                    if (!$participant) return;
                    
                    // Check if user has access to this participant's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $participant->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu katılımcıyı moderatör olarak seçemezsiniz.');
                        }
                    }
                },
            ],
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'venue_id' => 'salon',
            'category_ids' => 'kategoriler',
            'title' => 'oturum başlığı',
            'description' => 'açıklama',
            'start_time' => 'başlangıç saati',
            'end_time' => 'bitiş saati',
            'session_type' => 'oturum türü',
            'moderator_title' => 'moderatör unvanı',
            'sponsor_id' => 'sponsor',
            'is_break' => 'ara oturumu',
            'moderator_ids' => 'moderatörler',
            'sort_order' => 'sıra numarası',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Salon seçimi zorunludur.',
            'venue_id.exists' => 'Seçilen salon bulunamadı.',
            'category_ids.array' => 'Kategoriler liste formatında olmalıdır.',
            'category_ids.*.exists' => 'Seçilen kategorilerden biri bulunamadı.',
            'title.required' => 'Oturum başlığı zorunludur.',
            'title.max' => 'Oturum başlığı en fazla 500 karakter olabilir.',
            'start_time.required' => 'Başlangıç saati zorunludur.',
            'start_time.date_format' => 'Başlangıç saati HH:MM formatında olmalıdır.',
            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.date_format' => 'Bitiş saati HH:MM formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            'session_type.required' => 'Oturum türü seçimi zorunludur.',
            'session_type.in' => 'Geçersiz oturum türü.',
            'moderator_title.max' => 'Moderatör unvanı en fazla 255 karakter olabilir.',
            'sponsor_id.exists' => 'Seçilen sponsor bulunamadı.',
            'moderator_ids.array' => 'Moderatörler liste formatında olmalıdır.',
            'moderator_ids.*.exists' => 'Seçilen moderatörlerden biri bulunamadı.',
            'sort_order.integer' => 'Sıra numarası sayı olmalıdır.',
            'sort_order.min' => 'Sıra numarası 0 veya daha büyük olmalıdır.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_break' => $this->boolean('is_break', false),
            'sort_order' => $this->sort_order ?? 0,
        ]);

        // Filter out empty moderator_ids and category_ids
        if ($this->has('moderator_ids')) {
            $moderatorIds = array_filter($this->moderator_ids ?? [], function($value) {
                return !empty($value);
            });
            $this->merge(['moderator_ids' => array_values($moderatorIds)]);
        }

        if ($this->has('category_ids')) {
            $categoryIds = array_filter($this->category_ids ?? [], function($value) {
                return !empty($value);
            });
            $this->merge(['category_ids' => array_values($categoryIds)]);
        }

        // Convert empty sponsor_id to null
        if ($this->sponsor_id === '') {
            $this->merge(['sponsor_id' => null]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if the session duration is reasonable (at least 15 minutes, max 8 hours)
            if ($this->start_time && $this->end_time) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
                $duration = $start->diffInMinutes($end);
                
                if ($duration < 15) {
                    $validator->errors()->add('end_time', 'Oturum süresi en az 15 dakika olmalıdır.');
                }
                
                if ($duration > 480) { // 8 hours
                    $validator->errors()->add('end_time', 'Oturum süresi en fazla 8 saat olabilir.');
                }
            }
            
            // If it's a break session, ensure title reflects this
            if ($this->is_break && $this->title) {
                $breakKeywords = ['ara', 'break', 'mola', 'kahve', 'öğle', 'lunch'];
                $hasBreakKeyword = false;
                
                foreach ($breakKeywords as $keyword) {
                    if (stripos($this->title, $keyword) !== false) {
                        $hasBreakKeyword = true;
                        break;
                    }
                }
                
                if (!$hasBreakKeyword) {
                    $validator->errors()->add('title', 'Ara oturumu için başlık ara/mola belirtmeli.');
                }
            }
        });
    }

    /**
     * Calculate duration in minutes.
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
}
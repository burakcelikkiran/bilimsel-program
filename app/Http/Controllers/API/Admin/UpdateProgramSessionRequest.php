<?php

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\ProgramSession;
use App\Models\Venue;

class UpdateProgramSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $session = $this->route('session');
        return $this->user()->can('update', $session);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $session = $this->route('session');
        
        return [
            'venue_id' => [
                'required',
                'exists:venues,id',
                function ($attribute, $value, $fail) {
                    // Check if venue belongs to the event
                    $event = $this->route('event');
                    $venue = Venue::with('eventDay')->find($value);
                    if (!$venue || $venue->eventDay->event_id !== $event->id) {
                        $fail('Selected venue does not belong to this event.');
                    }

                    // Check user access to venue's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                        if (!$userOrganizationIds->contains($venue->eventDay->event->organization_id)) {
                            $fail('You do not have access to this venue.');
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
                    // Check for time conflicts (excluding current session)
                    $session = $this->route('session');
                    if ($this->venue_id && $this->end_time) {
                        $conflicts = ProgramSession::where('venue_id', $this->venue_id)
                            ->where('id', '!=', $session->id)
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
                            $fail('Time conflict with existing session in this venue.');
                        }
                    }
                },
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    // Check minimum duration (15 minutes)
                    if ($this->start_time) {
                        $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                        $endTime = \Carbon\Carbon::createFromFormat('H:i', $value);
                        $duration = $endTime->diffInMinutes($startTime);
                        
                        if ($duration < 15) {
                            $fail('Session must be at least 15 minutes long.');
                        }
                        
                        if ($duration > 480) { // 8 hours
                            $fail('Session cannot be longer than 8 hours.');
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
                        // Check if sponsor belongs to the same organization
                        $event = $this->route('event');
                        $sponsor = \App\Models\Sponsor::find($value);
                        if ($sponsor && $sponsor->organization_id !== $event->organization_id) {
                            $fail('Selected sponsor does not belong to this organization.');
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
                    // Check if category belongs to the event
                    $event = $this->route('event');
                    $category = \App\Models\ProgramSessionCategory::find($value);
                    if ($category && $category->event_id !== $event->id) {
                        $fail('Selected category does not belong to this event.');
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
                    // Check if participant belongs to the same organization
                    $event = $this->route('event');
                    $participant = \App\Models\Participant::find($value);
                    if ($participant && $participant->organization_id !== $event->organization_id) {
                        $fail('Selected moderator does not belong to this organization.');
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'venue_id' => 'venue',
            'title' => 'session title',
            'description' => 'session description',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'session_type' => 'session type',
            'max_participants' => 'maximum participants',
            'is_break' => 'break session',
            'is_featured' => 'featured session',
            'requires_registration' => 'requires registration',
            'registration_limit' => 'registration limit',
            'sponsor_id' => 'sponsor',
            'category_ids' => 'categories',
            'moderator_ids' => 'moderators',
            'notes' => 'notes',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Venue is required.',
            'venue_id.exists' => 'Selected venue does not exist.',
            
            'title.required' => 'Session title is required.',
            'title.min' => 'Session title must be at least 3 characters.',
            'title.max' => 'Session title cannot exceed 500 characters.',
            
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            
            'session_type.required' => 'Session type is required.',
            'session_type.in' => 'Invalid session type selected.',
            
            'max_participants.min' => 'Maximum participants must be at least 1.',
            'max_participants.max' => 'Maximum participants cannot exceed 10,000.',
            
            'registration_limit.required_if' => 'Registration limit is required when registration is required.',
            'registration_limit.min' => 'Registration limit must be at least 1.',
            
            'sponsor_id.exists' => 'Selected sponsor does not exist.',
            
            'category_ids.max' => 'Maximum 5 categories allowed per session.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
            
            'moderator_ids.max' => 'Maximum 3 moderators allowed per session.',
            'moderator_ids.*.exists' => 'One or more selected moderators do not exist.',
            
            'notes.max' => 'Notes cannot exceed 1,000 characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $session = $this->route('session');

        // Set default values if not provided
        if (!$this->has('is_break')) {
            $this->merge(['is_break' => $session->is_break ?? false]);
        }

        if (!$this->has('is_featured')) {
            $this->merge(['is_featured' => $session->is_featured ?? false]);
        }

        if (!$this->has('requires_registration')) {
            $this->merge(['requires_registration' => $session->requires_registration ?? false]);
        }

        // Clean up array inputs
        if ($this->has('category_ids') && is_array($this->category_ids)) {
            $this->merge([
                'category_ids' => array_filter($this->category_ids, function($id) {
                    return !empty($id);
                })
            ]);
        }

        if ($this->has('moderator_ids') && is_array($this->moderator_ids)) {
            $this->merge([
                'moderator_ids' => array_filter($this->moderator_ids, function($id) {
                    return !empty($id);
                })
            ]);
        }
    }
}
<?php

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()->can('update', $event);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $event = $this->route('event');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                'unique:events,slug,' . $event->id,
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) {
                    // Check if user has access to this organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $userOrganizationIds = $user->organizations()->pluck('organizations.id');
                        if (!$userOrganizationIds->contains($value)) {
                            $fail('You do not have access to this organization.');
                        }
                    }
                },
            ],
            'is_published' => [
                'boolean',
            ],
            'max_participants' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'timezone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'registration_start_date' => [
                'nullable',
                'date',
                'before_or_equal:start_date',
            ],
            'registration_end_date' => [
                'nullable',
                'date',
                'after_or_equal:registration_start_date',
                'before_or_equal:end_date',
            ],
            'website_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'contact_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'event name',
            'slug' => 'event slug',
            'description' => 'event description',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'location' => 'location',
            'organization_id' => 'organization',
            'is_published' => 'publication status',
            'max_participants' => 'maximum participants',
            'timezone' => 'timezone',
            'registration_start_date' => 'registration start date',
            'registration_end_date' => 'registration end date',
            'website_url' => 'website URL',
            'contact_email' => 'contact email',
            'contact_phone' => 'contact phone',
            'notes' => 'notes',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Event name is required.',
            'name.min' => 'Event name must be at least 3 characters.',
            'name.max' => 'Event name cannot exceed 255 characters.',
            
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already taken.',
            
            'start_date.required' => 'Start date is required.',
            
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization does not exist.',
            
            'max_participants.min' => 'Maximum participants must be at least 1.',
            'max_participants.max' => 'Maximum participants cannot exceed 100,000.',
            
            'website_url.url' => 'Website URL must be a valid URL.',
            'contact_email.email' => 'Contact email must be a valid email address.',
            
            'registration_start_date.before_or_equal' => 'Registration start date must be before or equal to event start date.',
            'registration_end_date.after_or_equal' => 'Registration end date must be after or equal to registration start date.',
            'registration_end_date.before_or_equal' => 'Registration end date must be before or equal to event end date.',
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
        $event = $this->route('event');
        
        // Auto-generate slug if not provided and name changed
        if (!$this->has('slug') && $this->has('name') && $this->name !== $event->name) {
            $this->merge([
                'slug' => \App\Models\Event::createSlugFromTurkish($this->name),
            ]);
        }

        // Set timezone if not provided
        if (!$this->has('timezone') && !$event->timezone) {
            $this->merge(['timezone' => config('app.timezone', 'UTC')]);
        }
    }
}
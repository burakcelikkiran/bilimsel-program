<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->canManageOrganization(auth()->user()->currentOrganization);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $organizationId = auth()->user()->currentOrganization->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('venues')->where('organization_id', $organizationId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('venues')->where('organization_id', $organizationId),
            ],
            'description' => 'nullable|string|max:1000',
            'capacity' => 'nullable|integer|min:1|max:100000',
            'location' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:100',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string|max:100',
            'setup_notes' => 'nullable|string|max:1000',
            'technical_specs' => 'nullable|string|max:1000',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:100',
            'accessibility_features' => 'nullable|array',
            'accessibility_features.*' => 'string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'hourly_rate' => 'nullable|numeric|min:0|max:99999.99',
            'daily_rate' => 'nullable|numeric|min:0|max:99999.99',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'booking_rules' => 'nullable|string|max:2000',
            'cancellation_policy' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'salon adı',
            'slug' => 'salon kısa adı',
            'description' => 'açıklama',
            'capacity' => 'kapasite',
            'location' => 'konum',
            'floor' => 'kat',
            'facilities' => 'özellikler',
            'setup_notes' => 'kurulum notları',
            'technical_specs' => 'teknik özellikler',
            'equipment' => 'ekipmanlar',
            'accessibility_features' => 'erişilebilirlik özellikleri',
            'contact_person' => 'iletişim kişisi',
            'contact_phone' => 'iletişim telefonu',
            'contact_email' => 'iletişim e-postası',
            'hourly_rate' => 'saatlik ücret',
            'daily_rate' => 'günlük ücret',
            'is_active' => 'aktif durumu',
            'sort_order' => 'sıra numarası',
            'booking_rules' => 'rezervasyon kuralları',
            'cancellation_policy' => 'iptal politikası',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Salon adı zorunludur.',
            'name.unique' => 'Bu salon adı zaten kullanılıyor.',
            'slug.unique' => 'Bu salon kısa adı zaten kullanılıyor.',
            'slug.regex' => 'Salon kısa adı sadece küçük harf, rakam ve tire içerebilir.',
            'capacity.integer' => 'Kapasite sayı olmalıdır.',
            'capacity.min' => 'Kapasite en az 1 olmalıdır.',
            'capacity.max' => 'Kapasite en fazla 100.000 olabilir.',
            'contact_email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'hourly_rate.numeric' => 'Saatlik ücret sayı olmalıdır.',
            'daily_rate.numeric' => 'Günlük ücret sayı olmalıdır.',
            'facilities.array' => 'Özellikler liste formatında olmalıdır.',
            'equipment.array' => 'Ekipmanlar liste formatında olmalıdır.',
            'accessibility_features.array' => 'Erişilebilirlik özellikleri liste formatında olmalıdır.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge([
                'slug' => \App\Models\Venue::createSlugFromTurkish($this->name)
            ]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->sort_order ?? 0,
        ]);

        // Process facilities array
        if ($this->has('facilities')) {
            $facilities = array_filter(array_map('trim', $this->facilities ?? []), function($value) {
                return !empty($value);
            });
            $this->merge(['facilities' => array_values($facilities)]);
        }

        // Process equipment array
        if ($this->has('equipment')) {
            $equipment = array_filter(array_map('trim', $this->equipment ?? []), function($value) {
                return !empty($value);
            });
            $this->merge(['equipment' => array_values($equipment)]);
        }

        // Process accessibility features array
        if ($this->has('accessibility_features')) {
            $accessibilityFeatures = array_filter(array_map('trim', $this->accessibility_features ?? []), function($value) {
                return !empty($value);
            });
            $this->merge(['accessibility_features' => array_values($accessibilityFeatures)]);
        }
    }

    /**
     * Get the validated data with organization_id added.
     */
    public function validatedWithOrganization(): array
    {
        $validated = $this->validated();
        $validated['organization_id'] = auth()->user()->currentOrganization->id;
        
        return $validated;
    }
}
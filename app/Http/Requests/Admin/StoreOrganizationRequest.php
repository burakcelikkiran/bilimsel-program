<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organizations', 'name'),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('organizations', 'slug'),
            ],
            'description' => 'nullable|string|max:2000',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
            'settings.default_language' => 'nullable|string|in:tr,en',
            'settings.default_timezone' => 'nullable|string|max:50',
            'settings.email_notifications' => 'boolean',
            'settings.allow_public_events' => 'boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'organizasyon adı',
            'slug' => 'organizasyon kısa adı',
            'description' => 'açıklama',
            'contact_email' => 'iletişim e-posta adresi',
            'contact_phone' => 'iletişim telefon numarası',
            'address' => 'adres',
            'city' => 'şehir',
            'country' => 'ülke',
            'timezone' => 'saat dilimi',
            'logo' => 'logo',
            'is_active' => 'aktif durumu',
            'settings.default_language' => 'varsayılan dil',
            'settings.default_timezone' => 'varsayılan saat dilimi',
            'settings.email_notifications' => 'e-posta bildirimleri',
            'settings.allow_public_events' => 'herkese açık etkinlikler',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Organizasyon adı zorunludur.',
            'name.unique' => 'Bu organizasyon adı zaten kullanılıyor.',
            'slug.unique' => 'Bu organizasyon kısa adı zaten kullanılıyor.',
            'slug.regex' => 'Organizasyon kısa adı sadece küçük harf, rakam ve tire içerebilir.',
            'contact_email.email' => 'Geçerli bir iletişim e-posta adresi giriniz.',
            'logo.image' => 'Logo bir resim dosyası olmalıdır.',
            'logo.mimes' => 'Logo jpeg, png, jpg, gif veya svg formatında olmalıdır.',
            'logo.max' => 'Logo boyutu en fazla 2MB olabilir.',
            'settings.default_language.in' => 'Varsayılan dil Türkçe veya İngilizce olmalıdır.',
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
                'slug' => \App\Models\Organization::createSlugFromTurkish($this->name)
            ]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'country' => $this->country ?? 'Türkiye',
            'timezone' => $this->timezone ?? 'Europe/Istanbul',
        ]);

        // Prepare settings
        if ($this->has('settings')) {
            $settings = $this->settings ?? [];
            $settings['email_notifications'] = $this->boolean('settings.email_notifications', true);
            $settings['allow_public_events'] = $this->boolean('settings.allow_public_events', true);
            $settings['default_language'] = $settings['default_language'] ?? 'tr';
            $settings['default_timezone'] = $settings['default_timezone'] ?? 'Europe/Istanbul';
            
            $this->merge(['settings' => $settings]);
        }
    }
}
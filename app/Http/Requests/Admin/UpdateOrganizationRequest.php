<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return auth()->user()->isAdmin() ||
            auth()->user()->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organizations', 'name')->ignore($organization->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('organizations', 'slug')->ignore($organization->id),
            ],
            'description' => 'nullable|string|max:2000',
            'website_url' => 'nullable|url|max:500',
            'contact_email' => 'nullable|email|max:255',  // email → contact_email
            'contact_phone' => 'nullable|string|max:50',  // phone → contact_phone
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            '_remove_logo' => 'boolean',  // remove_logo → _remove_logo (Vue.js'deki alan adıyla uyumlu)
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
            'website_url' => 'website adresi',
            'contact_email' => 'e-posta adresi',    // email → contact_email
            'contact_phone' => 'telefon numarası',  // phone → contact_phone
            'address' => 'adres',
            'city' => 'şehir',
            'country' => 'ülke',
            'timezone' => 'saat dilimi',
            'logo' => 'logo',
            '_remove_logo' => 'logoyu kaldır',      // remove_logo → _remove_logo
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
            'website_url.url' => 'Geçerli bir website adresi giriniz.',
            'contact_email.email' => 'Geçerli bir e-posta adresi giriniz.',  // email → contact_email
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
        $organization = $this->route('organization');

        // Debug: Gelen veriyi logla
        \Log::info('Request data before prepare:', $this->all());

        // Auto-generate slug if not provided
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge([
                'slug' => \App\Models\Organization::createSlugFromTurkish($this->name)
            ]);
        }

        // Clean and format data - name field'ını koruyun
        $mergeData = [];

        if ($this->has('name') && $this->name) {
            $mergeData['name'] = trim($this->name);
        }

        if ($this->has('description')) {
            $mergeData['description'] = $this->description ? trim($this->description) : null;
        }

        if ($this->has('contact_email')) {
            $mergeData['contact_email'] = $this->contact_email ? strtolower(trim($this->contact_email)) : null;
        }

        if ($this->has('contact_phone')) {
            $mergeData['contact_phone'] = $this->contact_phone ? trim($this->contact_phone) : null;
        }

        $mergeData['_remove_logo'] = $this->boolean('_remove_logo', false);
        $mergeData['is_active'] = $this->boolean('is_active', true);

        $this->merge($mergeData);

        // Debug: İşlendikten sonraki veriyi logla
        \Log::info('Request data after prepare:', $this->all());

        // Format phone number if provided
        if ($this->contact_phone) {
            $phone = preg_replace('/[^0-9+]/', '', $this->contact_phone);
            // Add +90 prefix for Turkish numbers if missing
            if (strlen($phone) === 10 && !str_starts_with($phone, '0')) {
                $phone = '+90' . $phone;
            } elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) {
                $phone = '+90' . substr($phone, 1);
            }
            $this->merge(['contact_phone' => $phone]);
        }
    }
}

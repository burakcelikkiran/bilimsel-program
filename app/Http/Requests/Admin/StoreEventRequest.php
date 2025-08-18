<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization
        if ($this->organization_id) {
            return $user->organizations()->where('organizations.id', $this->organization_id)->exists();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if (!$user->isAdmin() && !$user->organizations()->where('organizations.id', $value)->exists()) {
                        $fail('Bu organizasyona etkinlik ekleyemezsiniz.');
                    }
                },
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Event::where('organization_id', $this->organization_id)
                        ->where('title', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Bu organizasyonda aynı isimde bir etkinlik zaten var.');
                    }
                },
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $exists = \App\Models\Event::where('organization_id', $this->organization_id)
                            ->where('slug', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Bu organizasyonda aynı slug zaten kullanılıyor.');
                        }
                    }
                },
            ],
            'description' => 'nullable|string|max:5000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'website_url' => 'nullable|url|max:500',
            'registration_url' => 'nullable|url|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'max_participants' => 'nullable|integer|min:1|max:100000',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url|max:500',
            'social_links.twitter' => 'nullable|url|max:500',
            'social_links.linkedin' => 'nullable|url|max:500',
            'social_links.instagram' => 'nullable|url|max:500',
            'social_links.youtube' => 'nullable|url|max:500',
            'auto_create_days' => 'boolean',
            'settings' => 'nullable|array',
            'settings.allow_public_access' => 'boolean',
            'settings.require_registration' => 'boolean',
            'settings.auto_generate_days' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'organization_id' => 'organizasyon',
            'title' => 'etkinlik başlığı',
            'slug' => 'etkinlik kısa adı',
            'description' => 'açıklama',
            'start_date' => 'başlangıç tarihi',
            'end_date' => 'bitiş tarihi',
            'timezone' => 'saat dilimi',
            'location' => 'konum',
            'address' => 'adres',
            'website_url' => 'website adresi',
            'registration_url' => 'kayıt adresi',
            'contact_email' => 'iletişim e-postası',
            'contact_phone' => 'iletişim telefonu',
            'max_participants' => 'maksimum katılımcı sayısı',
            'banner' => 'banner resmi',
            'poster' => 'poster resmi',
            'logo' => 'logo',
            'is_published' => 'yayında',
            'is_featured' => 'öne çıkarılmış',
            'auto_create_days' => 'günleri otomatik oluştur',
            'social_links.facebook' => 'Facebook adresi',
            'social_links.twitter' => 'Twitter adresi',
            'social_links.linkedin' => 'LinkedIn adresi',
            'social_links.instagram' => 'Instagram adresi',
            'social_links.youtube' => 'YouTube adresi',
            'settings.allow_public_access' => 'herkese açık erişim',
            'settings.require_registration' => 'kayıt zorunlu',
            'settings.auto_generate_days' => 'otomatik gün oluştur',
            'tags' => 'etiketler',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organizasyon seçimi zorunludur.',
            'organization_id.exists' => 'Seçilen organizasyon bulunamadı.',
            'title.required' => 'Etkinlik başlığı zorunludur.',
            'title.max' => 'Etkinlik başlığı en fazla 255 karakter olabilir.',
            'slug.regex' => 'Etkinlik kısa adı sadece küçük harf, rakam ve tire içerebilir.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'start_date.after_or_equal' => 'Başlangıç tarihi bugünden önce olamaz.',
            'end_date.required' => 'Bitiş tarihi zorunludur.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'website_url.url' => 'Geçerli bir website adresi giriniz.',
            'registration_url.url' => 'Geçerli bir kayıt adresi giriniz.',
            'contact_email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'max_participants.min' => 'Maksimum katılımcı sayısı en az 1 olmalıdır.',
            'max_participants.max' => 'Maksimum katılımcı sayısı en fazla 100.000 olabilir.',
            'banner.image' => 'Banner bir resim dosyası olmalıdır.',
            'banner.max' => 'Banner boyutu en fazla 5MB olabilir.',
            'poster.image' => 'Poster bir resim dosyası olmalıdır.',
            'poster.max' => 'Poster boyutu en fazla 5MB olabilir.',
            'logo.image' => 'Logo bir resim dosyası olmalıdır.',
            'logo.max' => 'Logo boyutu en fazla 2MB olabilir.',
            'social_links.facebook.url' => 'Geçerli bir Facebook adresi giriniz.',
            'social_links.twitter.url' => 'Geçerli bir Twitter adresi giriniz.',
            'social_links.linkedin.url' => 'Geçerli bir LinkedIn adresi giriniz.',
            'social_links.instagram.url' => 'Geçerli bir Instagram adresi giriniz.',
            'social_links.youtube.url' => 'Geçerli bir YouTube adresi giriniz.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (empty($this->slug) && !empty($this->title)) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
        }

        // Set default values
        $this->merge([
            'is_published' => $this->boolean('is_published', false),
            'is_featured' => $this->boolean('is_featured', false),
            'auto_create_days' => $this->boolean('auto_create_days', true),
            'timezone' => $this->timezone ?? 'Europe/Istanbul',
        ]);

        // Prepare social links
        if ($this->has('social_links')) {
            $socialLinks = array_filter($this->social_links ?? [], function($value) {
                return !empty($value);
            });
            $this->merge(['social_links' => $socialLinks]);
        }

        // Prepare settings
        if ($this->has('settings')) {
            $settings = $this->settings ?? [];
            $settings['allow_public_access'] = $this->boolean('settings.allow_public_access', true);
            $settings['require_registration'] = $this->boolean('settings.require_registration', false);
            $settings['auto_generate_days'] = $this->boolean('settings.auto_generate_days', true);
            
            $this->merge(['settings' => $settings]);
        }

        // Prepare tags
        if ($this->has('tags')) {
            $tags = array_filter(array_map('trim', $this->tags ?? []), function($value) {
                return !empty($value);
            });
            $this->merge(['tags' => array_values($tags)]);
        }
    }
}
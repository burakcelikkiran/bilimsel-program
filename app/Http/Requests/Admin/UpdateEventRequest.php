<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the event's organization
        return $user->organizations()->where('organizations.id', $event->organization_id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $event = $this->route('event');

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
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('events', 'name')
                    ->where('organization_id', $this->organization_id ?? $event->organization_id)
                    ->ignore($event->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('events', 'slug')
                    ->where('organization_id', $event->organization_id)
                    ->ignore($event->id),
            ],
            'description' => 'nullable|string|max:5000',
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($event) {
                    // Allow past dates for existing events if they have sessions
                    if ($event->programSessions()->exists() && $value !== $event->start_date) {
                        if (\Carbon\Carbon::parse($value)->isPast()) {
                            $fail('Program oturumları olan etkinliğin tarihi geçmişe alınamaz.');
                        }
                    }
                },
            ],
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
            'remove_banner' => 'boolean',
            'remove_poster' => 'boolean',
            'remove_logo' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url|max:500',
            'social_links.twitter' => 'nullable|url|max:500',
            'social_links.linkedin' => 'nullable|url|max:500',
            'social_links.instagram' => 'nullable|url|max:500',
            'social_links.youtube' => 'nullable|url|max:500',
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
            'remove_banner' => 'bannerı kaldır',
            'remove_poster' => 'posterı kaldır',
            'remove_logo' => 'logoyu kaldır',
            'is_published' => 'yayında',
            'is_featured' => 'öne çıkarılmış',
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
            'title.unique' => 'Bu organizasyonda aynı isimde bir etkinlik zaten var.',
            'slug.unique' => 'Bu organizasyonda aynı slug zaten kullanılıyor.',
            'slug.regex' => 'Etkinlik kısa adı sadece küçük harf, rakam ve tire içerebilir.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
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
        $event = $this->route('event');
        
        // If title is provided, use it as the name for validation
        if ($this->has('title') && !$this->has('name')) {
            $this->merge([
                'name' => $this->title
            ]);
        }
        
        // Set default values
        $this->merge([
            'is_published' => $this->boolean('is_published', false),
            'is_featured' => $this->boolean('is_featured', false),
        ]);
        
        // Prepare social links if they exist
        if ($this->has('social_links')) {
            $socialLinks = array_filter($this->social_links ?? [], function($value) {
                return !empty($value);
            });
            $this->merge(['social_links' => $socialLinks]);
        }

        // Auto-generate slug if not provided
        if (empty($this->slug) && !empty($this->title)) {
            $this->merge([
                'slug' => \App\Models\Event::createSlugFromTurkish($this->title)
            ]);
        }

        // Set boolean values
        $this->merge([
            'remove_banner' => $this->boolean('remove_banner', false),
            'remove_poster' => $this->boolean('remove_poster', false),
            'remove_logo' => $this->boolean('remove_logo', false),
        ]);

        // Prepare social links
        if ($this->has('social_links')) {
            $currentSocialLinks = $event->social_links ?? [];
            $newSocialLinks = array_filter($this->social_links ?? [], function($value) {
                return !empty($value);
            });
            $this->merge(['social_links' => array_merge($currentSocialLinks, $newSocialLinks)]);
        }

        // Prepare settings
        if ($this->has('settings')) {
            $currentSettings = $event->settings ?? [];
            $newSettings = $this->settings ?? [];
            
            $settings = array_merge($currentSettings, $newSettings);
            $settings['allow_public_access'] = $this->boolean('settings.allow_public_access', $settings['allow_public_access'] ?? true);
            $settings['require_registration'] = $this->boolean('settings.require_registration', $settings['require_registration'] ?? false);
            $settings['auto_generate_days'] = $this->boolean('settings.auto_generate_days', $settings['auto_generate_days'] ?? true);
            
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
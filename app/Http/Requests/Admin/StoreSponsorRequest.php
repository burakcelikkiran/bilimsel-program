<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Organization;

class StoreSponsorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Admin can create sponsors for any organization
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization they're trying to add sponsor to
        if ($this->organization_id) {
            return $user->organizations()
                       ->where('organizations.id', $this->organization_id)
                       ->exists();
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
                    $organization = Organization::find($value);
                    if (!$organization || !$organization->is_active) {
                        $fail('Seçilen organizasyon aktif değil.');
                        return;
                    }
                    
                    // Check if user has access to this organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $value)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu organizasyona sponsor ekleme yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                function ($attribute, $value, $fail) {
                    // Check for duplicate sponsor name within the same organization
                    $exists = \App\Models\Sponsor::where('organization_id', $this->organization_id)
                        ->where('name', trim($value))
                        ->exists();
                    
                    if ($exists) {
                        $fail('Bu organizasyonda aynı isimde bir sponsor zaten mevcut.');
                    }
                }
            ],
            
            'sponsor_level' => [
                'required',
                'in:platinum,gold,silver,bronze',
            ],
            
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            
            'website_url' => [
                'nullable',
                'url',
                'max:500',
                'active_url',
            ],
            
            'contact_email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    // Check for blacklisted domains
                    $blacklistedDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com'];
                    $domain = substr(strrchr($value, "@"), 1);
                    if (in_array(strtolower($domain), $blacklistedDomains)) {
                        $fail('Geçici e-posta adresleri kabul edilmez.');
                    }
                }
            ],
            
            'contact_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    // Turkish phone number validation
                    $cleanPhone = preg_replace('/[^0-9]/', '', $value);
                    
                    if (str_starts_with($cleanPhone, '90')) {
                        $cleanPhone = substr($cleanPhone, 2);
                    }
                    
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = substr($cleanPhone, 1);
                    }
                    
                    // Turkish landline numbers should be 10 digits
                    if (strlen($cleanPhone) === 10) {
                        return; // Valid Turkish number
                    }
                    
                    // International numbers (7-15 digits)
                    if (strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15) {
                        return; // Valid international
                    }
                    
                    $fail('Geçerli bir telefon numarası giriniz.');
                }
            ],
            
            'contact_person' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÇĞİÖŞÜçğıöşü\s\-\'\.]+$/',
            ],
            
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,svg',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            
            'is_active' => [
                'boolean',
            ],
            
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999',
            ],
            
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
            
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            
            'country' => [
                'nullable',
                'string',
                'max:100',
            ],
            
            'tax_number' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9\-]+$/',
                'unique:sponsors,tax_number',
            ],
            
            'contract_start_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            
            'contract_end_date' => [
                'nullable',
                'date',
                'after:contract_start_date',
            ],
            
            'contract_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999',
            ],
            
            'contract_currency' => [
                'nullable',
                'string',
                'in:TRY,USD,EUR,GBP',
            ],
            
            'payment_status' => [
                'nullable',
                'in:pending,partial,paid,overdue',
            ],
            
            'benefits' => [
                'nullable',
                'array',
                'max:20',
            ],
            
            'benefits.*' => [
                'string',
                'max:255',
                'distinct',
            ],
            
            'social_media' => [
                'nullable',
                'array',
            ],
            
            'social_media.facebook' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?facebook\.com\/[a-zA-Z0-9\.\-_]+\/?$/',
            ],
            
            'social_media.twitter' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?twitter\.com\/[a-zA-Z0-9_]+\/?$/',
            ],
            
            'social_media.linkedin' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?linkedin\.com\/(company|in)\/[a-zA-Z0-9\-]+\/?$/',
            ],
            
            'social_media.instagram' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?instagram\.com\/[a-zA-Z0-9\.\-_]+\/?$/',
            ],
            
            'industry' => [
                'nullable',
                'string',
                'max:100',
                'in:healthcare,pharmaceutical,technology,medical_devices,education,finance,retail,manufacturing,consulting,other',
            ],
            
            'company_size' => [
                'nullable',
                'in:startup,small,medium,large,enterprise',
            ],
            
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            
            'tags' => [
                'nullable',
                'array',
                'max:10',
            ],
            
            'tags.*' => [
                'string',
                'max:50',
                'distinct',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organizasyon seçimi zorunludur.',
            'organization_id.exists' => 'Seçilen organizasyon bulunamadı.',
            
            'name.required' => 'Sponsor adı zorunludur.',
            'name.string' => 'Sponsor adı metin formatında olmalıdır.',
            'name.max' => 'Sponsor adı en fazla 255 karakter olabilir.',
            'name.min' => 'Sponsor adı en az 2 karakter olmalıdır.',
            
            'sponsor_level.required' => 'Sponsor seviyesi seçimi zorunludur.',
            'sponsor_level.in' => 'Geçersiz sponsor seviyesi seçildi.',
            
            'description.string' => 'Açıklama metin formatında olmalıdır.',
            'description.max' => 'Açıklama en fazla 2000 karakter olabilir.',
            
            'website_url.url' => 'Geçerli bir website adresi giriniz.',
            'website_url.max' => 'Website adresi en fazla 500 karakter olabilir.',
            'website_url.active_url' => 'Website adresi erişilebilir olmalıdır.',
            
            'contact_email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'contact_email.max' => 'E-posta adresi en fazla 255 karakter olabilir.',
            
            'contact_phone.string' => 'Telefon numarası metin formatında olmalıdır.',
            'contact_phone.max' => 'Telefon numarası en fazla 20 karakter olabilir.',
            'contact_phone.regex' => 'Geçerli bir telefon numarası formatı giriniz.',
            
            'contact_person.string' => 'İletişim kişisi metin formatında olmalıdır.',
            'contact_person.max' => 'İletişim kişisi en fazla 255 karakter olabilir.',
            'contact_person.regex' => 'İletişim kişisi sadece harf, boşluk, tire ve nokta içerebilir.',
            
            'logo.image' => 'Logo geçerli bir resim dosyası olmalıdır.',
            'logo.mimes' => 'Logo JPEG, JPG, PNG, GIF veya SVG formatında olmalıdır.',
            'logo.max' => 'Logo boyutu en fazla 2MB olabilir.',
            'logo.dimensions' => 'Logo en az 100x100, en fazla 2000x2000 piksel olmalıdır.',
            
            'is_active.boolean' => 'Durum seçimi doğru/yanlış değer olmalıdır.',
            
            'sort_order.integer' => 'Sıralama sayı formatında olmalıdır.',
            'sort_order.min' => 'Sıralama 0\'dan küçük olamaz.',
            'sort_order.max' => 'Sıralama 999\'dan büyük olamaz.',
            
            'address.string' => 'Adres metin formatında olmalıdır.',
            'address.max' => 'Adres en fazla 500 karakter olabilir.',
            
            'city.string' => 'Şehir metin formatında olmalıdır.',
            'city.max' => 'Şehir en fazla 100 karakter olabilir.',
            
            'country.string' => 'Ülke metin formatında olmalıdır.',
            'country.max' => 'Ülke en fazla 100 karakter olabilir.',
            
            'tax_number.string' => 'Vergi numarası metin formatında olmalıdır.',
            'tax_number.max' => 'Vergi numarası en fazla 50 karakter olabilir.',
            'tax_number.regex' => 'Vergi numarası sadece rakam ve tire içerebilir.',
            'tax_number.unique' => 'Bu vergi numarası zaten kullanılıyor.',
            
            'contract_start_date.date' => 'Sözleşme başlangıç tarihi geçerli bir tarih olmalıdır.',
            'contract_start_date.after_or_equal' => 'Sözleşme başlangıç tarihi bugün veya sonrası olmalıdır.',
            
            'contract_end_date.date' => 'Sözleşme bitiş tarihi geçerli bir tarih olmalıdır.',
            'contract_end_date.after' => 'Sözleşme bitiş tarihi başlangıç tarihinden sonra olmalıdır.',
            
            'contract_amount.numeric' => 'Sözleşme tutarı sayı formatında olmalıdır.',
            'contract_amount.min' => 'Sözleşme tutarı 0\'dan küçük olamaz.',
            'contract_amount.max' => 'Sözleşme tutarı çok yüksek.',
            
            'contract_currency.in' => 'Geçersiz para birimi seçildi.',
            
            'payment_status.in' => 'Geçersiz ödeme durumu seçildi.',
            
            'benefits.array' => 'Avantajlar dizi formatında olmalıdır.',
            'benefits.max' => 'En fazla 20 avantaj eklenebilir.',
            'benefits.*.string' => 'Avantaj metin formatında olmalıdır.',
            'benefits.*.max' => 'Avantaj en fazla 255 karakter olabilir.',
            'benefits.*.distinct' => 'Avantajlar tekrar edemez.',
            
            'social_media.array' => 'Sosyal medya bilgileri dizi formatında olmalıdır.',
            'social_media.facebook.url' => 'Geçerli bir Facebook adresi giriniz.',
            'social_media.facebook.regex' => 'Facebook profil adres formatı hatalı.',
            'social_media.twitter.url' => 'Geçerli bir Twitter adresi giriniz.',
            'social_media.twitter.regex' => 'Twitter profil adres formatı hatalı.',
            'social_media.linkedin.url' => 'Geçerli bir LinkedIn adresi giriniz.',
            'social_media.linkedin.regex' => 'LinkedIn profil adres formatı hatalı.',
            'social_media.instagram.url' => 'Geçerli bir Instagram adresi giriniz.',
            'social_media.instagram.regex' => 'Instagram profil adres formatı hatalı.',
            
            'industry.string' => 'Sektör metin formatında olmalıdır.',
            'industry.max' => 'Sektör en fazla 100 karakter olabilir.',
            'industry.in' => 'Geçersiz sektör seçildi.',
            
            'company_size.in' => 'Geçersiz şirket büyüklüğü seçildi.',
            
            'notes.string' => 'Notlar metin formatında olmalıdır.',
            'notes.max' => 'Notlar en fazla 1000 karakter olabilir.',
            
            'tags.array' => 'Etiketler dizi formatında olmalıdır.',
            'tags.max' => 'En fazla 10 etiket eklenebilir.',
            'tags.*.string' => 'Etiket metin formatında olmalıdır.',
            'tags.*.max' => 'Etiket en fazla 50 karakter olabilir.',
            'tags.*.distinct' => 'Etiketler tekrar edemez.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'organization_id' => 'organizasyon',
            'name' => 'sponsor adı',
            'sponsor_level' => 'sponsor seviyesi',
            'description' => 'açıklama',
            'website_url' => 'website',
            'contact_email' => 'iletişim e-postası',
            'contact_phone' => 'iletişim telefonu',
            'contact_person' => 'iletişim kişisi',
            'logo' => 'logo',
            'is_active' => 'durum',
            'sort_order' => 'sıralama',
            'address' => 'adres',
            'city' => 'şehir',
            'country' => 'ülke',
            'tax_number' => 'vergi numarası',
            'contract_start_date' => 'sözleşme başlangıç tarihi',
            'contract_end_date' => 'sözleşme bitiş tarihi',
            'contract_amount' => 'sözleşme tutarı',
            'contract_currency' => 'para birimi',
            'payment_status' => 'ödeme durumu',
            'benefits' => 'avantajlar',
            'social_media' => 'sosyal medya',
            'industry' => 'sektör',
            'company_size' => 'şirket büyüklüğü',
            'notes' => 'notlar',
            'tags' => 'etiketler',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data before validation
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'description' => $this->description ? trim($this->description) : null,
            'website_url' => $this->website_url ? $this->formatUrl($this->website_url) : null,
            'contact_email' => $this->contact_email ? strtolower(trim($this->contact_email)) : null,
            'contact_person' => $this->contact_person ? trim($this->contact_person) : null,
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->sort_order ? (int) $this->sort_order : null,
            'address' => $this->address ? trim($this->address) : null,
            'city' => $this->city ? trim($this->city) : null,
            'country' => $this->country ? trim($this->country) : 'Türkiye',
            'contract_currency' => $this->contract_currency ?: 'TRY',
            'payment_status' => $this->payment_status ?: 'pending',
            'notes' => $this->notes ? trim($this->notes) : null,
        ]);

        // Format phone number
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

        // Clean arrays
        if ($this->has('benefits') && is_array($this->benefits)) {
            $benefits = array_filter(array_map('trim', $this->benefits));
            $this->merge(['benefits' => array_values($benefits)]);
        }

        if ($this->has('tags') && is_array($this->tags)) {
            $tags = array_filter(array_map('trim', $this->tags));
            $this->merge(['tags' => array_values($tags)]);
        }

        // Clean social media URLs
        if ($this->has('social_media') && is_array($this->social_media)) {
            $socialMedia = [];
            foreach ($this->social_media as $platform => $url) {
                if (!empty($url)) {
                    $socialMedia[$platform] = $this->formatUrl($url);
                }
            }
            $this->merge(['social_media' => $socialMedia]);
        }
    }

    /**
     * Format URL to ensure it has proper protocol
     */
    private function formatUrl($url): string
    {
        $url = trim($url);
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateSponsorLevel($validator);
            $this->validateContract($validator);
            $this->validateBusinessInformation($validator);
        });
    }

    /**
     * Validate sponsor level constraints
     */
    protected function validateSponsorLevel($validator): void
    {
        if (!$this->sponsor_level || !$this->organization_id) {
            return;
        }

        // Check sponsor level limits per organization
        $levelLimits = [
            'platinum' => 2,
            'gold' => 5,
            'silver' => 10,
            'bronze' => 20,
        ];

        $currentCount = \App\Models\Sponsor::where('organization_id', $this->organization_id)
            ->where('sponsor_level', $this->sponsor_level)
            ->where('is_active', true)
            ->count();

        $limit = $levelLimits[$this->sponsor_level] ?? 999;

        if ($currentCount >= $limit) {
            $validator->errors()->add('sponsor_level', 
                "Bu organizasyonda {$this->sponsor_level} seviyesinde maksimum sponsor sayısına ({$limit}) ulaşılmış.");
        }

        // Validate contract amount based on sponsor level
        if ($this->contract_amount) {
            $minAmounts = [
                'platinum' => 50000,
                'gold' => 25000,
                'silver' => 10000,
                'bronze' => 5000,
            ];

            $minAmount = $minAmounts[$this->sponsor_level] ?? 0;
            if ($this->contract_amount < $minAmount) {
                $validator->errors()->add('contract_amount', 
                    "{$this->sponsor_level} seviyesi için minimum tutar: " . number_format($minAmount) . " {$this->contract_currency}");
            }
        }
    }

    /**
     * Validate contract information
     */
    protected function validateContract($validator): void
    {
        // If contract amount is provided, dates should also be provided
        if ($this->contract_amount && (!$this->contract_start_date || !$this->contract_end_date)) {
            $validator->errors()->add('contract_start_date', 
                'Sözleşme tutarı girildiğinde başlangıç ve bitiş tarihleri zorunludur.');
        }

        // Contract duration validation
        if ($this->contract_start_date && $this->contract_end_date) {
            $start = \Carbon\Carbon::parse($this->contract_start_date);
            $end = \Carbon\Carbon::parse($this->contract_end_date);
            $duration = $start->diffInDays($end);

            if ($duration < 30) {
                $validator->errors()->add('contract_end_date', 
                    'Sözleşme süresi en az 30 gün olmalıdır.');
            }

            if ($duration > 1095) { // 3 years
                $validator->errors()->add('contract_end_date', 
                    'Sözleşme süresi en fazla 3 yıl olabilir.');
            }
        }

        // Payment status validation
        if ($this->payment_status === 'paid' && !$this->contract_amount) {
            $validator->errors()->add('payment_status', 
                'Ödeme durumu "ödendi" seçildiğinde sözleşme tutarı zorunludur.');
        }
    }

    /**
     * Validate business information consistency
     */
    protected function validateBusinessInformation($validator): void
    {
        // Industry validation based on sponsor level
        if ($this->industry === 'healthcare' || $this->industry === 'pharmaceutical') {
            // Healthcare sponsors might have additional requirements
            if ($this->sponsor_level === 'platinum' && empty($this->tax_number)) {
                $validator->errors()->add('tax_number', 
                    'Sağlık sektöründeki platin sponsorlar için vergi numarası zorunludur.');
            }
        }

        // Company size vs contract amount consistency
        if ($this->company_size && $this->contract_amount) {
            $expectedRanges = [
                'startup' => [0, 10000],
                'small' => [5000, 25000],
                'medium' => [15000, 75000],
                'large' => [50000, 200000],
                'enterprise' => [100000, 999999999],
            ];

            if (isset($expectedRanges[$this->company_size])) {
                [$min, $max] = $expectedRanges[$this->company_size];
                if ($this->contract_amount < $min || $this->contract_amount > $max) {
                    $validator->errors()->add('contract_amount', 
                        "Şirket büyüklüğü '{$this->company_size}' için beklenen tutar aralığı: " . 
                        number_format($min) . " - " . number_format($max) . " {$this->contract_currency}");
                }
            }
        }

        // Social media validation
        if ($this->has('social_media') && is_array($this->social_media)) {
            $socialMedia = $this->social_media;
            
            // Check for duplicate URLs across platforms
            $urls = array_filter($socialMedia);
            if (count($urls) !== count(array_unique($urls))) {
                $validator->errors()->add('social_media', 
                    'Aynı URL birden fazla sosyal medya platformu için kullanılamaz.');
            }
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Set default sort order if not provided
        if (!isset($validated['sort_order']) || is_null($validated['sort_order'])) {
            $maxOrder = \App\Models\Sponsor::where('organization_id', $this->organization_id)
                                         ->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Sponsor;
use App\Models\Organization;

class UpdateSponsorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sponsor = $this->route('sponsor');
        
        if (!$sponsor) {
            return false;
        }

        $user = $this->user();
        
        // Admin can update any sponsor
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the sponsor
        return $user->organizations()
                   ->where('organizations.id', $sponsor->organization_id)
                   ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $sponsor = $this->route('sponsor');
        
        return [
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) use ($sponsor) {
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
                    
                    // If changing organization, check for existing commitments
                    if ($value != $sponsor->organization_id) {
                        $hasCommitments = $sponsor->programSessions()->exists() || $sponsor->presentations()->exists();
                        if ($hasCommitments) {
                            $fail('Sponsorun mevcut oturum veya sunumları bulunduğu için organizasyon değiştirilemez.');
                        }
                    }
                }
            ],
            
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                function ($attribute, $value, $fail) use ($sponsor) {
                    // Check for duplicate sponsor name within the same organization, excluding current sponsor
                    $exists = Sponsor::where('organization_id', $this->organization_id)
                        ->where('name', trim($value))
                        ->where('id', '!=', $sponsor->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Bu organizasyonda aynı isimde bir sponsor zaten mevcut.');
                    }
                }
            ],
            
            'sponsor_level' => [
                'required',
                'in:platinum,gold,silver,bronze',
                function ($attribute, $value, $fail) use ($sponsor) {
                    // If changing sponsor level, validate new level constraints
                    if ($value !== $sponsor->sponsor_level) {
                        $this->validateLevelChange($value, $sponsor, $fail);
                    }
                }
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
                    
                    // Phone number validation (same as store request)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $value);
                    
                    if (str_starts_with($cleanPhone, '90')) {
                        $cleanPhone = substr($cleanPhone, 2);
                    }
                    
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = substr($cleanPhone, 1);
                    }
                    
                    if (strlen($cleanPhone) === 10) {
                        return; // Valid Turkish number
                    }
                    
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
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
            
            'remove_logo' => [
                'boolean',
            ],
            
            'is_active' => [
                'boolean',
                function ($attribute, $value, $fail) use ($sponsor) {
                    // If deactivating, check for future commitments
                    if (!$value && $sponsor->is_active) {
                        $futureCommitments = $sponsor->programSessions()
                            ->whereHas('venue.eventDay.event', function ($query) {
                                $query->where('end_date', '>=', now()->toDateString());
                            })
                            ->exists();
                        
                        $futurePresentations = $sponsor->presentations()
                            ->whereHas('programSession.venue.eventDay.event', function ($query) {
                                $query->where('end_date', '>=', now()->toDateString());
                            })
                            ->exists();

                        if ($futureCommitments || $futurePresentations) {
                            $fail('Sponsorun gelecekteki etkinliklerde görevleri bulunduğu için pasif hale getirilemez.');
                        }
                    }
                }
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
                Rule::unique('sponsors')->ignore($sponsor->id),
            ],
            
            'contract_start_date' => [
                'nullable',
                'date',
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
                function ($attribute, $value, $fail) use ($sponsor) {
                    // Validate payment status changes
                    if ($value === 'paid' && $sponsor->payment_status !== 'paid') {
                        if (!$this->contract_amount && !$sponsor->contract_amount) {
                            $fail('Ödeme durumu "ödendi" olarak değiştirilmeden önce sözleşme tutarı girilmelidir.');
                        }
                    }
                    
                    if ($value === 'overdue' && $sponsor->payment_status === 'paid') {
                        $fail('Ödenmiş bir sözleşme vadesiz hale getirilemez.');
                    }
                }
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
            
            'internal_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            
            'rating' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
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
            
            'renewal_reminder_date' => [
                'nullable',
                'date',
                'after:today',
            ],
            
            'last_contact_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            
            'next_follow_up_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * Validate sponsor level change
     */
    private function validateLevelChange($newLevel, $sponsor, $fail): void
    {
        // Check if new level quota is available
        $levelLimits = [
            'platinum' => 2,
            'gold' => 5,
            'silver' => 10,
            'bronze' => 20,
        ];

        $currentCount = Sponsor::where('organization_id', $this->organization_id)
            ->where('sponsor_level', $newLevel)
            ->where('is_active', true)
            ->where('id', '!=', $sponsor->id)
            ->count();

        $limit = $levelLimits[$newLevel] ?? 999;

        if ($currentCount >= $limit) {
            $fail("Bu organizasyonda {$newLevel} seviyesinde maksimum sponsor sayısına ({$limit}) ulaşılmış.");
        }

        // Check if downgrading is allowed
        $levelHierarchy = ['platinum' => 4, 'gold' => 3, 'silver' => 2, 'bronze' => 1];
        $currentLevelValue = $levelHierarchy[$sponsor->sponsor_level] ?? 0;
        $newLevelValue = $levelHierarchy[$newLevel] ?? 0;

        if ($newLevelValue < $currentLevelValue) {
            // Downgrading - check for existing commitments at current level
            $hasHighLevelCommitments = $sponsor->programSessions()
                ->whereHas('venue.eventDay.event', function ($query) {
                    $query->where('end_date', '>=', now()->toDateString());
                })
                ->exists();

            if ($hasHighLevelCommitments) {
                $fail('Sponsorun gelecekteki etkinliklerde taahhütleri bulunduğu için seviye düşürülemez.');
            }
        }
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
            
            'remove_logo.boolean' => 'Logo kaldırma seçimi doğru/yanlış değer olmalıdır.',
            
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
            
            'internal_notes.string' => 'İç notlar metin formatında olmalıdır.',
            'internal_notes.max' => 'İç notlar en fazla 2000 karakter olabilir.',
            
            'rating.integer' => 'Değerlendirme sayı formatında olmalıdır.',
            'rating.min' => 'Değerlendirme en az 1 olmalıdır.',
            'rating.max' => 'Değerlendirme en fazla 5 olabilir.',
            
            'tags.array' => 'Etiketler dizi formatında olmalıdır.',
            'tags.max' => 'En fazla 10 etiket eklenebilir.',
            'tags.*.string' => 'Etiket metin formatında olmalıdır.',
            'tags.*.max' => 'Etiket en fazla 50 karakter olabilir.',
            'tags.*.distinct' => 'Etiketler tekrar edemez.',
            
            'renewal_reminder_date.date' => 'Yenileme hatırlatma tarihi geçerli olmalıdır.',
            'renewal_reminder_date.after' => 'Yenileme hatırlatma tarihi gelecekte olmalıdır.',
            
            'last_contact_date.date' => 'Son iletişim tarihi geçerli olmalıdır.',
            'last_contact_date.before_or_equal' => 'Son iletişim tarihi bugün veya öncesi olmalıdır.',
            
            'next_follow_up_date.date' => 'Sonraki takip tarihi geçerli olmalıdır.',
            'next_follow_up_date.after_or_equal' => 'Sonraki takip tarihi bugün veya sonrası olmalıdır.',
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
            'remove_logo' => 'logo kaldır',
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
            'internal_notes' => 'iç notlar',
            'rating' => 'değerlendirme',
            'tags' => 'etiketler',
            'renewal_reminder_date' => 'yenileme hatırlatma tarihi',
            'last_contact_date' => 'son iletişim tarihi',
            'next_follow_up_date' => 'sonraki takip tarihi',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data before validation (same as store request)
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'description' => $this->description ? trim($this->description) : null,
            'website_url' => $this->website_url ? $this->formatUrl($this->website_url) : null,
            'contact_email' => $this->contact_email ? strtolower(trim($this->contact_email)) : null,
            'contact_person' => $this->contact_person ? trim($this->contact_person) : null,
            'is_active' => $this->boolean('is_active'),
            'remove_logo' => $this->boolean('remove_logo'),
            'sort_order' => $this->sort_order ? (int) $this->sort_order : null,
            'address' => $this->address ? trim($this->address) : null,
            'city' => $this->city ? trim($this->city) : null,
            'country' => $this->country ? trim($this->country) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
            'internal_notes' => $this->internal_notes ? trim($this->internal_notes) : null,
            'rating' => $this->rating ? (int) $this->rating : null,
        ]);

        // Format phone number (same logic as store request)
        if ($this->contact_phone) {
            $phone = preg_replace('/[^0-9+]/', '', $this->contact_phone);
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
            $this->validateDataChanges($validator);
        });
    }

    /**
     * Validate sponsor level constraints (same as store request)
     */
    protected function validateSponsorLevel($validator): void
    {
        if (!$this->sponsor_level || !$this->organization_id) {
            return;
        }

        $sponsor = $this->route('sponsor');

        // Only validate if level is changing
        if ($this->sponsor_level === $sponsor->sponsor_level) {
            return;
        }

        // Validate contract amount based on new sponsor level
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
     * Validate contract information (same as store request)
     */
    protected function validateContract($validator): void
    {
        if ($this->contract_amount && (!$this->contract_start_date || !$this->contract_end_date)) {
            $validator->errors()->add('contract_start_date', 
                'Sözleşme tutarı girildiğinde başlangıç ve bitiş tarihleri zorunludur.');
        }

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
    }

    /**
     * Validate business information consistency (same as store request)
     */
    protected function validateBusinessInformation($validator): void
    {
        if ($this->industry === 'healthcare' || $this->industry === 'pharmaceutical') {
            if ($this->sponsor_level === 'platinum' && empty($this->tax_number)) {
                $validator->errors()->add('tax_number', 
                    'Sağlık sektöründeki platin sponsorlar için vergi numarası zorunludur.');
            }
        }

        if ($this->has('social_media') && is_array($this->social_media)) {
            $socialMedia = $this->social_media;
            $urls = array_filter($socialMedia);
            if (count($urls) !== count(array_unique($urls))) {
                $validator->errors()->add('social_media', 
                    'Aynı URL birden fazla sosyal medya platformu için kullanılamaz.');
            }
        }
    }

    /**
     * Validate significant data changes
     */
    protected function validateDataChanges($validator): void
    {
        $sponsor = $this->route('sponsor');
        
        // Track significant changes for audit
        $significantChanges = [];
        
        if ($this->name !== $sponsor->name) {
            $significantChanges[] = "Ad değişti: '{$sponsor->name}' → '{$this->name}'";
        }
        
        if ($this->sponsor_level !== $sponsor->sponsor_level) {
            $significantChanges[] = "Seviye değişti: {$sponsor->sponsor_level} → {$this->sponsor_level}";
        }
        
        if ($this->contact_email !== $sponsor->contact_email) {
            $significantChanges[] = "E-posta değişti: {$sponsor->contact_email} → {$this->contact_email}";
        }
        
        if ($this->payment_status !== $sponsor->payment_status) {
            $significantChanges[] = "Ödeme durumu değişti: {$sponsor->payment_status} → {$this->payment_status}";
        }

        if (!empty($significantChanges)) {
            // Store for logging after successful validation
            session()->flash('sponsor_changes', $significantChanges);
        }
    }

    /**
     * Handle any post-validation logic
     */
    protected function passedValidation(): void
    {
        $sponsor = $this->route('sponsor');
        $changes = session()->get('sponsor_changes', []);
        
        if (!empty($changes)) {
            \Log::info('Sponsor Updated', [
                'sponsor_id' => $sponsor->id,
                'organization_id' => $sponsor->organization_id,
                'user_id' => $this->user()->id,
                'changes' => $changes,
            ]);
        }
    }
}
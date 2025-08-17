<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Participant;
use App\Models\Organization;

class UpdateParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $participant = $this->route('participant');
        
        if (!$participant) {
            return false;
        }

        $user = $this->user();
        
        // Admin can update any participant
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the participant
        return $user->organizations()
                   ->where('organizations.id', $participant->organization_id)
                   ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $participant = $this->route('participant');
        
        return [
            'organization_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) use ($participant) {
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
                            $fail('Bu organizasyona katılımcı ekleme yetkiniz yok.');
                        }
                    }
                    
                    // If changing organization, check for existing commitments
                    if ($value != $participant->organization_id) {
                        $hasCommitments = $participant->hasParticipations();
                        if ($hasCommitments) {
                            $fail('Katılımcının mevcut oturum veya sunumları bulunduğu için organizasyon değiştirilemez.');
                        }
                    }
                }
            ],
            
            'first_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-ZÇĞİÖŞÜçğıöşü\s\-\'\.]+$/',
            ],
            
            'last_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-ZÇĞİÖŞÜçğıöşü\s\-\'\.]+$/',
            ],
            
            'title' => [
                'nullable',
                'string',
                'max:100',
                'in:Prof. Dr.,Doç. Dr.,Dr.,Uzm. Dr.,Op. Dr.,Dt.,Ebe,Hemşire,Fizyoterapist,Diyetisyen,Psikolog,Odyolog,Mr.,Ms.,Mrs.',
            ],
            
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                function ($attribute, $value, $fail) use ($participant) {
                    if (!$value) return;
                    
                    // Check for duplicate email within the same organization, excluding current participant
                    $exists = Participant::where('organization_id', $this->organization_id)
                        ->where('email', strtolower(trim($value)))
                        ->where('id', '!=', $participant->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('Bu e-posta adresi bu organizasyonda zaten kullanılıyor.');
                    }
                    
                    // Check for blacklisted domains
                    $blacklistedDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com'];
                    $domain = substr(strrchr($value, "@"), 1);
                    if (in_array(strtolower($domain), $blacklistedDomains)) {
                        $fail('Geçici e-posta adresleri kabul edilmez.');
                    }
                }
            ],
            
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    
                    // Phone number validation logic (same as store request)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $value);
                    
                    if (str_starts_with($cleanPhone, '90')) {
                        $cleanPhone = substr($cleanPhone, 2);
                    }
                    
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = substr($cleanPhone, 1);
                    }
                    
                    if (strlen($cleanPhone) === 10 && str_starts_with($cleanPhone, '5')) {
                        return; // Valid Turkish mobile
                    }
                    
                    if (strlen($cleanPhone) === 10 && !str_starts_with($cleanPhone, '5')) {
                        return; // Valid Turkish landline
                    }
                    
                    if (strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15) {
                        return; // Valid international
                    }
                    
                    $fail('Geçerli bir telefon numarası giriniz.');
                }
            ],
            
            'affiliation' => [
                'nullable',
                'string',
                'max:255',
                'min:2',
            ],
            
            'bio' => [
                'nullable',
                'string',
                'max:2000',
                'min:10',
            ],
            
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048',
                'dimensions:min_width=200,min_height=200,max_width=2000,max_height=2000',
            ],
            
            'remove_photo' => [
                'boolean',
            ],
            
            'is_speaker' => [
                'boolean',
                function ($attribute, $value, $fail) use ($participant) {
                    // If removing speaker role, check for existing presentations
                    if (!$value && $participant->is_speaker && $participant->presentations()->exists()) {
                        $fail('Katılımcının mevcut sunumları bulunduğu için konuşmacı rolü kaldırılamaz.');
                    }
                }
            ],
            
            'is_moderator' => [
                'boolean',
                function ($attribute, $value, $fail) use ($participant) {
                    // If removing moderator role, check for existing moderated sessions
                    if (!$value && $participant->is_moderator && $participant->moderatedSessions()->exists()) {
                        $fail('Katılımcının mevcut moderatör görevleri bulunduğu için moderatör rolü kaldırılamaz.');
                    }
                }
            ],
            
            'website_url' => [
                'nullable',
                'url',
                'max:500',
                'active_url',
            ],
            
            'linkedin_url' => [
                'nullable',
                'url',
                'max:500',
                'regex:/^https?:\/\/(www\.)?linkedin\.com\/in\/[a-zA-Z0-9\-]+\/?$/',
            ],
            
            'twitter_handle' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^@?[a-zA-Z0-9_]+$/',
                Rule::unique('participants')->ignore($participant->id),
            ],
            
            'orcid_id' => [
                'nullable',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{3}[0-9X]$/',
                Rule::unique('participants')->ignore($participant->id),
            ],
            
            'institution_type' => [
                'nullable',
                'in:university,hospital,clinic,research_center,government,private_sector,ngo,other',
            ],
            
            'department' => [
                'nullable',
                'string',
                'max:255',
            ],
            
            'position' => [
                'nullable',
                'string',
                'max:255',
            ],
            
            'specialties' => [
                'nullable',
                'array',
                'max:10',
            ],
            
            'specialties.*' => [
                'string',
                'max:100',
                'distinct',
            ],
            
            'languages' => [
                'nullable',
                'array',
                'max:10',
            ],
            
            'languages.*' => [
                'string',
                'in:tr,en,fr,de,es,it,ar,ru,zh,ja',
                'distinct',
            ],
            
            'consent_data_processing' => [
                'required',
                'accepted',
            ],
            
            'consent_communication' => [
                'boolean',
            ],
            
            'consent_photo_usage' => [
                'boolean',
            ],
            
            'emergency_contact_name' => [
                'nullable',
                'string',
                'max:255',
                'required_with:emergency_contact_phone',
            ],
            
            'emergency_contact_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'required_with:emergency_contact_name',
            ],
            
            'dietary_restrictions' => [
                'nullable',
                'array',
            ],
            
            'dietary_restrictions.*' => [
                'string',
                'in:vegetarian,vegan,halal,kosher,gluten_free,lactose_free,diabetic,other',
            ],
            
            'accessibility_needs' => [
                'nullable',
                'string',
                'max:500',
            ],
            
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            
            'is_active' => [
                'boolean',
                function ($attribute, $value, $fail) use ($participant) {
                    // If deactivating, check for future commitments
                    if (!$value && $participant->is_active) {
                        $futureCommitments = $participant->moderatedSessions()
                            ->whereHas('venue.eventDay.event', function ($query) {
                                $query->where('end_date', '>=', now()->toDateString());
                            })
                            ->exists();
                        
                        $futurePresentations = $participant->presentations()
                            ->whereHas('programSession.venue.eventDay.event', function ($query) {
                                $query->where('end_date', '>=', now()->toDateString());
                            })
                            ->exists();

                        if ($futureCommitments || $futurePresentations) {
                            $fail('Katılımcının gelecekteki etkinliklerde görevleri bulunduğu için pasif hale getirilemez.');
                        }
                    }
                }
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
                'max:20',
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
            
            'first_name.required' => 'Ad zorunludur.',
            'first_name.string' => 'Ad metin formatında olmalıdır.',
            'first_name.max' => 'Ad en fazla 255 karakter olabilir.',
            'first_name.min' => 'Ad en az 2 karakter olmalıdır.',
            'first_name.regex' => 'Ad sadece harf, boşluk, tire ve nokta içerebilir.',
            
            'last_name.required' => 'Soyad zorunludur.',
            'last_name.string' => 'Soyad metin formatında olmalıdır.',
            'last_name.max' => 'Soyad en fazla 255 karakter olabilir.',
            'last_name.min' => 'Soyad en az 2 karakter olmalıdır.',
            'last_name.regex' => 'Soyad sadece harf, boşluk, tire ve nokta içerebilir.',
            
            'title.string' => 'Ünvan metin formatında olmalıdır.',
            'title.max' => 'Ünvan en fazla 100 karakter olabilir.',
            'title.in' => 'Geçersiz ünvan seçildi.',
            
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.max' => 'E-posta adresi en fazla 255 karakter olabilir.',
            
            'phone.string' => 'Telefon numarası metin formatında olmalıdır.',
            'phone.max' => 'Telefon numarası en fazla 20 karakter olabilir.',
            'phone.regex' => 'Geçerli bir telefon numarası formatı giriniz.',
            
            'affiliation.string' => 'Kurum metin formatında olmalıdır.',
            'affiliation.max' => 'Kurum adı en fazla 255 karakter olabilir.',
            'affiliation.min' => 'Kurum adı en az 2 karakter olmalıdır.',
            
            'bio.string' => 'Biyografi metin formatında olmalıdır.',
            'bio.max' => 'Biyografi en fazla 2000 karakter olabilir.',
            'bio.min' => 'Biyografi en az 10 karakter olmalıdır.',
            
            'photo.image' => 'Fotoğraf geçerli bir resim dosyası olmalıdır.',
            'photo.mimes' => 'Fotoğraf JPEG, JPG veya PNG formatında olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 2MB olabilir.',
            'photo.dimensions' => 'Fotoğraf en az 200x200, en fazla 2000x2000 piksel olmalıdır.',
            
            'remove_photo.boolean' => 'Fotoğraf kaldırma seçimi doğru/yanlış değer olmalıdır.',
            
            'is_speaker.boolean' => 'Konuşmacı seçimi doğru/yanlış değer olmalıdır.',
            'is_moderator.boolean' => 'Moderatör seçimi doğru/yanlış değer olmalıdır.',
            
            'website_url.url' => 'Geçerli bir website adresi giriniz.',
            'website_url.max' => 'Website adresi en fazla 500 karakter olabilir.',
            'website_url.active_url' => 'Website adresi erişilebilir olmalıdır.',
            
            'linkedin_url.url' => 'Geçerli bir LinkedIn adresi giriniz.',
            'linkedin_url.regex' => 'LinkedIn profil adres formatı hatalı.',
            
            'twitter_handle.string' => 'Twitter kullanıcı adı metin formatında olmalıdır.',
            'twitter_handle.max' => 'Twitter kullanıcı adı en fazla 50 karakter olabilir.',
            'twitter_handle.regex' => 'Geçerli bir Twitter kullanıcı adı giriniz.',
            'twitter_handle.unique' => 'Bu Twitter kullanıcı adı zaten kullanılıyor.',
            
            'orcid_id.regex' => 'ORCID ID formatı hatalı (örn: 0000-0000-0000-0000).',
            'orcid_id.unique' => 'Bu ORCID ID zaten kullanılıyor.',
            
            'institution_type.in' => 'Geçersiz kurum türü seçildi.',
            
            'department.string' => 'Bölüm metin formatında olmalıdır.',
            'department.max' => 'Bölüm adı en fazla 255 karakter olabilir.',
            
            'position.string' => 'Pozisyon metin formatında olmalıdır.',
            'position.max' => 'Pozisyon en fazla 255 karakter olabilir.',
            
            'specialties.array' => 'Uzmanlık alanları dizi formatında olmalıdır.',
            'specialties.max' => 'En fazla 10 uzmanlık alanı seçilebilir.',
            'specialties.*.string' => 'Uzmanlık alanı metin formatında olmalıdır.',
            'specialties.*.max' => 'Uzmanlık alanı en fazla 100 karakter olabilir.',
            'specialties.*.distinct' => 'Uzmanlık alanları tekrar edemez.',
            
            'languages.array' => 'Diller dizi formatında olmalıdır.',
            'languages.max' => 'En fazla 10 dil seçilebilir.',
            'languages.*.in' => 'Geçersiz dil seçildi.',
            'languages.*.distinct' => 'Diller tekrar edemez.',
            
            'consent_data_processing.required' => 'Veri işleme onayı zorunludur.',
            'consent_data_processing.accepted' => 'Veri işleme onayı kabul edilmelidir.',
            
            'consent_communication.boolean' => 'İletişim onayı doğru/yanlış değer olmalıdır.',
            'consent_photo_usage.boolean' => 'Fotoğraf kullanım onayı doğru/yanlış değer olmalıdır.',
            
            'emergency_contact_name.string' => 'Acil durum iletişim kişisi metin formatında olmalıdır.',
            'emergency_contact_name.required_with' => 'Acil durum telefonu girildiğinde isim zorunludur.',
            
            'emergency_contact_phone.regex' => 'Geçerli bir acil durum telefonu giriniz.',
            'emergency_contact_phone.required_with' => 'Acil durum kişisi girildiğinde telefon zorunludur.',
            
            'dietary_restrictions.array' => 'Diyet kısıtlamaları dizi formatında olmalıdır.',
            'dietary_restrictions.*.in' => 'Geçersiz diyet kısıtlaması seçildi.',
            
            'accessibility_needs.string' => 'Erişilebilirlik ihtiyaçları metin formatında olmalıdır.',
            'accessibility_needs.max' => 'Erişilebilirlik ihtiyaçları en fazla 500 karakter olabilir.',
            
            'notes.string' => 'Notlar metin formatında olmalıdır.',
            'notes.max' => 'Notlar en fazla 1000 karakter olabilir.',
            
            'is_active.boolean' => 'Durum seçimi doğru/yanlış değer olmalıdır.',
            
            'internal_notes.string' => 'İç notlar metin formatında olmalıdır.',
            'internal_notes.max' => 'İç notlar en fazla 2000 karakter olabilir.',
            
            'rating.integer' => 'Değerlendirme sayı formatında olmalıdır.',
            'rating.min' => 'Değerlendirme en az 1 olmalıdır.',
            'rating.max' => 'Değerlendirme en fazla 5 olabilir.',
            
            'tags.array' => 'Etiketler dizi formatında olmalıdır.',
            'tags.max' => 'En fazla 20 etiket eklenebilir.',
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
            'first_name' => 'ad',
            'last_name' => 'soyad',
            'title' => 'ünvan',
            'email' => 'e-posta',
            'phone' => 'telefon',
            'affiliation' => 'kurum',
            'bio' => 'biyografi',
            'photo' => 'fotoğraf',
            'remove_photo' => 'fotoğraf kaldır',
            'is_speaker' => 'konuşmacı',
            'is_moderator' => 'moderatör',
            'website_url' => 'website',
            'linkedin_url' => 'LinkedIn',
            'twitter_handle' => 'Twitter',
            'orcid_id' => 'ORCID ID',
            'institution_type' => 'kurum türü',
            'department' => 'bölüm',
            'position' => 'pozisyon',
            'specialties' => 'uzmanlık alanları',
            'languages' => 'diller',
            'consent_data_processing' => 'veri işleme onayı',
            'consent_communication' => 'iletişim onayı',
            'consent_photo_usage' => 'fotoğraf kullanım onayı',
            'emergency_contact_name' => 'acil durum kişisi',
            'emergency_contact_phone' => 'acil durum telefonu',
            'dietary_restrictions' => 'diyet kısıtlamaları',
            'accessibility_needs' => 'erişilebilirlik ihtiyaçları',
            'notes' => 'notlar',
            'is_active' => 'durum',
            'internal_notes' => 'iç notlar',
            'rating' => 'değerlendirme',
            'tags' => 'etiketler',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the data before validation (same as store request)
        $this->merge([
            'first_name' => $this->first_name ? ucfirst(trim($this->first_name)) : null,
            'last_name' => $this->last_name ? ucfirst(trim($this->last_name)) : null,
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'affiliation' => $this->affiliation ? trim($this->affiliation) : null,
            'bio' => $this->bio ? trim($this->bio) : null,
            'is_speaker' => $this->boolean('is_speaker'),
            'is_moderator' => $this->boolean('is_moderator'),
            'remove_photo' => $this->boolean('remove_photo'),
            'website_url' => $this->website_url ? trim($this->website_url) : null,
            'linkedin_url' => $this->linkedin_url ? trim($this->linkedin_url) : null,
            'twitter_handle' => $this->twitter_handle ? str_replace('@', '', trim($this->twitter_handle)) : null,
            'department' => $this->department ? trim($this->department) : null,
            'position' => $this->position ? trim($this->position) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
            'consent_data_processing' => $this->boolean('consent_data_processing'),
            'consent_communication' => $this->boolean('consent_communication'),
            'consent_photo_usage' => $this->boolean('consent_photo_usage'),
            'emergency_contact_name' => $this->emergency_contact_name ? trim($this->emergency_contact_name) : null,
            'accessibility_needs' => $this->accessibility_needs ? trim($this->accessibility_needs) : null,
            'is_active' => $this->boolean('is_active'),
            'internal_notes' => $this->internal_notes ? trim($this->internal_notes) : null,
            'rating' => $this->rating ? (int) $this->rating : null,
        ]);

        // Format phone numbers (same logic as store request)
        if ($this->phone) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone);
            if (strlen($phone) === 10 && str_starts_with($phone, '5')) {
                $phone = '+90' . $phone;
            } elseif (strlen($phone) === 11 && str_starts_with($phone, '05')) {
                $phone = '+90' . substr($phone, 1);
            }
            $this->merge(['phone' => $phone]);
        }

        if ($this->emergency_contact_phone) {
            $emergencyPhone = preg_replace('/[^0-9+]/', '', $this->emergency_contact_phone);
            if (strlen($emergencyPhone) === 10 && str_starts_with($emergencyPhone, '5')) {
                $emergencyPhone = '+90' . $emergencyPhone;
            } elseif (strlen($emergencyPhone) === 11 && str_starts_with($emergencyPhone, '05')) {
                $emergencyPhone = '+90' . substr($emergencyPhone, 1);
            }
            $this->merge(['emergency_contact_phone' => $emergencyPhone]);
        }

        // Clean arrays
        if ($this->has('specialties') && is_array($this->specialties)) {
            $specialties = array_filter(array_map('trim', $this->specialties));
            $this->merge(['specialties' => array_values($specialties)]);
        }

        if ($this->has('languages') && is_array($this->languages)) {
            $languages = array_filter($this->languages);
            $this->merge(['languages' => array_values($languages)]);
        }

        if ($this->has('dietary_restrictions') && is_array($this->dietary_restrictions)) {
            $dietaryRestrictions = array_filter($this->dietary_restrictions);
            $this->merge(['dietary_restrictions' => array_values($dietaryRestrictions)]);
        }

        if ($this->has('tags') && is_array($this->tags)) {
            $tags = array_filter(array_map('trim', $this->tags));
            $this->merge(['tags' => array_values($tags)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateRoleConsistency($validator);
            $this->validateContactInformation($validator);
            $this->validateProfessionalInformation($validator);
            $this->validateDataChanges($validator);
        });
    }

    /**
     * Validate role consistency (same as store request)
     */
    protected function validateRoleConsistency($validator): void
    {
        $isSpeaker = $this->boolean('is_speaker');
        $isModerator = $this->boolean('is_moderator');
        
        if (!$isSpeaker && !$isModerator) {
            $validator->errors()->add('is_speaker', 'En az bir rol seçilmelidir (Konuşmacı veya Moderatör).');
        }

        if ($isSpeaker && empty($this->bio)) {
            $validator->errors()->add('bio', 'Konuşmacılar için biyografi zorunludur.');
        }

        if ($isSpeaker && empty($this->affiliation)) {
            $validator->errors()->add('affiliation', 'Konuşmacılar için kurum bilgisi zorunludur.');
        }
    }

    /**
     * Validate contact information consistency (same as store request)
     */
    protected function validateContactInformation($validator): void
    {
        if (empty($this->email) && empty($this->phone)) {
            $validator->errors()->add('email', 'E-posta veya telefon bilgilerinden en az biri zorunludur.');
        }

        if ($this->twitter_handle && strlen($this->twitter_handle) < 3) {
            $validator->errors()->add('twitter_handle', 'Twitter kullanıcı adı en az 3 karakter olmalıdır.');
        }
    }

    /**
     * Validate professional information (same as store request)
     */
    protected function validateProfessionalInformation($validator): void
    {
        if ($this->institution_type === 'university' && empty($this->department)) {
            $validator->errors()->add('department', 'Üniversite seçildiğinde bölüm bilgisi zorunludur.');
        }

        $medicalTitles = ['Dr.', 'Uzm. Dr.', 'Op. Dr.'];
        if ($this->title && in_array($this->title, $medicalTitles) && empty($this->specialties)) {
            $validator->errors()->add('specialties', 'Tıp doktorları için uzmanlık alanı seçimi önerilir.');
        }
    }

    /**
     * Validate significant data changes
     */
    protected function validateDataChanges($validator): void
    {
        $participant = $this->route('participant');
        
        // Track significant changes for audit
        $significantChanges = [];
        
        if ($this->email !== $participant->email) {
            $significantChanges[] = "E-posta değişti: {$participant->email} → {$this->email}";
        }
        
        if ($this->phone !== $participant->phone) {
            $significantChanges[] = "Telefon değişti: {$participant->phone} → {$this->phone}";
        }
        
        if ($this->boolean('is_speaker') !== $participant->is_speaker) {
            $role = $this->boolean('is_speaker') ? 'eklendi' : 'kaldırıldı';
            $significantChanges[] = "Konuşmacı rolü {$role}";
        }
        
        if ($this->boolean('is_moderator') !== $participant->is_moderator) {
            $role = $this->boolean('is_moderator') ? 'eklendi' : 'kaldırıldı';
            $significantChanges[] = "Moderatör rolü {$role}";
        }

        if (!empty($significantChanges)) {
            // Store for logging after successful validation
            session()->flash('participant_changes', $significantChanges);
        }
    }

    /**
     * Handle any post-validation logic
     */
    protected function passedValidation(): void
    {
        $participant = $this->route('participant');
        $changes = session()->get('participant_changes', []);
        
        if (!empty($changes)) {
            \Log::info('Participant Updated', [
                'participant_id' => $participant->id,
                'organization_id' => $participant->organization_id,
                'user_id' => $this->user()->id,
                'changes' => $changes,
            ]);
        }
    }
}
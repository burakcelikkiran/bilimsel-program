<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Organization;

class ImportParticipantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Admin can import participants for any organization
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization they're trying to import to
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
            'file' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    // Check file content
                    if ($value->getSize() === 0) {
                        $fail('Yüklenen dosya boş.');
                        return;
                    }
                    
                    // Basic file header validation for CSV
                    if ($value->getClientOriginalExtension() === 'csv') {
                        $handle = fopen($value->getRealPath(), 'r');
                        if ($handle) {
                            $firstLine = fgetcsv($handle);
                            fclose($handle);
                            
                            if (empty($firstLine) || count($firstLine) < 3) {
                                $fail('CSV dosyası en az 3 kolon içermelidir (Ad, Soyad, E-posta).');
                            }
                        }
                    }
                }
            ],
            
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
                            $fail('Bu organizasyona katılımcı içe aktarma yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'update_existing' => [
                'boolean',
            ],
            
            'skip_duplicates' => [
                'boolean',
            ],
            
            'default_speaker_role' => [
                'boolean',
            ],
            
            'default_moderator_role' => [
                'boolean',
            ],
            
            'send_welcome_email' => [
                'boolean',
            ],
            
            'create_user_accounts' => [
                'boolean',
            ],
            
            'import_mode' => [
                'required',
                'in:validate_only,import_all,import_new_only',
            ],
            
            'batch_size' => [
                'nullable',
                'integer',
                'min:10',
                'max:1000',
            ],
            
            'field_mapping' => [
                'nullable',
                'array',
            ],
            
            'field_mapping.first_name' => [
                'required_with:field_mapping',
                'string',
                'max:50',
            ],
            
            'field_mapping.last_name' => [
                'required_with:field_mapping',
                'string',
                'max:50',
            ],
            
            'field_mapping.email' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'field_mapping.phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'field_mapping.title' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'field_mapping.affiliation' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'field_mapping.bio' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'field_mapping.specialties' => [
                'nullable',
                'string',
                'max:50',
            ],
            
            'validation_rules' => [
                'nullable',
                'array',
            ],
            
            'validation_rules.require_email' => [
                'boolean',
            ],
            
            'validation_rules.require_phone' => [
                'boolean',
            ],
            
            'validation_rules.require_affiliation' => [
                'boolean',
            ],
            
            'validation_rules.require_bio' => [
                'boolean',
            ],
            
            'validation_rules.validate_email_format' => [
                'boolean',
            ],
            
            'validation_rules.validate_phone_format' => [
                'boolean',
            ],
            
            'validation_rules.min_bio_length' => [
                'nullable',
                'integer',
                'min:0',
                'max:500',
            ],
            
            'data_processing_consent' => [
                'required',
                'accepted',
            ],
            
            'import_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Dosya seçimi zorunludur.',
            'file.file' => 'Geçerli bir dosya seçiniz.',
            'file.mimes' => 'Dosya CSV, XLSX veya XLS formatında olmalıdır.',
            'file.max' => 'Dosya boyutu en fazla 10MB olabilir.',
            
            'organization_id.required' => 'Organizasyon seçimi zorunludur.',
            'organization_id.exists' => 'Seçilen organizasyon bulunamadı.',
            
            'update_existing.boolean' => 'Mevcut kayıtları güncelle seçimi doğru/yanlış değer olmalıdır.',
            'skip_duplicates.boolean' => 'Tekrar eden kayıtları atla seçimi doğru/yanlış değer olmalıdır.',
            'default_speaker_role.boolean' => 'Varsayılan konuşmacı rolü seçimi doğru/yanlış değer olmalıdır.',
            'default_moderator_role.boolean' => 'Varsayılan moderatör rolü seçimi doğru/yanlış değer olmalıdır.',
            'send_welcome_email.boolean' => 'Hoş geldin e-postası gönder seçimi doğru/yanlış değer olmalıdır.',
            'create_user_accounts.boolean' => 'Kullanıcı hesabı oluştur seçimi doğru/yanlış değer olmalıdır.',
            
            'import_mode.required' => 'İçe aktarma modu seçimi zorunludur.',
            'import_mode.in' => 'Geçersiz içe aktarma modu seçildi.',
            
            'batch_size.integer' => 'Parti boyutu sayı formatında olmalıdır.',
            'batch_size.min' => 'Parti boyutu en az 10 olmalıdır.',
            'batch_size.max' => 'Parti boyutu en fazla 1000 olabilir.',
            
            'field_mapping.array' => 'Alan eşleştirme dizi formatında olmalıdır.',
            'field_mapping.first_name.required_with' => 'Ad alanı eşleştirmesi zorunludur.',
            'field_mapping.first_name.string' => 'Ad alanı metin formatında olmalıdır.',
            'field_mapping.first_name.max' => 'Ad alanı en fazla 50 karakter olabilir.',
            'field_mapping.last_name.required_with' => 'Soyad alanı eşleştirmesi zorunludur.',
            'field_mapping.last_name.string' => 'Soyad alanı metin formatında olmalıdır.',
            'field_mapping.last_name.max' => 'Soyad alanı en fazla 50 karakter olabilir.',
            
            'validation_rules.array' => 'Doğrulama kuralları dizi formatında olmalıdır.',
            'validation_rules.require_email.boolean' => 'E-posta zorunlu seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.require_phone.boolean' => 'Telefon zorunlu seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.require_affiliation.boolean' => 'Kurum zorunlu seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.require_bio.boolean' => 'Biyografi zorunlu seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.validate_email_format.boolean' => 'E-posta format kontrolü seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.validate_phone_format.boolean' => 'Telefon format kontrolü seçimi doğru/yanlış değer olmalıdır.',
            'validation_rules.min_bio_length.integer' => 'Minimum biyografi uzunluğu sayı formatında olmalıdır.',
            'validation_rules.min_bio_length.min' => 'Minimum biyografi uzunluğu 0\'dan küçük olamaz.',
            'validation_rules.min_bio_length.max' => 'Minimum biyografi uzunluğu 500\'den büyük olamaz.',
            
            'data_processing_consent.required' => 'Veri işleme onayı zorunludur.',
            'data_processing_consent.accepted' => 'Veri işleme onayı kabul edilmelidir.',
            
            'import_notes.string' => 'İçe aktarma notları metin formatında olmalıdır.',
            'import_notes.max' => 'İçe aktarma notları en fazla 1000 karakter olabilir.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => 'dosya',
            'organization_id' => 'organizasyon',
            'update_existing' => 'mevcut kayıtları güncelle',
            'skip_duplicates' => 'tekrar eden kayıtları atla',
            'default_speaker_role' => 'varsayılan konuşmacı rolü',
            'default_moderator_role' => 'varsayılan moderatör rolü',
            'send_welcome_email' => 'hoş geldin e-postası gönder',
            'create_user_accounts' => 'kullanıcı hesabı oluştur',
            'import_mode' => 'içe aktarma modu',
            'batch_size' => 'parti boyutu',
            'field_mapping' => 'alan eşleştirme',
            'validation_rules' => 'doğrulama kuralları',
            'data_processing_consent' => 'veri işleme onayı',
            'import_notes' => 'içe aktarma notları',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults
        $this->merge([
            'update_existing' => $this->boolean('update_existing', false),
            'skip_duplicates' => $this->boolean('skip_duplicates', true),
            'default_speaker_role' => $this->boolean('default_speaker_role', false),
            'default_moderator_role' => $this->boolean('default_moderator_role', false),
            'send_welcome_email' => $this->boolean('send_welcome_email', false),
            'create_user_accounts' => $this->boolean('create_user_accounts', false),
            'import_mode' => $this->import_mode ?: 'validate_only',
            'batch_size' => $this->batch_size ? (int) $this->batch_size : 100,
            'data_processing_consent' => $this->boolean('data_processing_consent'),
            'import_notes' => $this->import_notes ? trim($this->import_notes) : null,
        ]);

        // Set default validation rules
        if (!$this->has('validation_rules')) {
            $this->merge([
                'validation_rules' => [
                    'require_email' => false,
                    'require_phone' => false,
                    'require_affiliation' => false,
                    'require_bio' => false,
                    'validate_email_format' => true,
                    'validate_phone_format' => true,
                    'min_bio_length' => 10,
                ]
            ]);
        }

        // Clean field mapping
        if ($this->has('field_mapping') && is_array($this->field_mapping)) {
            $fieldMapping = array_filter($this->field_mapping, function ($value) {
                return !empty($value);
            });
            $this->merge(['field_mapping' => $fieldMapping]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateFileContent($validator);
            $this->validateImportSettings($validator);
            $this->validateFieldMapping($validator);
            $this->validateImportLimits($validator);
        });
    }

    /**
     * Validate file content
     */
    protected function validateFileContent($validator): void
    {
        if (!$this->hasFile('file')) {
            return;
        }

        $file = $this->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            if ($extension === 'csv') {
                $this->validateCsvContent($file, $validator);
            } else {
                $this->validateExcelContent($file, $validator);
            }
        } catch (\Exception $e) {
            $validator->errors()->add('file', 'Dosya içeriği okunamıyor: ' . $e->getMessage());
        }
    }

    /**
     * Validate CSV content
     */
    private function validateCsvContent($file, $validator): void
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            $validator->errors()->add('file', 'CSV dosyası açılamıyor.');
            return;
        }

        $rowCount = 0;
        $headers = null;
        
        while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
            if ($rowCount === 0) {
                $headers = $row;
                
                // Check for required columns
                $requiredColumns = ['ad', 'soyad'];
                $lowerHeaders = array_map('strtolower', array_map('trim', $headers));
                
                foreach ($requiredColumns as $required) {
                    if (!in_array($required, $lowerHeaders)) {
                        $validator->errors()->add('file', 
                            "CSV dosyasında '{$required}' kolonu bulunamadı. Gerekli kolonlar: " . 
                            implode(', ', $requiredColumns));
                    }
                }
                
                // Check for empty headers
                if (count(array_filter($headers)) !== count($headers)) {
                    $validator->errors()->add('file', 'CSV dosyasında boş kolon başlıkları var.');
                }
            } else {
                // Check data consistency
                if (count($row) !== count($headers)) {
                    $validator->errors()->add('file', 
                        "Satır " . ($rowCount + 1) . ": Kolon sayısı başlık satırıyla uyuşmuyor.");
                }
            }
            
            $rowCount++;
        }
        
        fclose($handle);
        
        if ($rowCount < 2) {
            $validator->errors()->add('file', 'CSV dosyasında veri satırı bulunamadı.');
        }
    }

    /**
     * Validate Excel content
     */
    private function validateExcelContent($file, $validator): void
    {
        // Basic Excel file validation
        // In real implementation, you would use PhpSpreadsheet or similar
        $fileSize = $file->getSize();
        if ($fileSize > 10 * 1024 * 1024) { // 10MB
            $validator->errors()->add('file', 'Excel dosyası çok büyük.');
        }
    }

    /**
     * Validate import settings
     */
    protected function validateImportSettings($validator): void
    {
        // Conflicting settings validation
        if ($this->boolean('update_existing') && $this->boolean('skip_duplicates')) {
            $validator->errors()->add('update_existing', 
                'Mevcut kayıtları güncelle ve tekrar eden kayıtları atla seçenekleri aynı anda seçilemez.');
        }

        // Role validation
        if (!$this->boolean('default_speaker_role') && !$this->boolean('default_moderator_role')) {
            $validator->errors()->add('default_speaker_role', 
                'En az bir varsayılan rol seçilmelidir (Konuşmacı veya Moderatör).');
        }

        // User account creation validation
        if ($this->boolean('create_user_accounts')) {
            $validationRules = $this->validation_rules ?? [];
            if (!($validationRules['require_email'] ?? false)) {
                $validator->errors()->add('create_user_accounts', 
                    'Kullanıcı hesabı oluşturmak için e-posta adresi zorunlu olmalıdır.');
            }
        }

        // Welcome email validation
        if ($this->boolean('send_welcome_email')) {
            $validationRules = $this->validation_rules ?? [];
            if (!($validationRules['require_email'] ?? false)) {
                $validator->errors()->add('send_welcome_email', 
                    'Hoş geldin e-postası göndermek için e-posta adresi zorunlu olmalıdır.');
            }
        }
    }

    /**
     * Validate field mapping
     */
    protected function validateFieldMapping($validator): void
    {
        if (!$this->has('field_mapping') || empty($this->field_mapping)) {
            return;
        }

        $mapping = $this->field_mapping;
        
        // Check for duplicate mappings
        $mappedFields = array_filter($mapping);
        if (count($mappedFields) !== count(array_unique($mappedFields))) {
            $validator->errors()->add('field_mapping', 
                'Aynı kolon birden fazla alana eşleştirilemez.');
        }

        // Validate required field mappings
        if (empty($mapping['first_name'])) {
            $validator->errors()->add('field_mapping.first_name', 'Ad alanı eşleştirmesi zorunludur.');
        }

        if (empty($mapping['last_name'])) {
            $validator->errors()->add('field_mapping.last_name', 'Soyad alanı eşleştirmesi zorunludur.');
        }

        // Check validation rules consistency
        $validationRules = $this->validation_rules ?? [];
        
        if (($validationRules['require_email'] ?? false) && empty($mapping['email'])) {
            $validator->errors()->add('field_mapping.email', 
                'E-posta zorunlu olarak ayarlandığında e-posta alanı eşleştirmesi gereklidir.');
        }

        if (($validationRules['require_phone'] ?? false) && empty($mapping['phone'])) {
            $validator->errors()->add('field_mapping.phone', 
                'Telefon zorunlu olarak ayarlandığında telefon alanı eşleştirmesi gereklidir.');
        }

        if (($validationRules['require_affiliation'] ?? false) && empty($mapping['affiliation'])) {
            $validator->errors()->add('field_mapping.affiliation', 
                'Kurum zorunlu olarak ayarlandığında kurum alanı eşleştirmesi gereklidir.');
        }

        if (($validationRules['require_bio'] ?? false) && empty($mapping['bio'])) {
            $validator->errors()->add('field_mapping.bio', 
                'Biyografi zorunlu olarak ayarlandığında biyografi alanı eşleştirmesi gereklidir.');
        }
    }

    /**
     * Validate import limits
     */
    protected function validateImportLimits($validator): void
    {
        if (!$this->organization_id) {
            return;
        }

        // Check organization participant limits
        $currentParticipantCount = \App\Models\Participant::where('organization_id', $this->organization_id)->count();
        $organizationLimits = [
            'max_participants' => 10000, // Configurable per organization
            'max_import_batch' => 1000,
        ];

        if ($currentParticipantCount >= $organizationLimits['max_participants']) {
            $validator->errors()->add('organization_id', 
                'Bu organizasyon maksimum katılımcı sayısına ulaşmış (' . 
                $organizationLimits['max_participants'] . ').');
        }

        if ($this->batch_size && $this->batch_size > $organizationLimits['max_import_batch']) {
            $validator->errors()->add('batch_size', 
                'Maksimum içe aktarma parti boyutu: ' . $organizationLimits['max_import_batch']);
        }

        // Check daily import limits
        $dailyImportCount = \App\Models\Participant::where('organization_id', $this->organization_id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($dailyImportCount >= 500) { // Daily limit
            $validator->errors()->add('organization_id', 
                'Günlük içe aktarma limitine ulaşıldı (500 katılımcı). Yarın tekrar deneyiniz.');
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Add import metadata
        $validated['import_metadata'] = [
            'imported_by' => $this->user()->id,
            'imported_at' => now(),
            'file_name' => $this->file('file')->getClientOriginalName(),
            'file_size' => $this->file('file')->getSize(),
            'file_type' => $this->file('file')->getClientOriginalExtension(),
            'organization_id' => $this->organization_id,
            'import_settings' => [
                'update_existing' => $this->boolean('update_existing'),
                'skip_duplicates' => $this->boolean('skip_duplicates'),
                'default_speaker_role' => $this->boolean('default_speaker_role'),
                'default_moderator_role' => $this->boolean('default_moderator_role'),
                'send_welcome_email' => $this->boolean('send_welcome_email'),
                'create_user_accounts' => $this->boolean('create_user_accounts'),
            ],
        ];

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
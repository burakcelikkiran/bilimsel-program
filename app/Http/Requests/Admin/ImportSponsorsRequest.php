<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Organization;

class ImportSponsorsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if ($user->isAdmin()) {
            return true;
        }

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
                'max:5120',
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
                    
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $value)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu organizasyona sponsor içe aktarma yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'update_existing' => ['boolean'],
            'skip_duplicates' => ['boolean'],
            'import_mode' => ['required', 'in:validate_only,import_all,import_new_only'],
            'batch_size' => ['nullable', 'integer', 'min:10', 'max:100'],
            'default_sponsor_level' => ['required', 'in:platinum,gold,silver,bronze'],
            'auto_activate_sponsors' => ['boolean'],
            
            'field_mapping' => ['nullable', 'array'],
            'field_mapping.name' => ['required_with:field_mapping', 'string', 'max:50'],
            'field_mapping.sponsor_level' => ['nullable', 'string', 'max:50'],
            'field_mapping.website_url' => ['nullable', 'string', 'max:50'],
            'field_mapping.contact_email' => ['nullable', 'string', 'max:50'],
            'field_mapping.contact_phone' => ['nullable', 'string', 'max:50'],
            'field_mapping.contact_person' => ['nullable', 'string', 'max:50'],
            
            'validation_rules' => ['nullable', 'array'],
            'validation_rules.require_contact_email' => ['boolean'],
            'validation_rules.require_website' => ['boolean'],
            'validation_rules.validate_email_format' => ['boolean'],
            'validation_rules.validate_url_format' => ['boolean'],
            
            'data_processing_consent' => ['required', 'accepted'],
            'import_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Dosya seçimi zorunludur.',
            'file.mimes' => 'Dosya CSV, XLSX veya XLS formatında olmalıdır.',
            'file.max' => 'Dosya boyutu en fazla 5MB olabilir.',
            'organization_id.required' => 'Organizasyon seçimi zorunludur.',
            'import_mode.required' => 'İçe aktarma modu seçimi zorunludur.',
            'default_sponsor_level.required' => 'Varsayılan sponsor seviyesi seçimi zorunludur.',
            'field_mapping.name.required_with' => 'Sponsor adı alanı eşleştirmesi zorunludur.',
            'data_processing_consent.required' => 'Veri işleme onayı zorunludur.',
            'data_processing_consent.accepted' => 'Veri işleme onayı kabul edilmelidir.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'update_existing' => $this->boolean('update_existing', false),
            'skip_duplicates' => $this->boolean('skip_duplicates', true),
            'import_mode' => $this->import_mode ?: 'validate_only',
            'batch_size' => $this->batch_size ? (int) $this->batch_size : 25,
            'default_sponsor_level' => $this->default_sponsor_level ?: 'bronze',
            'auto_activate_sponsors' => $this->boolean('auto_activate_sponsors', true),
            'data_processing_consent' => $this->boolean('data_processing_consent'),
            'import_notes' => $this->import_notes ? trim($this->import_notes) : null,
        ]);

        if (!$this->has('validation_rules')) {
            $this->merge([
                'validation_rules' => [
                    'require_contact_email' => false,
                    'require_website' => false,
                    'validate_email_format' => true,
                    'validate_url_format' => true,
                ]
            ]);
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        $validated['import_metadata'] = [
            'imported_by' => $this->user()->id,
            'imported_at' => now(),
            'file_name' => $this->file('file')->getClientOriginalName(),
            'file_size' => $this->file('file')->getSize(),
            'file_type' => $this->file('file')->getClientOriginalExtension(),
            'organization_id' => $this->organization_id,
        ];

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
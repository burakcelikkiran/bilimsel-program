<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProgramSession;

class ImportPresentationsRequest extends FormRequest
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

        if ($this->program_session_id) {
            $session = ProgramSession::with('venue.eventDay.event')->find($this->program_session_id);
            if ($session) {
                return $user->organizations()
                           ->where('organizations.id', $session->venue->eventDay->event->organization_id)
                           ->exists();
            }
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
            
            'program_session_id' => [
                'required',
                'exists:program_sessions,id',
                function ($attribute, $value, $fail) {
                    $session = ProgramSession::with('venue.eventDay.event')->find($value);
                    if (!$session) {
                        $fail('Seçilen oturum bulunamadı.');
                        return;
                    }
                    
                    if ($session->is_break) {
                        $fail('Ara oturumlarına sunum içe aktarılamaz.');
                        return;
                    }
                    
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $session->venue->eventDay->event->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu oturuma sunum içe aktarma yetkiniz yok.');
                        }
                    }
                }
            ],
            
            'update_existing' => ['boolean'],
            'skip_duplicates' => ['boolean'],
            'import_mode' => ['required', 'in:validate_only,import_all,import_new_only'],
            'batch_size' => ['nullable', 'integer', 'min:5', 'max:100'],
            'default_presentation_type' => ['required', 'in:keynote,oral,case_presentation,symposium,poster,workshop,panel'],
            'default_language' => ['nullable', 'string', 'in:tr,en,fr,de,es,it,ar'],
            'auto_create_speakers' => ['boolean'],
            
            'field_mapping' => ['nullable', 'array'],
            'field_mapping.title' => ['required_with:field_mapping', 'string', 'max:50'],
            'field_mapping.abstract' => ['nullable', 'string', 'max:50'],
            'field_mapping.duration_minutes' => ['nullable', 'string', 'max:50'],
            'field_mapping.presentation_type' => ['nullable', 'string', 'max:50'],
            'field_mapping.speakers' => ['nullable', 'string', 'max:50'],
            'field_mapping.language' => ['nullable', 'string', 'max:50'],
            
            'validation_rules' => ['nullable', 'array'],
            'validation_rules.require_abstract' => ['boolean'],
            'validation_rules.require_speakers' => ['boolean'],
            'validation_rules.min_abstract_length' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'validation_rules.max_duration' => ['nullable', 'integer', 'min:5', 'max:480'],
            
            'speaker_settings' => ['nullable', 'array'],
            'speaker_settings.default_speaker_role' => ['nullable', 'in:primary,co_speaker,discussant'],
            'speaker_settings.split_multiple_speakers' => ['boolean'],
            'speaker_settings.speaker_separator' => ['nullable', 'string', 'max:10'],
            
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
            'program_session_id.required' => 'Oturum seçimi zorunludur.',
            'program_session_id.exists' => 'Seçilen oturum bulunamadı.',
            'import_mode.required' => 'İçe aktarma modu seçimi zorunludur.',
            'default_presentation_type.required' => 'Varsayılan sunum türü seçimi zorunludur.',
            'field_mapping.title.required_with' => 'Sunum başlığı alanı eşleştirmesi zorunludur.',
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
            'batch_size' => $this->batch_size ? (int) $this->batch_size : 20,
            'default_presentation_type' => $this->default_presentation_type ?: 'oral',
            'default_language' => $this->default_language ?: 'tr',
            'auto_create_speakers' => $this->boolean('auto_create_speakers', true),
            'data_processing_consent' => $this->boolean('data_processing_consent'),
            'import_notes' => $this->import_notes ? trim($this->import_notes) : null,
        ]);

        if (!$this->has('validation_rules')) {
            $this->merge([
                'validation_rules' => [
                    'require_abstract' => false,
                    'require_speakers' => true,
                    'min_abstract_length' => 50,
                    'max_duration' => 60,
                ]
            ]);
        }

        if (!$this->has('speaker_settings')) {
            $this->merge([
                'speaker_settings' => [
                    'default_speaker_role' => 'primary',
                    'split_multiple_speakers' => true,
                    'speaker_separator' => ',',
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
            'program_session_id' => $this->program_session_id,
        ];

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
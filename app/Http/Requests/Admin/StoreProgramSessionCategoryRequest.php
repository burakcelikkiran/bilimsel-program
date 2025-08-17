<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Event;

class StoreProgramSessionCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Admin can create categories for any event
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the event
        if ($this->event_id) {
            $event = Event::find($this->event_id);
            if ($event) {
                return $user->organizations()
                           ->where('organizations.id', $event->organization_id)
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
            'event_id' => [
                'required',
                'exists:events,id',
                function ($attribute, $value, $fail) {
                    $event = Event::find($value);
                    if (!$event) {
                        $fail('Seçilen etkinlik bulunamadı.');
                        return;
                    }
                    
                    // Check if user has access to this event's organization
                    $user = $this->user();
                    if (!$user->isAdmin()) {
                        $hasAccess = $user->organizations()
                                         ->where('organizations.id', $event->organization_id)
                                         ->exists();
                        if (!$hasAccess) {
                            $fail('Bu etkinliğe kategori ekleme yetkiniz yok.');
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
                    if (!$this->event_id) return;
                    
                    // Check for duplicate category name within the same event
                    $exists = \App\Models\ProgramSessionCategory::where('event_id', $this->event_id)
                        ->where('name', trim($value))
                        ->exists();
                    
                    if ($exists) {
                        $fail('Bu etkinlikte aynı isimde bir kategori zaten mevcut.');
                    }
                }
            ],
            
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            
            'color' => [
                'nullable',
                'string',
                'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            ],
            
            'icon' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\-_]+$/', // Only alphanumeric, dash, underscore for icon names
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
            
            'background_color' => [
                'nullable',
                'string',
                'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            ],
            
            'text_color' => [
                'nullable',
                'string',
                'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            ],
            
            'border_style' => [
                'nullable',
                'in:solid,dashed,dotted,double',
            ],
            
            'display_order' => [
                'nullable',
                'in:alphabetical,chronological,manual',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'Etkinlik seçimi zorunludur.',
            'event_id.exists' => 'Seçilen etkinlik bulunamadı.',
            
            'name.required' => 'Kategori adı zorunludur.',
            'name.string' => 'Kategori adı metin formatında olmalıdır.',
            'name.max' => 'Kategori adı en fazla 255 karakter olabilir.',
            'name.min' => 'Kategori adı en az 2 karakter olmalıdır.',
            
            'description.string' => 'Açıklama metin formatında olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',
            
            'color.string' => 'Renk kodu metin formatında olmalıdır.',
            'color.regex' => 'Renk kodu geçerli bir hex formatında olmalıdır (örn: #FF0000).',
            
            'icon.string' => 'İkon adı metin formatında olmalıdır.',
            'icon.max' => 'İkon adı en fazla 100 karakter olabilir.',
            'icon.regex' => 'İkon adı sadece harf, rakam, tire ve alt çizgi içerebilir.',
            
            'is_active.boolean' => 'Durum seçimi doğru/yanlış değer olmalıdır.',
            
            'sort_order.integer' => 'Sıralama sayı formatında olmalıdır.',
            'sort_order.min' => 'Sıralama 0\'dan küçük olamaz.',
            'sort_order.max' => 'Sıralama 999\'dan büyük olamaz.',
            
            'background_color.regex' => 'Arka plan rengi geçerli bir hex formatında olmalıdır.',
            'text_color.regex' => 'Metin rengi geçerli bir hex formatında olmalıdır.',
            
            'border_style.in' => 'Geçersiz kenarlık stili seçildi.',
            'display_order.in' => 'Geçersiz görüntüleme sırası seçildi.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'etkinlik',
            'name' => 'kategori adı',
            'description' => 'açıklama',
            'color' => 'renk',
            'icon' => 'ikon',
            'is_active' => 'durum',
            'sort_order' => 'sıralama',
            'background_color' => 'arka plan rengi',
            'text_color' => 'metin rengi',
            'border_style' => 'kenarlık stili',
            'display_order' => 'görüntüleme sırası',
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
            'is_active' => $this->boolean('is_active', true), // Default to active
            'sort_order' => $this->sort_order ? (int) $this->sort_order : null,
            'icon' => $this->icon ? trim(strtolower($this->icon)) : null,
        ]);

        // Format color codes
        if ($this->color) {
            $color = trim($this->color);
            if (!str_starts_with($color, '#')) {
                $color = '#' . $color;
            }
            $this->merge(['color' => strtoupper($color)]);
        }

        if ($this->background_color) {
            $bgColor = trim($this->background_color);
            if (!str_starts_with($bgColor, '#')) {
                $bgColor = '#' . $bgColor;
            }
            $this->merge(['background_color' => strtoupper($bgColor)]);
        }

        if ($this->text_color) {
            $textColor = trim($this->text_color);
            if (!str_starts_with($textColor, '#')) {
                $textColor = '#' . $textColor;
            }
            $this->merge(['text_color' => strtoupper($textColor)]);
        }

        // Set default color if not provided
        if (!$this->color) {
            $defaultColors = [
                '#3B82F6', // Blue
                '#10B981', // Emerald  
                '#F59E0B', // Amber
                '#EF4444', // Red
                '#8B5CF6', // Violet
                '#06B6D4', // Cyan
                '#84CC16', // Lime
                '#F97316', // Orange
                '#EC4899', // Pink
                '#6366F1', // Indigo
            ];
            
            $this->merge(['color' => $defaultColors[array_rand($defaultColors)]]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateColorContrast($validator);
            $this->validateCategoryLimits($validator);
            $this->validateIconFormat($validator);
        });
    }

    /**
     * Validate color contrast for accessibility
     */
    protected function validateColorContrast($validator): void
    {
        if (!$this->background_color || !$this->text_color) {
            return;
        }

        // Basic contrast check - ensure colors are not too similar
        $bgColor = $this->background_color;
        $textColor = $this->text_color;
        
        if (strtoupper($bgColor) === strtoupper($textColor)) {
            $validator->errors()->add('text_color', 'Metin rengi ve arka plan rengi aynı olamaz.');
        }

        // Check for light background with light text or dark background with dark text
        $lightColors = ['#FFFFFF', '#F8F9FA', '#E9ECEF', '#DEE2E6', '#CED4DA'];
        $darkColors = ['#000000', '#212529', '#343A40', '#495057', '#6C757D'];
        
        $isLightBg = in_array(strtoupper($bgColor), $lightColors) || 
                    (hexdec(substr($bgColor, 1, 2)) + hexdec(substr($bgColor, 3, 2)) + hexdec(substr($bgColor, 5, 2))) > 600;
        
        $isLightText = in_array(strtoupper($textColor), $lightColors) ||
                      (hexdec(substr($textColor, 1, 2)) + hexdec(substr($textColor, 3, 2)) + hexdec(substr($textColor, 5, 2))) > 600;

        if (($isLightBg && $isLightText) || (!$isLightBg && !$isLightText && !in_array(strtoupper($textColor), $lightColors))) {
            $validator->errors()->add('text_color', 'Renk kombinasyonu yeterli kontrast sağlamıyor. Okunabilirlik için uygun renk seçiniz.');
        }
    }

    /**
     * Validate category limits per event
     */
    protected function validateCategoryLimits($validator): void
    {
        if (!$this->event_id) {
            return;
        }

        $categoryCount = \App\Models\ProgramSessionCategory::where('event_id', $this->event_id)->count();
        
        if ($categoryCount >= 20) {
            $validator->errors()->add('event_id', 'Bir etkinlikte en fazla 20 kategori oluşturulabilir.');
        }
    }

    /**
     * Validate icon format and availability
     */
    protected function validateIconFormat($validator): void
    {
        if (!$this->icon) {
            return;
        }

        // Common icon libraries prefixes/formats
        $validIconPrefixes = [
            'fa-', 'fas-', 'far-', 'fab-', // FontAwesome
            'bi-', // Bootstrap Icons
            'ion-', // Ionic Icons
            'material-', // Material Icons
            'feather-', // Feather Icons
            'heroicon-', // Heroicons
            'lucide-', // Lucide Icons
        ];

        $icon = $this->icon;
        $hasValidPrefix = false;
        
        foreach ($validIconPrefixes as $prefix) {
            if (str_starts_with($icon, $prefix)) {
                $hasValidPrefix = true;
                break;
            }
        }

        // If no prefix, assume it's a basic icon name
        if (!$hasValidPrefix && !preg_match('/^[a-z0-9\-_]+$/', $icon)) {
            $validator->errors()->add('icon', 'İkon formatı geçersiz. Geçerli ikon kütüphanesi formatı kullanınız.');
        }

        // Check common restricted icon names
        $restrictedIcons = ['delete', 'remove', 'trash', 'error', 'warning'];
        if (in_array($icon, $restrictedIcons)) {
            $validator->errors()->add('icon', 'Bu ikon adı kategori için uygun değil.');
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
            $maxOrder = \App\Models\ProgramSessionCategory::where('event_id', $this->event_id)->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        return $key ? data_get($validated, $key, $default) : $validated;
    }
}
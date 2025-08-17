<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProgramSessionCategory;

class UpdateProgramSessionCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('programSessionCategory');
        
        if (!$category) {
            return false;
        }

        $user = $this->user();
        
        // Admin can update any category
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user belongs to the organization that owns the event
        return $user->organizations()
                   ->where('organizations.id', $category->event->organization_id)
                   ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $category = $this->route('programSessionCategory');
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                function ($attribute, $value, $fail) use ($category) {
                    // Check for duplicate category name within the same event, excluding current category
                    $exists = ProgramSessionCategory::where('event_id', $category->event_id)
                        ->where('name', trim($value))
                        ->where('id', '!=', $category->id)
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
                'regex:/^[a-zA-Z0-9\-_]+$/',
            ],
            
            'is_active' => [
                'boolean',
                function ($attribute, $value, $fail) use ($category) {
                    // Prevent deactivating category if it has active sessions
                    if (!$value && $category->programSessions()->exists()) {
                        $fail('Bu kategoriye ait oturumlar bulunduğu için kategori pasif hale getirilemez.');
                    }
                }
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
            
            'show_in_legend' => [
                'boolean',
            ],
            
            'is_featured' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
            
            'show_in_legend.boolean' => 'Lejantda göster seçimi doğru/yanlış değer olmalıdır.',
            'is_featured.boolean' => 'Öne çıkarılmış seçimi doğru/yanlış değer olmalıdır.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
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
            'show_in_legend' => 'lejantda göster',
            'is_featured' => 'öne çıkarılmış',
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
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->sort_order ? (int) $this->sort_order : null,
            'icon' => $this->icon ? trim(strtolower($this->icon)) : null,
            'show_in_legend' => $this->boolean('show_in_legend', true),
            'is_featured' => $this->boolean('is_featured', false),
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
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateColorContrast($validator);
            $this->validateCategoryUsage($validator);
            $this->validateIconFormat($validator);
            $this->validateFeaturedLimit($validator);
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

        $bgColor = $this->background_color;
        $textColor = $this->text_color;
        
        if (strtoupper($bgColor) === strtoupper($textColor)) {
            $validator->errors()->add('text_color', 'Metin rengi ve arka plan rengi aynı olamaz.');
        }

        // Advanced contrast ratio calculation
        $bgLuminance = $this->calculateLuminance($bgColor);
        $textLuminance = $this->calculateLuminance($textColor);
        
        $contrastRatio = ($bgLuminance > $textLuminance) 
            ? ($bgLuminance + 0.05) / ($textLuminance + 0.05)
            : ($textLuminance + 0.05) / ($bgLuminance + 0.05);

        // WCAG AA standard requires 4.5:1 for normal text
        if ($contrastRatio < 4.5) {
            $validator->errors()->add('text_color', 
                'Renk kombinasyonu erişilebilirlik standartlarını karşılamıyor (kontrast oranı: ' . 
                number_format($contrastRatio, 1) . ':1, gerekli: 4.5:1).');
        }
    }

    /**
     * Calculate relative luminance of a color
     */
    private function calculateLuminance($hexColor): float
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Validate category usage constraints
     */
    protected function validateCategoryUsage($validator): void
    {
        $category = $this->route('programSessionCategory');
        
        // If changing the category name, check if it's used in active sessions
        if ($this->name !== $category->name) {
            $activeSessionsCount = $category->programSessions()
                ->whereHas('venue.eventDay.event', function ($query) {
                    $query->where('end_date', '>=', now()->toDateString());
                })
                ->count();

            if ($activeSessionsCount > 0) {
                // Warning, but allow the change
                $validator->after(function ($validator) use ($activeSessionsCount) {
                    $message = "Bu kategoriye ait {$activeSessionsCount} aktif oturum var. " .
                              "İsim değişikliği tüm oturumlara yansıyacak.";
                    
                    // Add as a warning if your validation system supports it
                    // Otherwise, you might want to store this in session for display
                    session()->flash('warning', $message);
                });
            }
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

        $icon = $this->icon;
        
        // Check if icon has changed and validate new format
        $category = $this->route('programSessionCategory');
        if ($icon !== $category->icon) {
            // Validate icon exists in common libraries
            $commonIcons = [
                'calendar', 'clock', 'users', 'microphone', 'presentation',
                'star', 'heart', 'home', 'settings', 'user', 'email',
                'phone', 'location', 'document', 'folder', 'image',
                'video', 'audio', 'chart', 'graph', 'table', 'list'
            ];

            // Check for FontAwesome pattern
            $isFontAwesome = preg_match('/^(fa[bsr]?-)?[a-z\-]+$/', $icon);
            $isCommonIcon = in_array(str_replace(['fa-', 'fas-', 'far-', 'fab-'], '', $icon), $commonIcons);

            if (!$isFontAwesome && !$isCommonIcon) {
                $validator->errors()->add('icon', 
                    'İkon formatı veya adı geçersiz. Yaygın kullanılan ikon adlarını kullanınız.');
            }
        }
    }

    /**
     * Validate featured category limits
     */
    protected function validateFeaturedLimit($validator): void
    {
        if (!$this->boolean('is_featured')) {
            return;
        }

        $category = $this->route('programSessionCategory');
        
        // Check if this would exceed the featured category limit
        $featuredCount = ProgramSessionCategory::where('event_id', $category->event_id)
            ->where('is_featured', true)
            ->where('id', '!=', $category->id)
            ->count();

        if ($featuredCount >= 5) {
            $validator->errors()->add('is_featured', 
                'Bir etkinlikte en fazla 5 kategori öne çıkarılabilir.');
        }
    }

    /**
     * Handle any post-validation logic
     */
    protected function passedValidation(): void
    {
        $category = $this->route('programSessionCategory');
        
        // Log significant changes
        $changes = [];
        
        if ($this->name !== $category->name) {
            $changes[] = "İsim değişti: '{$category->name}' → '{$this->name}'";
        }
        
        if ($this->color !== $category->color) {
            $changes[] = "Renk değişti: {$category->color} → {$this->color}";
        }
        
        if ($this->boolean('is_active') !== $category->is_active) {
            $status = $this->boolean('is_active') ? 'aktif' : 'pasif';
            $changes[] = "Durum değişti: {$status}";
        }

        if (!empty($changes)) {
            \Log::info('Program Session Category Updated', [
                'category_id' => $category->id,
                'event_id' => $category->event_id,
                'user_id' => $this->user()->id,
                'changes' => $changes,
            ]);
        }
    }
}
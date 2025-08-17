<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventDayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        return auth()->user()->canManageOrganization($event->organization);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'title' => 'required|string|max:255',
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $event->start_date,
                'before_or_equal:' . $event->end_date,
                Rule::unique('event_days')->where('event_id', $event->id),
            ],
            'description' => 'nullable|string|max:1000',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'theme' => 'nullable|string|max:255',
            'special_notes' => 'nullable|string|max:2000',
            'dress_code' => 'nullable|string|max:255',
            'lunch_arrangements' => 'nullable|string|max:1000',
            'transportation_info' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'gün başlığı',
            'date' => 'tarih',
            'description' => 'açıklama',
            'start_time' => 'başlangıç saati',
            'end_time' => 'bitiş saati',
            'is_active' => 'aktif durumu',
            'sort_order' => 'sıra numarası',
            'theme' => 'tema',
            'special_notes' => 'özel notlar',
            'dress_code' => 'kıyafet kodu',
            'lunch_arrangements' => 'yemek düzenlemeleri',
            'transportation_info' => 'ulaşım bilgileri',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Gün başlığı zorunludur.',
            'date.required' => 'Tarih zorunludur.',
            'date.after_or_equal' => 'Tarih etkinlik başlangıç tarihinden önce olamaz.',
            'date.before_or_equal' => 'Tarih etkinlik bitiş tarihinden sonra olamaz.',
            'date.unique' => 'Bu tarihte zaten bir gün tanımlanmış.',
            'start_time.date_format' => 'Başlangıç saati HH:MM formatında olmalıdır.',
            'end_time.date_format' => 'Bitiş saati HH:MM formatında olmalıdır.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $event = $this->route('event');

        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->sort_order ?? $this->calculateDefaultSortOrder($event),
        ]);

        // Auto-generate title if not provided
        if (empty($this->title) && !empty($this->date)) {
            $dayNumber = $this->calculateDayNumber($event);
            $this->merge([
                'title' => $dayNumber > 1 ? "{$dayNumber}. Gün" : $event->title
            ]);
        }
    }

    /**
     * Calculate default sort order based on existing event days.
     */
    private function calculateDefaultSortOrder($event): int
    {
        $maxSortOrder = $event->eventDays()->max('sort_order') ?? 0;
        return $maxSortOrder + 1;
    }

    /**
     * Calculate day number based on date and existing days.
     */
    private function calculateDayNumber($event): int
    {
        if (!$this->date) {
            return 1;
        }

        $existingDays = $event->eventDays()
            ->where('date', '<', $this->date)
            ->count();

        return $existingDays + 1;
    }

    /**
     * Get the validated data with event_id added.
     */
    public function validatedWithEvent(): array
    {
        $validated = $this->validated();
        $validated['event_id'] = $this->route('event')->id;
        
        return $validated;
    }
}
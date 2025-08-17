<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventDayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $eventDay = $this->route('day'); // or eventDay depending on route parameter name
        return auth()->user()->canManageOrganization($eventDay->event->organization);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $eventDay = $this->route('day');
        $event = $eventDay->event;

        return [
            'title' => 'required|string|max:255',
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $event->start_date,
                'before_or_equal:' . $event->end_date,
                Rule::unique('event_days')
                    ->where('event_id', $event->id)
                    ->ignore($eventDay->id),
                function ($attribute, $value, $fail) use ($eventDay) {
                    // Check if changing date affects existing sessions
                    if ($value !== $eventDay->date && $eventDay->programSessions()->exists()) {
                        $fail('Bu güne ait program oturumları bulunduğu için tarih değiştirilemez.');
                    }
                },
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
        // No special preparation needed for update
    }
}
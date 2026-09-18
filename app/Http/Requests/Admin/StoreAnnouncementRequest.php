<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $this->user()?->can('sendNotifications', $event) ?? false;
    }

    public function rules(): array
    {
        /** @var Event $event */
        $event = $this->route('event');

        $sessionIds = $event->programSessions()->pluck('program_sessions.id');

        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'program_session_id' => ['nullable', 'integer', Rule::in($sessionIds->all())],
            'send_push' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('program_session_id') === '' || $this->input('program_session_id') === '0') {
            $this->merge(['program_session_id' => null]);
        }
    }

    public function attributes(): array
    {
        return [
            'title' => 'başlık',
            'body' => 'mesaj',
            'program_session_id' => 'oturum',
        ];
    }
}

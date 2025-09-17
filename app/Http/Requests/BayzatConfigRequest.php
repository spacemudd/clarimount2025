<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BayzatConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'api_key' => [
                'required',
                'string',
                'min:10',
            ],
            'api_url' => [
                'nullable',
                'url',
            ],
            'is_enabled' => [
                'boolean',
            ],
            'sync_frequency' => [
                'required',
                'in:manual,hourly,daily',
            ],
            'settings' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'api_key.required' => __('messages.api_key_required'),
            'api_key.min' => __('messages.api_key_too_short'),
            'api_url.url' => __('messages.invalid_api_url'),
            'sync_frequency.required' => __('messages.sync_frequency_required'),
            'sync_frequency.in' => __('messages.invalid_sync_frequency'),
        ];
    }

    public function prepareForValidation(): void
    {
        // Set default API URL if not provided
        if (empty($this->api_url)) {
            $this->merge([
                'api_url' => config('services.bayzat.default_api_url', 'https://integration.bayzat.com/attendance'),
            ]);
        }

        // Ensure is_enabled is boolean
        if ($this->has('is_enabled')) {
            $this->merge([
                'is_enabled' => (bool) $this->is_enabled,
            ]);
        }
    }
}

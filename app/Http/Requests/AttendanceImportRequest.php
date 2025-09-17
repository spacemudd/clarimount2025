<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:10240', // 10MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('messages.file_required'),
            'file.file' => __('messages.invalid_file'),
            'file.mimes' => __('messages.file_must_be_csv'),
            'file.max' => __('messages.file_too_large'),
        ];
    }
}

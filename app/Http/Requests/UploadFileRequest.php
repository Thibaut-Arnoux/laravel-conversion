<?php

namespace App\Http\Requests;

use App\Enums\FileExtensionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|string[]|string>
     */
    public function rules(): array
    {
        $fileExtensions = array_column(FileExtensionEnum::cases(), 'value');

        return [
            'file' => ['required', 'file', 'max:2048', 'mimes:'.implode(',', $fileExtensions)],
            'name' => ['required', 'max:255'],
        ];
    }
}

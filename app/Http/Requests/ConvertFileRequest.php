<?php

namespace App\Http\Requests;

use App\Enums\FileExtensionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ConvertFileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|string[]|string|array<int, \Illuminate\Validation\Rules\Enum>>
     */
    public function rules(): array
    {
        return [
            'convert_extension' => ['required', new Enum(FileExtensionEnum::class)],
        ];
    }
}

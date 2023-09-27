<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;

class ConverterFactory
{
    public static function createConverter(FileExtensionEnum $extension): IConverterService
    {
        return match ($extension->value) {
            'jpg', 'jpeg', 'png' => app()->make(ImageConverterService::class),
            'pdf' => app()->make(PdfConverterService::class),
            'docx', 'doc', 'odt' => app()->make(DocConverterService::class),
        };
    }
}

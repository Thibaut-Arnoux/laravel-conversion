<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;

class ConverterFactory
{
    public static function createConverter(FileExtensionEnum $extension): IConverterService
    {
        return match ($extension->value) {
            'jpg', 'jpeg', 'png' => new ImageConverterService(),
            'pdf' => new PdfConverterService(),
            'docx', 'doc', 'odt' => new DocConverterService(),
        };
    }
}

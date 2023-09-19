<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;

class ConverterFactory implements IConverterFactory
{
    public static function createConverter(FileExtensionEnum $extension): IConverterService
    {
        return match ($extension->value) {
            'jpg', 'jpeg', 'png' => new ImageConverterService(),
            'pdf' => new PdfConverterService(),
        };
    }
}

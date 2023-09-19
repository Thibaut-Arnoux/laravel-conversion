<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;

interface IConverterFactory
{
    public static function createConverter(FileExtensionEnum $file): IConverterService;
}

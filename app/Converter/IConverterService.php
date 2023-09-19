<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;

interface IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): Conversion;
}

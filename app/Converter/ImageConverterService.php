<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use Exception;

class ImageConverterService implements IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): Conversion
    {
        throw new Exception('Not yet implemented');
    }
}

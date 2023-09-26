<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;

interface IConverterService
{
    /**
     * @return Conversion[]
     */
    public function convert(File $file, FileExtensionEnum $convertExtension): array;

    /**
     * @return string[]
     */
    public function toImg(string $inputPath, string $extension = 'png'): array;

    /**
     * @return string[]
     */
    public function toPdf(string $inputPath): array;

    /**
     * @return string[]
     */
    public function toDoc(string $inputPath, string $extension = 'odt'): array;
}

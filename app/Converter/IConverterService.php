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

    public function toImg(string $inputPath, string $outputPath, int $pageNumber = 1): void;

    public function toPdf(string $inputPath, string $outputPath): void;
}

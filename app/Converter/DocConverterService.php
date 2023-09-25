<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\File;
use Exception;

class DocConverterService implements IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): array
    {
        throw new Exception('Not yet implemented');
    }

    public function toImg(string $inputPath, string $outputPath, int $pageNumber = 1): void
    {
        throw new Exception('Not yet implemented');
    }

    public function toPdf(string $inputPath, string $outputPath): void
    {
        throw new Exception('Not yet implemented');
    }

    public function toDoc(string $inputPath, string $outputPath): void
    {
        throw new Exception('Not yet implemented');
    }
}

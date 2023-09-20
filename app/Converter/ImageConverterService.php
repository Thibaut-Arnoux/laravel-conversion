<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use Exception;
use Illuminate\Http\File as HttpFile;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

class ImageConverterService implements IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): Conversion
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();
        $convertPath = $temporaryDirectory->path($file->name.'.'.$convertExtension->value);

        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => throw new Exception('No conversion required'),
            'pdf' => $this->toPdf(Storage::path($file->path), $convertPath),
        };

        $convertHttpFile = new HttpFile($convertPath);
        $convertHashPath = $convertHttpFile->hashName();
        Storage::putFileAs('', $convertHttpFile, $convertHashPath);
        $temporaryDirectory->delete();

        // register convert file
        // TODO : Commit file only when Conversion is saved
        $convertFile = new File();
        $convertFile->name = $file->name;
        $convertFile->path = $convertHashPath;
        $convertFile->extension = $convertExtension;
        $convertFile->save();

        // register conversion
        $conversion = new Conversion();
        $conversion->originalFile()->associate($file);
        $conversion->convertFile()->associate($convertFile);
        $conversion->save();

        return $conversion;
    }

    private function toPdf(string $inputPath, string $outputPath): void
    {
        try {
            $pdf = new \Imagick($inputPath);
            $pdf->setImageFormat('pdf');
            $pdf->writeImage($outputPath);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

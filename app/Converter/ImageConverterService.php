<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use Exception;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\DB;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

class ImageConverterService implements IConverterService
{
    /**
     * Convert the given file to the specified extension and save it.
     *
     * @param  File  $file The file to be converted.
     * @param  FileExtensionEnum  $convertExtension The extension to convert the file to.
     * @return Conversion[] Return list of conversions.
     *
     * @throws Exception Throws an exception if no conversion is required for the specified extension.
     */
    public function convert(File $file, FileExtensionEnum $convertExtension): array
    {
        // prepare paths
        $temporaryDirectory = (new TemporaryDirectory())->create();
        $convertPath = $temporaryDirectory->path($file->name.'.'.$convertExtension->value);

        // convert file
        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => $this->toImg(Storage::path($file->path), $convertPath),
            'pdf' => $this->toPdf(Storage::path($file->path), $convertPath),
        };

        // save converted file on disk
        $convertHttpFile = new HttpFile($convertPath);
        $convertHashPath = $convertHttpFile->hashName();
        Storage::putFileAs('', $convertHttpFile, $convertHashPath);
        $temporaryDirectory->delete();

        // TODO : Move to conversion save action
        return DB::transaction(function () use ($file, $convertHashPath, $convertExtension) {
            // save converted file on db
            $convertFile = new File();
            $convertFile->name = $file->name;
            $convertFile->path = $convertHashPath;
            $convertFile->extension = $convertExtension;
            $convertFile->save();

            // save conversion
            $conversion = new Conversion();
            $conversion->originalFile()->associate($file);
            $conversion->convertFile()->associate($convertFile);
            $conversion->save();

            return [$conversion];
        });
    }

    public function toImg(string $inputPath, string $outputPath, int $pageNumber = 1): void
    {
        throw new Exception('No conversion required');
    }

    /**
     * Converts an image file to PDF format.
     *
     * @param  string  $inputPath The path to the input image file.
     * @param  string  $outputPath The path to save the output PDF file.
     *
     * @throws Exception If there is an error during the conversion process.
     */
    public function toPdf(string $inputPath, string $outputPath): void
    {
        try {
            $pdf = new \Imagick($inputPath);
            $pdf->setImageFormat('pdf');
            $pdf->writeImage($outputPath);
        } catch (Exception $e) {
            throw new Exception('Failed to convert img to pdf: '.$e->getMessage());
        }
    }
}

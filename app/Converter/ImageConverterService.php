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
    /**
     * Converts a file to a specified extension and saves it.
     *
     * @param  File  $file The file to be converted.
     * @param  FileExtensionEnum  $convertExtension The extension to convert the file to.
     * @return Conversion The conversion object that represents the converted file.
     *
     * @throws Exception When no conversion is required for the given extension.
     */
    public function convert(File $file, FileExtensionEnum $convertExtension): Conversion
    {
        // prepare paths
        $temporaryDirectory = (new TemporaryDirectory())->create();
        $convertPath = $temporaryDirectory->path($file->name.'.'.$convertExtension->value);

        // convert file
        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => throw new Exception('No conversion required'),
            'pdf' => $this->toPdf(Storage::path($file->path), $convertPath),
        };

        // save converted file on disk
        $convertHttpFile = new HttpFile($convertPath);
        $convertHashPath = $convertHttpFile->hashName();
        Storage::putFileAs('', $convertHttpFile, $convertHashPath);
        $temporaryDirectory->delete();

        // save converted file on db
        // TODO : Commit file only when Conversion is saved
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

        return $conversion;
    }

    /**
     * Converts an image file to PDF format.
     *
     * @param  string  $inputPath The path to the input image file.
     * @param  string  $outputPath The path to save the output PDF file.
     *
     * @throws Exception If there is an error during the conversion process.
     */
    private function toPdf(string $inputPath, string $outputPath): void
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

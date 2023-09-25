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
        $inputPath = Storage::path($file->path);
        $convertPath = $temporaryDirectory->path($file->name.'.'.$convertExtension->value);

        // convert file
        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => $this->toImg($inputPath, $convertPath),
            'pdf' => $this->toPdf($inputPath, $convertPath),
            'docx', 'doc', 'odt' => $this->toDoc($inputPath, $convertPath),
        };

        // save converted file on disk
        $convertHttpFile = new HttpFile($convertPath);
        $convertName = pathinfo($convertPath, PATHINFO_FILENAME);
        $convertPath = $convertHttpFile->hashName();
        Storage::putFileAs('', $convertHttpFile, $convertPath);

        // TODO : Move to conversion save action
        $conversion = DB::transaction(function () use ($file, $convertName, $convertPath, $convertExtension) {
            // save converted file on db
            $convertFile = new File();
            $convertFile->name = $convertName;
            $convertFile->path = $convertPath;
            $convertFile->extension = $convertExtension;
            $convertFile->save();

            // save conversion
            $conversion = new Conversion();
            $conversion->originalFile()->associate($file);
            $conversion->convertFile()->associate($convertFile);
            $conversion->save();

            return [$conversion];
        });
        $temporaryDirectory->delete();

        return $conversion;
    }

    /**
     * @throws Exception No conversion required.
     */
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

    /**
     * Converts an image to doc format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $outputPath The output file path.
     *
     * @throws Exception Not yet implemented
     */
    public function toDoc(string $inputPath, string $outputPath): void
    {
        throw new Exception('Not yet implemented');
    }
}

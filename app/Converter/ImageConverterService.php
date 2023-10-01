<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use Exception;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

class ImageConverterService implements IConverterService
{
    public function __construct(
        public TemporaryDirectory $temporaryDirectory
    ) {
    }

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
        // convert file
        $inputPath = Storage::path($file->path);
        $extension = $convertExtension->value;
        $convertPaths = match ($extension) {
            'jpg', 'jpeg', 'png' => $this->toImg($inputPath, $extension),
            'pdf' => $this->toPdf($inputPath),
            'docx', 'doc', 'odt' => $this->toDoc($inputPath, $extension),
        };

        if (count($convertPaths) !== 1) {
            throw new Exception('Unexpected output for image conversion');
        }

        // save converted file on disk
        $convertedPath = $convertPaths[0];
        $convertHttpFile = new HttpFile($convertedPath);
        $convertName = $file->name;
        $convertPath = $convertHttpFile->hashName();
        Storage::putFileAs('', $convertHttpFile, $convertPath);

        // TODO : Move to conversion save action
        $conversion = DB::transaction(function () use ($file, $convertName, $convertPath, $convertExtension) {
            $userId = Auth::user()->id;
            // save converted file on db
            $convertFile = new File();
            $convertFile->name = $convertName;
            $convertFile->path = $convertPath;
            $convertFile->extension = $convertExtension;
            $convertFile->user_id = $userId;
            $convertFile->save();

            // save conversion
            $conversion = new Conversion();
            $conversion->originalFile()->associate($file);
            $conversion->convertFile()->associate($convertFile);
            $conversion->user_id = $userId;
            $conversion->save();

            return [$conversion];
        });
        $this->temporaryDirectory->delete();

        return $conversion;
    }

    /**
     * @throws Exception No conversion required.
     */
    public function toImg(string $inputPath, string $extension = 'png'): array
    {
        throw new Exception('No conversion required');
    }

    /**
     * Convert an Image file to PDF format.
     *
     * @param  string  $inputPath The path to the input image file.
     *
     * @throws Exception If there is an error during the conversion process.
     */
    public function toPdf(string $inputPath): array
    {
        $inputFilename = pathinfo($inputPath, PATHINFO_FILENAME);
        $outputPath = $this->temporaryDirectory->path($inputFilename.'.'.FileExtensionEnum::PDF->value);

        try {
            $pdf = new \Imagick($inputPath);
            $pdf->setImageFormat('pdf');
            $pdf->writeImage($outputPath);
        } catch (Exception $e) {
            throw new Exception('Failed to convert img to pdf: '.$e->getMessage());
        }

        $convertPaths = glob($this->temporaryDirectory->path().'/*.'.FileExtensionEnum::PDF->value);
        if ($convertPaths === false) {
            throw new Exception('No conversion files found');
        }

        return $convertPaths;
    }

    /**
     * Convert an Image file to Doc format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $extension The output extension.
     *
     * @throws Exception Not yet implemented
     */
    public function toDoc(string $inputPath, string $extension = 'odt'): array
    {
        $convertPaths = $this->toPdf($inputPath);
        if (count($convertPaths) !== 1) {
            throw new Exception('Unexpected output for img conversion to pdf');
        }
        $convertPath = $convertPaths[0];
        $intermediateExtension = (new HttpFile($convertPath))->extension();
        $intermediateExtensionEnum = FileExtensionEnum::from($intermediateExtension);

        $converter = ConverterFactory::createConverter($intermediateExtensionEnum);

        return $converter->toDoc($convertPath, $extension);
    }
}

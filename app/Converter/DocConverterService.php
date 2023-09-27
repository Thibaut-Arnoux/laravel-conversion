<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use DB;
use Exception;
use Illuminate\Http\File as HttpFile;
use Process;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

class DocConverterService implements IConverterService
{
    public function __construct(
        public TemporaryDirectory $temporaryDirectory
    ) {
    }

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

        $conversions = [];
        $convertPathsLength = count($convertPaths);
        for ($i = 0; $i < $convertPathsLength; $i++) {
            // save converted file on disk
            $convertedPath = $convertPaths[$i];
            $convertHttpFile = new HttpFile($convertedPath);
            $convertName = count($convertPaths) === 1 ? $file->name : $file->name.'-'.$i;
            $convertPath = $convertHttpFile->hashName();
            Storage::putFileAs('', $convertHttpFile, $convertPath);

            // TODO : Move to conversion save action
            $conversions[] = DB::transaction(function () use ($file, $convertName, $convertPath, $convertExtension) {
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

                return $conversion;
            });
        }
        $this->temporaryDirectory->delete();

        return $conversions;
    }

    /**
     * Convert a Doc to Image format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $extension The output extension.
     *
     * @throws Exception Not yet implemented
     */
    public function toImg(string $inputPath, string $extension = 'png'): array
    {
        $convertPaths = $this->toPdf($inputPath);
        if (count($convertPaths) !== 1) {
            throw new Exception('Unexpected output for odt conversion to pdf');
        }
        $convertPath = $convertPaths[0];
        $intermediateExtension = (new HttpFile($convertPath))->extension();
        $intermediateExtensionEnum = FileExtensionEnum::from($intermediateExtension);

        $converter = ConverterFactory::createConverter($intermediateExtensionEnum);

        return $converter->toImg($convertPath, $extension);
    }

    /**
     * Convert a Doc file to PDF format.
     *
     * @param  string  $inputPath The input file path.
     *
     * @throws Exception If failed to convert the file to a PDF.
     */
    public function toPdf(string $inputPath): array
    {
        $dirname = $this->temporaryDirectory->path();

        if (! $dirname) {
            throw new Exception('Invalid path during pdf conversion to doc');
        }

        $command = "soffice --headless --convert-to pdf --outdir \"$dirname\" \"$inputPath\"";
        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Failed to convert pdf to doc: '.$result->errorOutput());
        }

        $convertPaths = glob($dirname.'/*.'.FileExtensionEnum::PDF->value);
        if ($convertPaths === false) {
            throw new Exception('No conversion files found');
        }

        return $convertPaths;
    }

    /**
     * @throws Exception No conversion is required.
     */
    public function toDoc(string $inputPath, string $extension = 'odt'): array
    {
        throw new Exception('No conversion required');
    }
}

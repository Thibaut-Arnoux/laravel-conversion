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
    public function convert(File $file, FileExtensionEnum $convertExtension): array
    {
        // prepare paths
        $temporaryDirectory = (new TemporaryDirectory())->create();
        $inputPath = Storage::path($file->path);
        $filename = pathinfo($inputPath, PATHINFO_FILENAME);
        $convertPath = $temporaryDirectory->path($filename.'.'.$convertExtension->value);

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

    public function toImg(string $inputPath, string $outputPath, int $pageNumber = 1): void
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Converts a Doc file to PDF format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $outputPath The output file path.
     *
     * @throws Exception If failed to convert the doc file to a PDF.
     */
    public function toPdf(string $inputPath, string $outputPath): void
    {
        // Care output path will be override by soffice cli
        $fileInfo = pathinfo($outputPath);
        $dirname = $fileInfo['dirname'] ?? '';

        if (! $dirname) {
            throw new Exception('Invalid path during pdf conversion to doc');
        }

        $command = "soffice --headless --convert-to pdf --outdir \"$dirname\" \"$inputPath\"";
        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Failed to convert pdf to doc: '.$result->errorOutput());
        }
    }

    /**
     * @throws Exception No conversion is required.
     */
    public function toDoc(string $inputPath, string $outputPath): void
    {
        throw new Exception('No conversion required');
    }
}

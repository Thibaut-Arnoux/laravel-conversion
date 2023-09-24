<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use DB;
use Exception;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PdfConverterService implements IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): array
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => $this->convertImg($file, $temporaryDirectory, $convertExtension),
            'pdf' => $this->toPdf(Storage::path($file->path), $temporaryDirectory->path($file->name.'.'.$convertExtension->value)),
        };

        // parse temporary foldet to get all converted files
        $convertPaths = glob($temporaryDirectory->path().'/*.'.$convertExtension->value);
        if ($convertPaths === false) {
            throw new Exception('No conversion files found');
        }

        $conversions = [];
        foreach ($convertPaths as $convertPath) {
            // save converted file on disk
            $convertHttpFile = new HttpFile($convertPath);
            $convertName = pathinfo($convertPath, PATHINFO_FILENAME);
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
        $temporaryDirectory->delete();

        return $conversions;
    }

    public function convertImg(File $file, TemporaryDirectory $outputTempFolder, FileExtensionEnum $convertExtension): void
    {
        $pdfPath = Storage::path($file->path);
        $nbPages = (new Pdf($pdfPath))->getNumberOfPages();

        for ($i = 0; $i < $nbPages; $i++) {
            $convertPath = $outputTempFolder->path($file->name.'-'.$i.'.'.$convertExtension->value);
            $this->toImg($pdfPath, $convertPath, $i + 1);
        }
    }

    public function toImg(string $inputPath, string $outputPath, int $pageNumber = 1): void
    {
        try {
            $pdf = new Pdf($inputPath);
            $pdf->setPage($pageNumber)
                ->saveImage($outputPath);
        } catch (Exception $e) {
            throw new Exception('Failed to convert pdf to img: '.$e->getMessage());
        }
    }

    public function toPdf(string $inputPath, string $outputPath): void
    {
        throw new Exception('No conversion required');
    }
}

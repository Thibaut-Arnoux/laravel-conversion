<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use DB;
use Exception;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\Storage;
use Process;
use Spatie\PdfToImage\Pdf;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PdfConverterService implements IConverterService
{
    public function convert(File $file, FileExtensionEnum $convertExtension): array
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();
        $inputPath = Storage::path($file->path);
        $outputPath = $temporaryDirectory->path($file->name.'.'.$convertExtension->value);

        match ($convertExtension->value) {
            'jpg', 'jpeg', 'png' => $this->convertImg($file, $temporaryDirectory, $convertExtension),
            'pdf' => $this->toPdf($inputPath, $outputPath),
            'docx', 'doc', 'odt' => $this->toDoc($inputPath, $outputPath),
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

    /**
     * Converts a PDF file to an image file.
     *
     * @param  string  $inputPath The path of the PDF file to convert.
     * @param  string  $outputPath The path where the converted image file should be saved.
     * @param  int  $pageNumber The page number of the PDF file to convert. Default is 1.
     *
     * @throws Exception If failed to convert the PDF file to an image.
     */
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

    /**
     * @throws Exception No conversion is required.
     */
    public function toPdf(string $inputPath, string $outputPath): void
    {
        throw new Exception('No conversion required');
    }

    /**
     * Converts a PDF file to doc format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $outputPath The output file path.
     *
     * @throws Exception If failed to convert the PDF file to doc.
     */
    public function toDoc(string $inputPath, string $outputPath): void
    {
        // Care output path will be override by soffice cli
        $fileInfo = pathinfo($outputPath);
        $extension = $fileInfo['extension'] ?? 'docx';
        $dirname = $fileInfo['dirname'] ?? '';

        if (! $dirname) {
            throw new Exception('Invalid path during pdf conversion to doc');
        }

        $command = "soffice --headless --infilter=\"writer_pdf_import\" --convert-to $extension --outdir \"$dirname\" \"$inputPath\"";
        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Failed to convert pdf to doc: '.$result->errorOutput());
        }
    }
}

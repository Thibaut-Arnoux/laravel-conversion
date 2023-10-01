<?php

namespace App\Converter;

use App\Enums\FileExtensionEnum;
use App\Models\Conversion;
use App\Models\File;
use DB;
use Exception;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Process;
use Spatie\PdfToImage\Pdf;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PdfConverterService implements IConverterService
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

                return $conversion;
            });
        }
        $this->temporaryDirectory->delete();

        return $conversions;
    }

    /**
     * Convert a PDF file to an Image format.
     *
     * @param  string  $inputPath The path of the PDF file to convert.
     * @param  string  $extension The output extension.
     *
     * @throws Exception If failed to convert the file to an image.
     */
    public function toImg(string $inputPath, string $extension = 'png'): array
    {
        $inputFilename = pathinfo($inputPath, PATHINFO_FILENAME);
        $pdf = new Pdf($inputPath);
        $nbPages = $pdf->getNumberOfPages();

        for ($i = 0; $i < $nbPages; $i++) {
            try {
                $outputPath = $this->temporaryDirectory->path($inputFilename.'-'.$i.'.'.$extension);
                $pdf->setPage($i + 1)
                    ->saveImage($outputPath);
            } catch (Exception $e) {
                throw new Exception('Failed to convert pdf to img: '.$e->getMessage());
            }
        }

        $convertPaths = glob($this->temporaryDirectory->path().'/*.'.$extension);
        if ($convertPaths === false) {
            throw new Exception('No conversion files found');
        }

        return $convertPaths;
    }

    /**
     * @throws Exception No conversion is required.
     */
    public function toPdf(string $inputPath): array
    {
        throw new Exception('No conversion required');
    }

    /**
     * Convert a PDF file to a Doc format.
     *
     * @param  string  $inputPath The input file path.
     * @param  string  $extension The output extension.
     *
     * @throws Exception If failed to convert the PDF file to doc.
     */
    public function toDoc(string $inputPath, string $extension = 'odt'): array
    {
        $dirname = $this->temporaryDirectory->path();

        if (! $dirname) {
            throw new Exception('Invalid path during pdf conversion to doc');
        }

        $command = "soffice --headless --infilter=\"writer_pdf_import\" --convert-to $extension --outdir \"$dirname\" \"$inputPath\"";
        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Failed to convert pdf to doc: '.$result->errorOutput());
        }

        $convertPaths = glob($dirname.'/*.'.$extension);
        if ($convertPaths === false) {
            throw new Exception('No conversion files found');
        }

        return $convertPaths;
    }
}

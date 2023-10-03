<?php

namespace App\Http\Controllers;

use App\Converter\IConverterService;
use App\Enums\FileExtensionEnum;
use App\Http\Requests\ConvertFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\ConversionResource;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithSuccess(
            FileResource::collection(
                File::query()
                    ->whereUserId($request->user()->id)
                    ->get()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        /** @var \Illuminate\Http\UploadedFile $uploadFile */
        $uploadFile = $request->file;
        Storage::putFileAs('', $uploadFile, $uploadFile->hashName());

        $file = new File();
        $file->name = $request->name;
        $file->path = $uploadFile->hashName();
        $file->extension = FileExtensionEnum::from($uploadFile->extension());
        $file->user_id = $request->user()->id;
        $file->save();

        return $this->respondCreated(new FileResource($file));
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file): JsonResponse
    {
        $this->authorize('view', $file);

        $file->load('conversions');

        return $this->respondWithSuccess(
            new FileResource(
                $file
            )
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file): JsonResponse
    {
        $this->authorize('delete', $file);

        $file->delete();

        return $this->respondNoContent();
    }

    /**
     * Convert file into another format specify in query parameter
     */
    public function convert(ConvertFileRequest $request, File $file, IConverterService $converterService): JsonResponse
    {
        $this->authorize('convert', $file);

        $convertExtension = FileExtensionEnum::from($request->convert_format);
        $conversions = $converterService->convert($file, $convertExtension);

        return $this->respondCreated(ConversionResource::collection($conversions));
    }

    /**
     * Download the specified resource.
     */
    public function download(File $file): StreamedResponse
    {
        $this->authorize('download', $file);

        return Storage::download($file->path, $file->name.'.'.$file->extension->value);
    }
}

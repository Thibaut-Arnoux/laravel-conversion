<?php

namespace App\Http\Controllers;

use App\Converter\IConverterFactory;
use App\Enums\FileExtensionEnum;
use App\Http\Requests\ConvertFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return $this->respondWithSuccess(new FileCollection(File::all()));
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
        $file->save();

        return $this->respondCreated(new FileResource($file));
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file): JsonResponse
    {
        return $this->respondWithSuccess(new FileResource($file));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, File $file)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(File $file)
    // {
    //     //
    // }

    public function convert(ConvertFileRequest $request, File $file, IConverterFactory $converter)
    {
        $convertExtension = FileExtensionEnum::from($request->convert_extension);

        $converter->createConverter($convertExtension)->convert($file, $convertExtension);

        // TODO : Convert file with factory

        // TODO : Return convert resource
    }
}

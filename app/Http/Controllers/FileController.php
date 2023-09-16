<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
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
    // public function index()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileUploadRequest $request): JsonResponse
    {

        $validated = $request->validated();
        /** @var \Illuminate\Http\UploadedFile $uploadFile */
        $uploadFile = $validated['file'];
        Storage::putFileAs('', $uploadFile, $uploadFile->hashName());

        $file = new File();
        $file->name = $validated['name'];
        $file->path = $uploadFile->hashName();
        $file->extension = $uploadFile->extension();
        $file->save();

        return $this->respondCreated(new FileResource($file));
    }

    /**
     * Display the specified resource.
     */
    // public function show(File $file)
    // {
    //     //
    // }

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
}

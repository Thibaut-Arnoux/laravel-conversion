<?php

namespace App\Http\Controllers;

use App\Converter\IConverterService;
use App\Enums\FileExtensionEnum;
use App\Http\Requests\ConvertFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\ConversionResource;
use App\Http\Resources\FileResource;
use App\Http\Responses\CollectionResponse;
use App\Http\Responses\MessageResponse;
use App\Http\Responses\ModelResponse;
use App\Models\File;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Responsable
    {
        return new CollectionResponse(
            FileResource::collection(
                QueryBuilder::for(File::class)
                    ->allowedFilters([
                        'name',
                        AllowedFilter::callback('has_conversions', function (Builder $query, $value) {
                            return $value === true ? $query->has('conversions') : $query->doesntHave('conversions');
                        }),
                    ])
                    ->whereUserId($request->user()->id)
                    ->jsonPaginate()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UploadFileRequest $request): Responsable
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

        return new ModelResponse(
            new FileResource($file),
            Response::HTTP_CREATED,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file): Responsable
    {
        $this->authorize('view', $file);

        $file->load('conversions');

        return new ModelResponse(
            new FileResource($file),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file): Responsable
    {
        $this->authorize('delete', $file);

        $file->delete();

        return new MessageResponse(
            [],
            Response::HTTP_NO_CONTENT,
        );
    }

    /**
     * Convert file into another format specify in query parameter
     */
    public function convert(ConvertFileRequest $request, File $file, IConverterService $converterService): Responsable
    {
        $this->authorize('convert', $file);

        $convertExtension = FileExtensionEnum::from($request->convert_format);
        $conversions = $converterService->convert($file, $convertExtension);

        return new CollectionResponse(
            ConversionResource::collection($conversions),
            Response::HTTP_CREATED,
        );
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

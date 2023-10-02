<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversionResource;
use App\Models\Conversion;
use Illuminate\Http\JsonResponse;

class ConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return $this->respondWithSuccess(
            ConversionResource::collection(
                Conversion::query()
                    ->with('originalFile', 'convertFile')
                    ->get()
            )
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversion $conversion): JsonResponse
    {
        $conversion->load(['originalFile', 'convertFile']);

        return $this->respondWithSuccess(new ConversionResource($conversion));
    }
}

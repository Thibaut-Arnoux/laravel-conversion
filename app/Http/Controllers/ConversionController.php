<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversionCollection;
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
        return $this->respondWithSuccess(new ConversionCollection(Conversion::all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversion $conversion): JsonResponse
    {
        return $this->respondWithSuccess(new ConversionResource($conversion));
    }
}

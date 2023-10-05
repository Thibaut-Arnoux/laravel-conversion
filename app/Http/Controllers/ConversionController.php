<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversionCollection;
use App\Http\Resources\ConversionResource;
use App\Models\Conversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return ConversionCollection::make(
            Conversion::query()
                ->with('originalFile', 'convertFile')
                ->whereUserId($request->user()->id)
                ->get()
        )
            ->toResponse($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Conversion $conversion): JsonResponse
    {
        $conversion->load(['originalFile', 'convertFile']);

        return ConversionResource::make($conversion)
            ->toResponse($request);
    }
}

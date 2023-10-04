<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasResponse
{
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            $this->data,
            $this->status
        );
    }
}

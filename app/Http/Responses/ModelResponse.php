<?php

namespace App\Http\Responses;

use App\Traits\HasResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ModelResponse implements Responsable
{
    use HasResponse;

    public function __construct(
        protected readonly JsonResource $data,
        protected readonly int $status = Response::HTTP_OK
    ) {
    }
}

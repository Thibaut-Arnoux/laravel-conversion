<?php

namespace App\Http\Responses;

use App\Traits\HasResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CollectionResponse implements Responsable
{
    use HasResponse;

    public function __construct(
        protected readonly AnonymousResourceCollection $data,
        protected readonly int $status = Response::HTTP_OK
    ) {
    }
}

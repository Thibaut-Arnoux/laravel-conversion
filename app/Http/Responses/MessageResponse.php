<?php

namespace App\Http\Responses;

use App\Traits\HasResponse;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class MessageResponse implements Responsable
{
    use HasResponse;

    /**
     * @param  array<mixed>  $data
     */
    public function __construct(
        protected readonly array $data,
        protected readonly int $status = Response::HTTP_OK
    ) {
    }
}

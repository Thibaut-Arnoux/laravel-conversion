<?php

namespace App\Http\Resources;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read null|CarbonInterface $resource
 */
class DateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     * human: null|string,
     * string: null|string,
     * local: null|string}
     */
    public function toArray(Request $request): array
    {
        return [
            'human' => $this->resource?->diffForHumans(),
            'string' => $this->resource?->toDateTimeString(),
            'local' => $this->resource?->toDateTimeLocalString(),
        ];
    }
}

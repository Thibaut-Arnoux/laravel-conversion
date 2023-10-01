<?php

namespace App\Http\Resources;

use App\Models\Conversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Conversion $resource
 */
class ConversionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     * id: string,
     * original_file: FileResource,
     * convert_file: FileResource,
     * created_by: UserResource,
     * created_at: DateResource,
     * updated_at: DateResource}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'original_file' => new FileResource($this->whenLoaded('originalFile')),
            'convert_file' => new FileResource($this->whenLoaded('convertFile')),
            'created_by' => new UserResource($this->whenLoaded('user')),
            'created_at' => new DateResource($this->resource->created_at),
            'updated_at' => new DateResource($this->resource->updated_at),
        ];
    }
}

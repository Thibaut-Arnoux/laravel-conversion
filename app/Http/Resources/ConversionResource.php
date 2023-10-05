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
            'original_file' => FileResource::make($this->whenLoaded('originalFile')),
            'convert_file' => FileResource::make($this->whenLoaded('convertFile')),
            'created_by' => UserResource::make($this->whenLoaded('user')),
            'created_at' => DateResource::make($this->resource->created_at),
            'updated_at' => DateResource::make($this->resource->updated_at),
        ];
    }
}

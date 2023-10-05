<?php

namespace App\Http\Resources;

use App\Enums\FileExtensionEnum;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read File $resource
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     * id: string,
     * name: string,
     * extension: FileExtensionEnum,
     * created_by: UserResource,
     * created_at: DateResource,
     * updated_at: DateResource}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'extension' => $this->resource->extension,
            'conversions' => ConversionCollection::make($this->whenLoaded('conversions')),
            'created_by' => UserResource::make($this->whenLoaded('user')),
            'created_at' => DateResource::make($this->resource->created_at),
            'updated_at' => DateResource::make($this->resource->updated_at),
        ];
    }
}

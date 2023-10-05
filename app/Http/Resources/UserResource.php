<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     * id: string,
     * name: string,
     * email: string,
     * created_at: DateResource,
     * updated_at: DateResource}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'created_at' => DateResource::make($this->resource->created_at),
            'updated_at' => DateResource::make($this->resource->updated_at),
        ];
    }
}

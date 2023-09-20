<?php

namespace App\Http\Resources;

use App\Enums\FileExtensionEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\File
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: string, name: string, extension: FileExtensionEnum, created_at: Carbon|null, updated_at: Carbon|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'extension' => $this->extension,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

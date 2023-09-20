<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Conversion
 */
class ConversionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: string, original_file_id: string, convert_file_id: string, created_at: Carbon|null, updated_at: Carbon|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_file_id' => $this->original_file_id,
            'convert_file_id' => $this->convert_file_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

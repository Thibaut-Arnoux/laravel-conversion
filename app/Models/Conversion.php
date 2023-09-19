<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversion extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * Retrieves the original file associated with this model.
     *
     * @return BelongsTo<File, self>
     */
    public function originalFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'original_file_id');
    }

    /**
     * Retrieves the convert file associated with this model.
     *
     * @return BelongsTo<File, self>
     */
    public function convertFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'convert_file_id');
    }
}

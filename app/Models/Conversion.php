<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversion extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * Retrieves the original file associated with this model.
     *
     * @return HasOne<File> The original file relationship.
     */
    public function originalFile(): HasOne
    {
        return $this->hasOne(File::class, 'original_file_id');
    }

    /**
     * Retrieves the convert file associated with this model.
     *
     * @return HasOne<File>
     */
    public function convertFile(): HasOne
    {
        return $this->hasOne(File::class, 'convert_file_id');
    }
}

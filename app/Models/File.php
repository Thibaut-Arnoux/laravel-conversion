<?php

namespace App\Models;

use App\Enums\FileExtensionEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $casts = [
        'extension' => FileExtensionEnum::class,
    ];

    /**
     * Retrieves the conversions associated with this model.
     *
     * @return HasMany<Conversion>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class, 'original_file_id');
    }

    /**
     * Retrieves the user associated with this file.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

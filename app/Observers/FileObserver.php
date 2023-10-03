<?php

namespace App\Observers;

use App\Models\File;

class FileObserver
{
    /**
     * Handle the File "deleted" event.
     */
    public function deleted(File $file): void
    {
        $file->conversions()->delete();
    }

    /**
     * Handle the File "restored" event.
     */
    public function restored(File $file): void
    {
        $file->conversions()->restore();
    }
}

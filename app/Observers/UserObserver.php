<?php

namespace App\Observers;

use App\Models\File;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->files->each(fn (File $file) => $file->delete());
    }

    /**
     * Handle the User "restored" event.
     *
     * @see https://github.com/laravel/framework/issues/2536
     */
    public function restored(User $user): void
    {
        File::query()
            ->withTrashed()
            ->whereUserId($user->id)
            ->get()
            ->each(fn (File $file) => $file->restore());
    }
}

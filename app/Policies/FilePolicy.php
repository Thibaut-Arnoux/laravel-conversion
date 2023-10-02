<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can convert the model.
     */
    public function convert(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    /**
     * Determine whether the user can view the media.
     */
    public function view(?User $user, Media $media): bool
    {
        return true; // All can view
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update(User $user, Media $media): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        // Only admin can delete media
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the media.
     */
    public function restore(User $user, Media $media): bool
    {
        return false; // Media don't support restore
    }

    /**
     * Determine whether the user can permanently delete the media.
     */
    public function forceDelete(User $user, Media $media): bool
    {
        return $user->isAdmin();
    }
}

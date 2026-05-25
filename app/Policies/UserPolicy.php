<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if user can view the user.
     */
    public function view(User $authUser, User $user): bool
    {
        // User can view themselves or admins can view anyone
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    /**
     * Determine if user can edit the user.
     */
    public function edit(User $authUser, User $user): bool
    {
        // User can only edit themselves or admin can edit anyone
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    /**
     * Determine if user can update their profile.
     */
    public function updateProfile(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    /**
     * Determine if user can change their password.
     */
    public function changePassword(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }

    /**
     * Determine if admin can suspend user.
     */
    public function suspend(User $authUser, User $user): bool
    {
        return $authUser->isAdmin() && $authUser->id !== $user->id;
    }

    /**
     * Determine if admin can ban user.
     */
    public function ban(User $authUser, User $user): bool
    {
        return $authUser->isAdmin() && $authUser->id !== $user->id;
    }

    /**
     * Determine if admin can restore user.
     */
    public function restore(User $authUser, User $user): bool
    {
        return $authUser->isAdmin() && $authUser->id !== $user->id;
    }

    /**
     * Determine if user can manage their addresses.
     */
    public function manageAddresses(User $authUser, User $user): bool
    {
        return $authUser->id === $user->id;
    }
}

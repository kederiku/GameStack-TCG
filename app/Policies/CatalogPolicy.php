<?php

namespace App\Policies;

use App\Models\CatalogRecord;
use App\Models\User;

class CatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin && $user->hasVerifiedEmail();
    }

    public function view(User $user, CatalogRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, CatalogRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, CatalogRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}

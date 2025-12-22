<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class ActiveUserProvider extends EloquentUserProvider
{
    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        if (! $this->isActive($user)) {
            return false;
        }

        return parent::validateCredentials($user, $credentials);
    }

    protected function isActive(UserContract $user): bool
    {
        return property_exists($user, 'is_active') ? (bool) $user->is_active : true;
    }
}

<?php

namespace App\Policies;

use App\Models\ExternalCalculation;
use App\Models\User;

class ExternalCalculationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ExternalCalculation $record): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, ExternalCalculation $record): bool
    {
        return true;
    }

    public function delete(?User $user, ExternalCalculation $record): bool
    {
        return false;
    }
}

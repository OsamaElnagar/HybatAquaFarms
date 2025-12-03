<?php

namespace App\Observers;

use App\Models\Factory;

class FactoryObserver
{
    public function creating(Factory $factory): void
    {
        if (! $factory->code) {
            $factory->code = static::generateCode();
        }
    }

    protected static function generateCode(): string
    {
        $lastFactory = Factory::latest('id')->first();
        $number = $lastFactory ? ((int) substr($lastFactory->code, 4)) + 1 : 1;

        return 'FAC-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}

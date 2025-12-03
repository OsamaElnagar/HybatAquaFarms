<?php

namespace App\Observers;

use App\Models\ExpenseCategory;

class ExpenseCategoryObserver
{
    public function creating(ExpenseCategory $category): void
    {
        if (! $category->code) {
            $category->code = static::generateCode();
        }
    }

    protected static function generateCode(): string
    {
        $lastCategory = ExpenseCategory::latest('id')->first();
        $number = $lastCategory ? ((int) substr($lastCategory->code, 4)) + 1 : 1;

        return 'EXC-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}

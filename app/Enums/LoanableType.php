<?php

namespace App\Enums;

use App\Models\Factory;
use App\Models\Trader;
use Filament\Support\Contracts\HasLabel;

enum LoanableType: string implements HasLabel
{
    case Trader = 'trader';
    case Factory = 'factory';

    public function getLabel(): string
    {
        return match ($this) {
            self::Trader => 'تاجر',
            self::Factory => 'مصنع / مورد',
        };
    }

    public function modelClass(): string
    {
        return match ($this) {
            self::Trader => Trader::class,
            self::Factory => Factory::class,
        };
    }

    /**
     * The posting rule event key for netting repayment.
     */
    public function nettingPostingKey(): string
    {
        return match ($this) {
            self::Trader => 'partner_loan.repay_netting_trader',
            self::Factory => 'partner_loan.repay_netting_factory',
        };
    }

    /**
     * The account code used on the credit side of netting.
     */
    public function nettingAccountCode(): string
    {
        return match ($this) {
            self::Trader => '1140',   // Trader Receivables
            self::Factory => '2110',  // Factory Payables
        };
    }

    /**
     * Resolve enum case from a model class string.
     */
    public static function fromModelClass(string $class): self
    {
        return match ($class) {
            Trader::class => self::Trader,
            Factory::class => self::Factory,
            default => throw new \InvalidArgumentException("Unknown loanable model: {$class}"),
        };
    }
}

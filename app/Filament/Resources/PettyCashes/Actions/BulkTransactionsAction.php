<?php

namespace App\Filament\Resources\PettyCashes\Actions;

use App\Filament\Resources\PettyCashTransactions\Schemas\PettyCashTransactionForm;
use App\Models\PettyCash;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BulkTransactionsAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkTransactions';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إضافة معاملات متعددة')
            ->modalHeading('إضافة معاملات متعددة للعهدة')
            ->modalWidth('7xl')
            ->schema([
                Repeater::make('transactions')
                    ->label('المعاملات')
                    ->schema(fn (Schema $schema) => PettyCashTransactionForm::configure($schema->livewire($this->getLivewire())))
                    ->minItems(1)
                    ->reorderable()
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                /** @var PettyCash $pettyCash */
                $pettyCash = $this->getLivewire()->getOwnerRecord();
                $lastDate = null;

                foreach ($data['transactions'] ?? [] as $transactionData) {
                    $pettyCash->transactions()->create([
                        'petty_cash_id' => $pettyCash->id,
                        'farm_id' => $transactionData['farm_id'],
                        'batch_id' => $transactionData['batch_id'] ?? null,
                        'direction' => $transactionData['direction'],
                        'expense_category_id' => $transactionData['expense_category_id'] ?? null,
                        'employee_id' => $transactionData['employee_id'] ?? null,
                        'date' => $transactionData['date'] ?? now()->toDateString(),
                        'amount' => $transactionData['amount'],
                        'description' => $transactionData['description'],
                        'recorded_by' => Auth::id(),
                    ]);
                    $lastDate = $transactionData['date'];
                }

                if ($lastDate) {
                    Cache::put('user_'.auth('web')->id().'_last_petty_cash_date', $lastDate, now()->addDays(1));
                }
            });
    }
}

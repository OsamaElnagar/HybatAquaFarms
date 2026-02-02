<?php

namespace App\Filament\Resources\HarvestOperations\Pages;

use App\Filament\Resources\HarvestOperations\HarvestOperationResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\HarvestOperation;
use App\Models\Trader;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewHarvestOperation extends ViewRecord
{
    protected static string $resource = HarvestOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sell_harvest')
                ->label('إصدار فاتورة بيع')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn(HarvestOperation $record) => $record->orders()->whereDoesntHave('salesOrders')->exists())
                ->form([
                    Select::make('trader_id')
                        ->label('التاجر')
                        ->options(
                            fn(HarvestOperation $record) => Trader::whereIn('id', $record->orders()->whereDoesntHave('salesOrders')->pluck('trader_id'))
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('date')
                        ->label('تاريخ الفاتورة')
                        ->displayFormat('Y-m-d')
                        ->native(false)
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data, HarvestOperation $record) {
                    $trader = Trader::findOrFail($data['trader_id']);
                    $date = \Illuminate\Support\Carbon::parse($data['date']);

                    $orders = $record->orders()
                        ->where('trader_id', $trader->id)
                        ->whereDoesntHave('salesOrders')
                        ->get();

                    if ($orders->isEmpty()) {
                        Notification::make()
                            ->title('لا توجد طلبات غير مفوترة لهذا التاجر')
                            ->warning()
                            ->send();

                        return;
                    }

                    $salesOrder = app(\App\Actions\Sales\CreateSalesOrderFromOrders::class)->execute(
                        harvestOperation: $record,
                        trader: $trader,
                        date: $date,
                        orders: $orders,
                        notes: "تم إنشاؤه من عملية الحصاد رقم: {$record->operation_number}"
                    );

                    Notification::make()
                        ->title('تم إنشاء فاتورة البيع بنجاح')
                        ->success()
                        ->send();

                    return redirect()->to(SalesOrderResource::getUrl('edit', ['record' => $salesOrder]));
                }),
            EditAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}

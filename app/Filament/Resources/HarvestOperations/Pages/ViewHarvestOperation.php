<?php

namespace App\Filament\Resources\HarvestOperations\Pages;

use App\Filament\Resources\HarvestOperations\HarvestOperationResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\HarvestOperation;
use App\Models\SalesOrder;
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
                ->label('بيع الحصاد')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn (HarvestOperation $record) => $record->harvestBoxes()->where('is_sold', false)->exists())
                ->form([
                    Select::make('trader_id')
                        ->label('التاجر')
                        ->options(Trader::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    DatePicker::make('date')
                        ->label('تاريخ البيع')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data, HarvestOperation $record) {
                    // Create Sales Order
                    $salesOrder = SalesOrder::create([
                        'farm_id' => $record->farm_id,
                        'trader_id' => $data['trader_id'],
                        'date' => $data['date'],
                        'created_by' => auth()->id(),
                        'notes' => "تم إنشاؤه من عملية الحصاد رقم: {$record->operation_number}",
                    ]);

                    // Link unsold boxes
                    $boxes = $record->harvestBoxes()->where('is_sold', false)->get();
                    foreach ($boxes as $box) {
                        $box->update([
                            'sales_order_id' => $salesOrder->id,
                            'trader_id' => $salesOrder->trader_id,
                            'is_sold' => true,
                            // Set default sold_at to order date? HarvestBox updates this automatically on 'is_sold' change to now(), but maybe we want the order date?
                            // For now rely on HarvestBox observer logic or explicit if needed.
                            'sold_at' => $data['date'], 
                        ]);
                    }

                    $salesOrder->recalculateTotals();

                    Notification::make()
                        ->title('تم إنشاء أمر البيع بنجاح')
                        ->success()
                        ->send();

                    return redirect()->to(SalesOrderResource::getUrl('edit', ['record' => $salesOrder]));
                }),
            EditAction::make(),
        ];
    }
}

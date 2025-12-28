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
                    $trader = Trader::findOrFail($data['trader_id']);
                    $date = \Illuminate\Support\Carbon::parse($data['date']);
                    $boxes = $record->harvestBoxes()->where('is_sold', false)->get();

                    if ($boxes->isEmpty()) {
                        Notification::make()
                            ->title('لا يوجد صناديق متاحة للبيع')
                            ->warning()
                            ->send();
                        return;
                    }

                    $salesOrder = app(\App\Actions\Sales\CreateSalesOrderFromBoxes::class)->execute(
                        farm: $record->farm,
                        trader: $trader,
                        date: $date,
                        boxes: $boxes,
                        notes: "تم إنشاؤه من عملية الحصاد رقم: {$record->operation_number}"
                    );

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

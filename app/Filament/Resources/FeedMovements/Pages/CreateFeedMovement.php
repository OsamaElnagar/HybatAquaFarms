<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Enums\FeedMovementType;
use App\Filament\Resources\FeedMovements\FeedMovementResource;
use App\Models\FeedStock;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedMovement extends CreateRecord
{
    protected static string $resource = FeedMovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        if ($data['movement_type'] !== FeedMovementType::Sale) {
            return;
        }

        if (empty($data['from_warehouse_id'])) {
            Notification::make()
                ->warning()
                ->title('خطأ في البيانات')
                ->body('يرجى اختيار المستودع المصدر للبيع.')
                ->send();

            $this->halt();
        }

        $stock = FeedStock::where('feed_warehouse_id', $data['from_warehouse_id'])
            ->where('feed_item_id', $data['feed_item_id'])
            ->first();

        $availableQuantity = $stock ? (float) $stock->quantity_in_stock : 0;
        $requestedQuantity = (float) $data['quantity'];

        if ($availableQuantity < $requestedQuantity) {
            Notification::make()
                ->warning()
                ->title('كمية غير كافية')
                ->body("الكمية المتوفرة في المستودع: {$availableQuantity} كجم\nالكمية المطلوبة: {$requestedQuantity} كجم")
                ->send();

            $this->halt();
        }
    }
}

<?php

namespace App\Traits;

use App\Models\Batch;

trait ProtectsClosedBatch
{
    protected static function bootProtectsClosedBatch()
    {
        static::creating(function ($model) {
            if ($model->batch_id) {
                $batch = Batch::find($model->batch_id);
                if ($batch && $batch->is_cycle_closed) {
                    throw new \Exception('لا يمكن إضافة سجل ليتبع دورة مقفلة');
                }
            }
        });

        static::updating(function ($model) {
            // If the original batch was closed, don't allow modifying this record at all
            if ($model->getOriginal('batch_id')) {
                $originalBatch = Batch::find($model->getOriginal('batch_id'));
                if ($originalBatch && $originalBatch->is_cycle_closed) {
                    throw new \Exception('لا يمكن تعديل سجل يتبع بالفعل لدورة مقفلة');
                }
            }

            // If the user is trying to move the record to a new batch that is closed
            if ($model->isDirty('batch_id') && $model->batch_id) {
                $newBatch = Batch::find($model->batch_id);
                if ($newBatch && $newBatch->is_cycle_closed) {
                    throw new \Exception('لا يمكن نقل السجل ليتبع دورة مقفلة');
                }
            }
        });

        static::deleting(function ($model) {
            if ($model->batch_id) {
                $batch = Batch::find($model->batch_id);
                if ($batch && $batch->is_cycle_closed) {
                    throw new \Exception('لا يمكن حذف سجل يتبع دورة مقفلة');
                }
            }
        });
    }
}

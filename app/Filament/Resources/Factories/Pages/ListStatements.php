<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Filament\Resources\Factories\FactoryResource;
use App\Models\FactoryStatement;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ListStatements extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = FactoryResource::class;

    protected string $view = 'filament.resources.factories.pages.list-statements';

    protected static ?string $title = 'سجل كشوفات الحساب';

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            '#' => static::$title,
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FactoryStatement::query()
                    ->where('factory_id', $this->record->id)
                    ->latest('opened_at')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->placeholder('بدون عنوان')
                    ->searchable(),
                TextColumn::make('opened_at')
                    ->label('تاريخ الفتح')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('تاريخ الإغلاق')
                    ->date('Y-m-d')
                    ->placeholder('لا يزال مفتوحاً'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('opening_balance')
                    ->label('رصيد افتتاحي')
                    ->money('EGP', locale: 'en'),
                TextColumn::make('closing_balance')
                    ->label('رصيد إغلاق')
                    ->money('EGP', locale: 'en')
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('viewStatement')
                    ->label('عرض الكشف')
                    ->icon('heroicon-o-eye')
                    ->url(fn (FactoryStatement $record) => FactoryResource::getUrl('statement', [
                        'record' => $this->record->id,
                        'statement_id' => $record->id,
                    ])),
            ]);
    }
}

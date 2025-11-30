<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\AdvanceStatus;
use App\Filament\Resources\EmployeeAdvances\Schemas\EmployeeAdvanceForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdvancesRelationManager extends RelationManager
{
    protected static string $relationship = 'advances';

    protected static ?string $title = 'سُلف الموظف';

    public function form(Schema $schema): Schema
    {
        return EmployeeAdvanceForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('advance_number')
            ->columns([
                TextColumn::make('advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('المبلغ')
                    ->numeric()
                    ->suffix(' EGP ')
                    ->sortable(),
                TextColumn::make('balance_remaining')
                    ->label('المتبقي')
                    ->numeric()
                    ->suffix(' EGP ')
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('installments_count')
                    ->label('الأقساط')
                    ->numeric()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(AdvanceStatus::class)
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة سلفة')
                    ->mutateDataUsing(function (array $data): array {
                        $data['employee_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('request_date', 'desc');
    }
}

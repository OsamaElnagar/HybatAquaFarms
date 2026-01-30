<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'موظفين و عمال';

    public function form(Schema $schema): Schema
    {
        return EmployeeResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('employee_number')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('phone2')
                    ->searchable(),
                TextColumn::make('national_id')
                    ->searchable(),
                TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('termination_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة موظف'),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

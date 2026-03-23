<?php

use App\Filament\Tables\Filters\DateRangeFilter;
use App\Models\EggSale;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

test('date range filter returns a Filament Filter instance', function () {
    $filter = DateRangeFilter::make('sale_date');

    expect($filter)->toBeInstanceOf(Filter::class);
    expect($filter->getName())->toBe('sale_date');
});

test('date range filter has correct internal schema', function () {
    $filter = DateRangeFilter::make('sale_date');
    $schema = $filter->getFormSchema();

    expect($schema)->toHaveCount(2);
    expect($schema[0]->getName())->toBe('date_from');
    expect($schema[1]->getName())->toBe('date_to');
});

test('date range filter applies correct query constraints', function () {
    $filter = DateRangeFilter::make('sale_date');

    // We can't easily check toSql() because of how Filament applies queries,
    // but we can test the query closure logic directly if we want to be thorough.
    // However, a simpler way is to just call apply and verify the query builder state.

    $query = EggSale::query();
    $data = ['date_from' => '2023-01-01', 'date_to' => null];

    $filter->apply($query, $data);

    $sql = $query->toSql();
    expect($sql)->toContain('sale_date');
    expect($sql)->toContain('>=');
});

<?php

namespace App\Filament\Resources\Treasuries\Pages;

use App\Filament\Resources\Treasuries\TreasuryResource;
use Filament\Resources\Pages\Page;

class TreasuryDashboard extends Page
{
    protected static string $resource = TreasuryResource::class;

    protected string $view = 'filament.resources.treasuries.pages.treasury-dashboard';
}

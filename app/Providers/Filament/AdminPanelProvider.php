<?php

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use App\Filament\Widgets\SalesTrendChart;
use App\Filament\Widgets\TreasuryOverview;
use App\Filament\Widgets\TreasuryTransactions;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jacobtims\FilamentLogger\FilamentLoggerPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Nagi\FilamentAbyssTheme\FilamentAbyssThemePlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            // ->spa()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->unsavedChangesAlerts()
            ->favicon(asset('favicon.ico'))
            ->brandName(config('app.name'))
            ->brandLogoHeight('50px')
            ->brandLogo(asset('android-chrome-512x512.png'))
            ->colors([
                'primary' => Color::Blue,
            ])
            // ->sidebarCollapsibleOnDesktop()
            ->topNavigation()
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                // Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([

                TreasuryOverview::class,

                TreasuryTransactions::class,

                SalesTrendChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                FilamentLoggerPlugin::make(),
                FilamentSpatieLaravelBackupPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make()->navigationGroup('اعدادات النظام'),
                FilamentAbyssThemePlugin::make(),
                FilamentApexChartsPlugin::make(),
                GlobalSearchModalPlugin::make(),
                FilamentLogViewer::make()->authorize(fn () => auth('web')->check())
                    ->navigationGroup('اعدادات النظام')
                    ->navigationIcon('heroicon-o-document-text')
                    ->navigationLabel('Log Viewer')
                    ->navigationSort(10)
                    ->navigationUrl('/logs')
                    ->pollingTime(null),
            ])
            ->plugin(
                AuthDesignerPlugin::make()
                    ->login(
                        fn (AuthPageConfig $config) => $config
                            ->media(asset('assets/images/pexel-waterfall.mp4'))
                            ->mediaPosition(MediaPosition::Cover)
                            ->blur(0)
                    )
            )
            ->databaseNotifications()
            ->databaseNotificationspolling('10s');
    }
}

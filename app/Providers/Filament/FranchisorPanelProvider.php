<?php

namespace App\Providers\Filament;

use App\Filament\Franchisor\Widgets\FranchisorOverviewStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class FranchisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('franchisor')
            ->path('franchisor')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => [
                    50 => '#FDF4E4',
                    100 => '#FBE7C4',
                    200 => '#FBE7C4',
                    300 => '#F3CD8B',
                    400 => '#F3CD8B',
                    500 => '#EBB45A',
                    600 => '#D89A34',
                    700 => '#B37B22',
                    800 => '#B37B22',
                    900 => '#12223C',
                    950 => '#0A1526',
                ],
                'danger' => Color::hex('#C0392B'),
                'success' => Color::hex('#1E8A5F'),
                'warning' => Color::hex('#EBB45A'),
                'info' => Color::hex('#2C6FAD'),
                'navy' => [
                    50 => '#EAEDF2',
                    100 => '#D2D8E3',
                    200 => '#A6B0C4',
                    300 => '#A6B0C4',
                    400 => '#4C5C7A',
                    500 => '#4C5C7A',
                    600 => '#1A2E4F',
                    700 => '#1A2E4F',
                    800 => '#12223C',
                    900 => '#12223C',
                    950 => '#0A1526',
                ],
            ])
            ->brandName('Daniyal Group of Colleges')
            ->brandLogo(fn () => new HtmlString(view('components.sidebar-brand')->render()))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/branding/daniyal-group-of-colleges-logo.png'))
            ->viteTheme('resources/css/filament/admin.css')
            ->discoverResources(in: app_path('Filament/Franchisor/Resources'), for: 'App\\Filament\\Franchisor\\Resources')
            ->discoverPages(in: app_path('Filament/Franchisor/Pages'), for: 'App\\Filament\\Franchisor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Franchisor/Widgets'), for: 'App\\Filament\\Franchisor\\Widgets')
            ->widgets([
                AccountWidget::class,
                FranchisorOverviewStats::class,
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
            ]);
    }
}

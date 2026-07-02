<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Resources\AcademicSessionResource;
use App\Filament\Resources\CampusResource;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\ExpenseCategoryResource;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\FranchisorResource;

use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\FinancialSummaryWidget;
use App\Filament\Widgets\CampusFinancialOverviewWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin')
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
            ->brandName('DCHS Super Admin')
            ->brandLogo(asset('images/dchs-logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('favicon.ico'))
            ->viteTheme('resources/css/filament/admin.css')
            ->resources([
                AcademicSessionResource::class,
                CampusResource::class,
                CourseResource::class,
                FranchisorResource::class,
                ExpenseCategoryResource::class,
                ExpenseResource::class,
                UserResource::class,
                SettingResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                OverviewStats::class,
                FinancialSummaryWidget::class,
                CampusFinancialOverviewWidget::class,
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
            ])
            ->navigationGroups([
                NavigationGroup::make('Academic Management')->collapsed(false),
                NavigationGroup::make('Financial Management')->collapsed(false),
                NavigationGroup::make('Administration')->collapsed(false),
            ])
            ->maxContentWidth('7xl');
    }
}

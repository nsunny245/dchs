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
use App\Filament\Resources\VisitorQueryResource;
use App\Filament\Resources\AdmissionResource;
use App\Filament\Resources\StudentResource;
use App\Filament\Resources\StaffResource;
use App\Filament\Resources\TimetableResource;
use App\Filament\Resources\ExamResource;
use App\Filament\Resources\MarkResource;
use App\Filament\Resources\FeeStructureResource;
use App\Filament\Resources\FeePaymentResource;
use App\Filament\Resources\ExpenseResource;

class CampusPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('campus')
            ->path('campus')
            ->login()
            ->authGuard('campus')
            ->colors([
                'primary' => '#0f766e', // Teal theme for Campus panel
            ])
            ->brandName('DCHS Campus Portal')
            ->brandLogo(asset('images/dchs-logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/dchs-logo.png'))
            ->assets([
                \Filament\Support\Assets\Css::make('custom-admin-theme', '/css/custom-admin.css'),
            ])
            ->resources([
                AcademicSessionResource::class,
                VisitorQueryResource::class,
                AdmissionResource::class,
                StudentResource::class,
                StaffResource::class,
                TimetableResource::class,
                ExamResource::class,
                MarkResource::class,
                FeeStructureResource::class,
                FeePaymentResource::class,
                ExpenseResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
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
                NavigationGroup::make('Student Relations')->collapsed(false),
                NavigationGroup::make('Academic Management')->collapsed(false),
                NavigationGroup::make('Financial Management')->collapsed(false),
                NavigationGroup::make('Administration')->collapsed(false),
            ])
            ->maxContentWidth('7xl');
    }
}

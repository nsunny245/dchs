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
use App\Filament\Resources\AdmissionInquiryResource;

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
            ->brandName('DCHS Campus Portal')
            ->brandLogo(asset('images/dchs-logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('favicon.ico'))
            ->viteTheme('resources/css/filament/admin.css')
            ->resources([
                AcademicSessionResource::class,
                VisitorQueryResource::class,
                AdmissionInquiryResource::class,
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

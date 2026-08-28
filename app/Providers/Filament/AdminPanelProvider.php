<?php

namespace App\Providers\Filament;

use App\Filament\Pages\FranchiseReport;
use App\Filament\Resources\AcademicSessionResource;
use App\Filament\Resources\AdmissionInquiryResource;
use App\Filament\Resources\AdmissionResource;
use App\Filament\Resources\AttendanceResource;
use App\Filament\Resources\CampusResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Filament\Resources\CourseResource;
use App\Filament\Resources\ExpenseCategoryResource;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\FeeCollectionResource;
use App\Filament\Resources\FeeHeadResource;
use App\Filament\Resources\FeeStructureResource;
use App\Filament\Resources\FeeVoucherResource;
use App\Filament\Resources\FranchisorResource;
use App\Filament\Resources\LeaveRequestResource;
use App\Filament\Resources\SettingResource;
use App\Filament\Resources\StaffResource;
use App\Filament\Resources\StudentResource;
use App\Filament\Resources\SuperAdminFranchisorPaymentResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\VisitorQueryResource;
use App\Filament\Widgets\CampusFinancialOverviewWidget;
use App\Filament\Widgets\CampusQuickAccessWidget;
use App\Filament\Widgets\FinancialSummaryWidget;
use App\Filament\Widgets\OverviewStats;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->brandName('Daniyal Group of Colleges')
            ->brandLogo(fn () => new HtmlString(view('components.sidebar-brand')->render()))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/branding/daniyal-group-of-colleges-logo.png'))
            ->viteTheme('resources/css/filament/admin.css')
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('components.topbar-notification'))
            ->renderHook(PanelsRenderHook::USER_MENU_AFTER, fn () => view('components.topbar-user-copy'))
            ->resources([
                AdmissionResource::class,
                StudentResource::class,
                AttendanceResource::class,
                StaffResource::class,
                LeaveRequestResource::class,
                VisitorQueryResource::class,
                AdmissionInquiryResource::class,
                ContactSubmissionResource::class,
                FeeStructureResource::class,
                FeeCollectionResource::class,
                FeeHeadResource::class,
                FeeVoucherResource::class,
                ExpenseCategoryResource::class,
                ExpenseResource::class,
                SuperAdminFranchisorPaymentResource::class,
                FranchisorResource::class,
                AcademicSessionResource::class,
                CampusResource::class,
                CourseResource::class,
                UserResource::class,
                SettingResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
                FranchiseReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                OverviewStats::class,
                CampusQuickAccessWidget::class,
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
                NavigationGroup::make('Student Relations')->collapsed(false),
                NavigationGroup::make('Finance')->collapsed(false),
                NavigationGroup::make('Academic Management')->collapsed(false),
                NavigationGroup::make('Administration')->collapsed(false),
            ])
            ->maxContentWidth('7xl');
    }
}

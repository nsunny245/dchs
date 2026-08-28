<?php

namespace App\Filament\Widgets;

use App\Models\Campus;
use Filament\Widgets\Widget;

class CampusQuickAccessWidget extends Widget
{
    protected static string $view = 'filament.widgets.campus-quick-access-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = filament()->auth()->user();

        return filament()->getCurrentPanel()?->getId() === 'admin'
            && $user?->hasRole('Super Admin');
    }

    protected function getViewData(): array
    {
        return [
            'campuses' => Campus::query()
                ->where('is_active', true)
                ->with('principals')
                ->orderBy('name')
                ->get(),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LicenseResource;
use App\Filament\Resources\PackageResource;
use App\Models\License;
use App\Models\Version;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;

class NumbersWidget extends StatsOverviewWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getCards(): array
    {
        $team = Filament::getTenant();

        $packageCount = $team->packages()->count();

        $licensesCount = License::query()
            ->whereRelation('package', 'team_id', $team->id)
            ->count();

        $versionsCount = Version::query()
            ->whereRelation('package', 'team_id', $team->id)
            ->count();

        $storageSize = Version::query()
            ->whereRelation('package', 'team_id', $team->id)
            ->sum('size');

        return [
            StatsOverviewWidget\Card::make('Packages', $packageCount)
                ->icon('heroicon-o-code-bracket-square')
                ->url(PackageResource::getUrl()),
            StatsOverviewWidget\Card::make('Licenses', $licensesCount)
                ->icon('heroicon-o-key')
                ->url(LicenseResource::getUrl()),
            StatsOverviewWidget\Card::make('Versions', $versionsCount)
                ->icon('heroicon-o-rocket-launch'),
            StatsOverviewWidget\Card::make('Storage', formatFilesize($storageSize)),
        ];
    }
}

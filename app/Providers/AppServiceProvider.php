<?php

namespace App\Providers;

use App\Models\License;
use App\Models\Package;
use App\Models\Team;
use App\Models\User;
use App\Models\Version;
use Illuminate\Support\ServiceProvider;
use Statview\Satellite\Statview;
use Statview\Satellite\Widgets\Widget;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Statview::registerWidgets([
            Widget::make('users')
                ->title('# users')
                ->value(User::count()),

            Widget::make('teams')
                ->title('# teams')
                ->value(Team::count()),

            Widget::make('packages')
                ->title('# packages')
                ->value(Package::count()),

            Widget::make('versions')
                ->title('# versions')
                ->value(Version::count()),

            Widget::make('licenses')
                ->title('# licenses')
                ->value(License::count()),
        ]);
    }
}

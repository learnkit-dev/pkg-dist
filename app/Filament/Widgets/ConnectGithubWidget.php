<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class ConnectGithubWidget extends Widget
{
    use InteractsWithActions;

    protected static string $view = 'filament.widgets.connect-github';

    public static function canView(): bool
    {
        return ! filled(Filament::getTenant()->gh_api_key);
    }

    public function connectGithubAction(): Action
    {
        return Action::make('connect_github')
            ->label('Connect Github')
            ->url(route('oauth.gh.redirect'));
    }
}

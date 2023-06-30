<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EditTeam extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.edit-team';

    protected static bool $shouldRegisterNavigation = false;

    public function mount()
    {
        $this->form->fill(Filament::getTenant()->toArray());
    }

    protected function getFormStatePath(): ?string
    {
        return 'team';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('General')
                ->inlineLabel()
                ->schema([
                    TextInput::make('name'),
                    TextInput::make('gh_api_key')
                        ->label('GitHub personal token')
                        ->password(),
                ]),
        ];
    }

    public function submitAction(): Action
    {
        return Action::make('submit')
            ->label('Save')
            ->action('submit');
    }

    public function submit()
    {
        Filament::getTenant()->update($this->form->getState());

        Notification::make()
            ->success()
            ->title('Team')
            ->body('Changes saved')
            ->send();
    }
}

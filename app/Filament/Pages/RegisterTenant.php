<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegisterTenant extends \Filament\Pages\Tenancy\RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register your team';
    }

    protected function getFormStatePath(): ?string
    {
        return 'team';
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->lazy()
                ->required()
                ->afterStateUpdated(function ($get, $set) {
                    $set('slug', Str::of($get('name'))->slug());
                }),
            TextInput::make('slug')
                ->unique('teams', 'slug')
                ->required(),
        ];
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        return [
            ...$data,
            'user_id' => auth()->id(),
        ];
    }

    protected function handleRegistration(array $data): Model
    {
        $team = parent::handleRegistration($data);

        $team->users()->attach(auth()->user());

        return $team;
    }
}

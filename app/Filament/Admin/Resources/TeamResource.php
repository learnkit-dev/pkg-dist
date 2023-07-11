<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TeamResource\Pages;
use App\Models\Team;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $slug = 'teams';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')
                    ->inlineLabel()
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('slug'),
                    ]),

                Section::make('Limits')
                    ->inlineLabel()
                    ->schema([
                        TextInput::make('limit_packages')
                            ->label('Packages'),
                        TextInput::make('limit_version_per_package')
                            ->label('Versions per package'),
                        TextInput::make('limit_licenses')
                            ->label('Licenses'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('versions_sum_size')
                    ->searchable()
                    ->sortable()
                    ->sum('versions', 'size')
                    ->formatStateUsing(fn ($state) => formatFilesize($state)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }
}

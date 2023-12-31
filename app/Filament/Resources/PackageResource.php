<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationGroup = 'Repository';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->inlineLabel()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->reactive()
                            ->afterStateUpdated(function ($get, $set) {
                                $set('slug', Str::of($get('name'))->slug());
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->unique('packages', 'slug', fn ($record) => $record)
                            ->required(),
                        Forms\Components\TextInput::make('package_name'),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->columns(4)
                    ->schema([
                        Section::make('General')
                            ->columnSpan(2)
                            ->columns()
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('slug'),
                                TextEntry::make('package_name'),
                            ]),

                        Section::make('Instructions')
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('add_repo')
                                    ->copyable()
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->getStateUsing(fn (?Package $record) => $record->getAddRepositoryCommand()),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('package_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('versions_count')
                    ->label('Versions')
                    ->sortable()
                    ->counts('versions'),
                Tables\Columns\TextColumn::make('licenses_count')
                    ->label('Licenses')
                    ->sortable()
                    ->counts('licenses'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VersionsRelationManager::class,
            RelationManagers\LicensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'view' => Pages\ViewPackage::route('/{record}'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $team = Filament::getTenant();

        if (! filled($team->limit_packages)) {
            return true;
        }

        return $team->packages()->count() < $team->limit_packages;
    }
}

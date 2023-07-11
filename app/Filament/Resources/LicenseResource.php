<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Filament\Resources\LicenseResource\RelationManagers;
use App\Models\License;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 500;

    protected static ?string $navigationGroup = 'Repository';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General')
                    ->columns()
                    ->schema([
                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->relationship('package', 'name', function ($query) {
                                return $query->whereBelongsTo(Filament::getTenant());
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('key')
                            ->disabled()
                            ->label('Key'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->username ?? 'Not used yet';
                    }),

                Tables\Columns\TextColumn::make('key')
                    ->sortable(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function scopeEloquentQueryToTenant(Builder $query, Model $tenant): Builder
    {
        return $query
            ->whereHas('package', function ($query) {
                return $query->whereBelongsTo(Filament::getTenant());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'view' => Pages\ViewLicense::route('/{record}'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}

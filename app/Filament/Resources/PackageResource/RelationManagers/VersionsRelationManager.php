<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use App\Filament\Actions\SelectVersion;
use App\Jobs\DownloadReleaseForRepoJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $recordTitleAttribute = 'version';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_version')
                    ->label('Add version')
                    ->modalWidth('md')
                    ->form([
                        SelectVersion::make('tag')
                            ->label('Tag'),
                    ])
                    ->action(function ($data) {
                        dispatch(new DownloadReleaseForRepoJob($this->getOwnerRecord(), $data['tag']));

                        Notification::make()
                            ->success()
                            ->title('Version')
                            ->body('Added version to your private composer repository.')
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

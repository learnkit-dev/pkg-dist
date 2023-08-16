<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use App\Enums\VersionStatus;
//use App\Filament\Actions\SelectBranch;
use App\Filament\Actions\SelectVersion;
use App\Filament\Resources\PackageResource\Pages\ViewPackage;
use App\Jobs\DownloadReleaseForRepoJob;
use Composer\Semver\VersionParser;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\RateLimiter;

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
            ->poll()
            ->columns([
                Tables\Columns\TextColumn::make('version'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => formatFilesize($state)),
                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Last sync')
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label('Imported')
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_version')
                    ->label('Import version')
                    ->modalWidth('md')
                    ->visible(function () {
                        $team = Filament::getTenant();

                        if (! filled($team->limit_version_per_package)) {
                            return true;
                        }

                        return $this->ownerRecord->versions()->count() < $team->limit_version_per_package;
                    })
                    ->form([
                        SelectVersion::make('tag')
                            ->required()
                            ->label('Tag'),
                    ])
                    ->action(function ($data) {
                        /*if ($data['type'] === 'branch') {
                            $tag = (new VersionParser)->normalizeBranch($data['branch']);
                        }*/

                        $tag = (new VersionParser)->normalize($data['tag']);

                        // Create new version for the package and start the download job
                        $version = $this->getOwnerRecord()->versions()->create([
                            'version' => $data['tag'],
                            'version_normalized' => $tag,
                        ]);

                        dispatch(new DownloadReleaseForRepoJob($this->getOwnerRecord(), $version));

                        Notification::make()
                            ->success()
                            ->title('Version')
                            ->body('Added version to your private composer repository.')
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('sync')
                    ->color('primary')
                    ->label('Sync')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->visible(function ($livewire) {
                        return $livewire->pageClass === ViewPackage::class;
                    })
                    ->action(function ($record) {
                        $executed = RateLimiter::attempt(
                            'resync-' . $record->id,
                            $perMinute = 1,
                            function () use ($record) {
                                $record->update([
                                    'status' => VersionStatus::Syncing,
                                ]);

                                dispatch(new DownloadReleaseForRepoJob($record->package, $record));

                                Notification::make()
                                    ->success()
                                    ->title('Version')
                                    ->body('Sync process successfully queued')
                                    ->send();
                            },
                        );

                        if (! $executed) {
                            Notification::make()
                                ->danger()
                                ->title('Version')
                                ->body('Too many attempts')
                                ->send();
                        }
                    }),
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

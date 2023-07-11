<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use App\Enums\ExtendPeriod;
use App\Filament\Resources\PackageResource\Pages\ViewPackage;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LicensesRelationManager extends RelationManager
{
    protected static string $relationship = 'licenses';

    protected static ?string $recordTitleAttribute = 'username';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2)
                    ->disabled()
                    ->visibleOn('edit'),

                Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->columnSpan(2),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires at')
                    ->columnSpan(2),

                Forms\Components\Toggle::make('is_revoked')
                    ->label('Revoked')
                    ->columnSpan(2)
                    ->helperText('You can pause the license by revoking it.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('username')
                    ->getStateUsing(function ($record) {
                        return $record->username ?? 'Not used yet';
                    }),
                Tables\Columns\IconColumn::make('is_revoked')
                    ->label('Revoked')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth('md'),
            ])
            ->actions([
                Action::make('extend')
                    ->label('Extend')
                    ->modalWidth('sm')
                    ->color('secondary')
                    ->icon('heroicon-m-calendar')
                    ->modalDescription('Extending the license is easy. Just fill out the following form.')
                    ->form([
                        Forms\Components\Radio::make('period')
                            ->label(false)
                            ->reactive()
                            ->required()
                            ->options(ExtendPeriod::getLabels()),

                        Forms\Components\DateTimePicker::make('custom_date')
                            ->label('Expires at')
                            ->visible(fn ($get) => $get('type') === 'custom'),
                    ])
                    ->action(function ($data, $record) {
                        if ($data['period'] === ExtendPeriod::Custom->value) {
                            $date = $data['custom_date'];
                        } else {
                            $expiresAt = $record->expires_at;

                            $date = $expiresAt->addMonths(ExtendPeriod::getMonths($data['period']));
                        }

                        $record->update([
                            'expires_at' => $date,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('License')
                            ->body('Extended the license.')
                            ->send();
                    }),
                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon('heroicon-m-stop')
                    ->requiresConfirmation()
                    ->visible(fn ($record, $livewire) => ! $record->is_revoked && $livewire->pageClass === ViewPackage::class)
                    ->action(fn ($record) => $record->update(['is_revoked' => true])),
                Action::make('activate')
                    ->label('Activate')
                    ->requiresConfirmation()
                    ->visible(fn ($record, $livewire) => $record->is_revoked  && $livewire->pageClass === ViewPackage::class)
                    ->action(fn ($record) => $record->update(['is_revoked' => false])),
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

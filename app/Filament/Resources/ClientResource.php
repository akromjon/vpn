<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\ConnectionsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\LogsRelationManager;
use Illuminate\Support\Str;
use Modules\Client\Models\Client;
use Modules\Client\Models\Enum\ClientMonetizationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Modules\Client\Models\Token;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationGroup = "Clients";

    protected static ?string $navigationLabel = 'Clients';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),

                Select::make("monetization_type")
                    ->options(ClientMonetizationType::class)
                    ->multiple()
                    ->label("Monetization Type"),

                TextInput::make('os_type')
                    ->label('OS Type')
                    ->required()
                    ->maxLength(20),

                TextInput::make('os_version')
                    ->label('OS Version')
                    ->required()
                    ->maxLength(20),

                TextInput::make('model')
                    ->label('Model')
                    ->required()
                    ->maxLength(50),

                TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(50),
                TextInput::make('last_used_at'),

                Fieldset::make('token')->label("Token")->relationship('token')->schema([
                    TextInput::make('token')->readOnly()->label('Value'),
                    DateTimePicker::make('created_at')->readOnly()->label('Created At'),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->formatStateUsing(function ($state, Client $client) {
                        return Str::limit($client->uuid, 10, '...');
                    })
                    ->copyable()
                    ->searchable(),

                TextColumn::make("monetization_type")->label("Mon Type")->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(fn (Client $client) => match ($client->status) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->badge()
                    ->searchable(),

                TextColumn::make("logs")
                    ->label('Logs')
                    ->formatStateUsing(function ($state, Client $client) {
                        return "L: " . $client->logs->count() . ' C: ' . $client->connections->count();
                    }),

                TextColumn::make("logs.city")->label('Location')
                    ->formatStateUsing(function ($state, Client $client) {
                        return $client->logs?->first()?->country_code . ", " . $client->logs?->first()?->city;
                    }),

                TextColumn::make('os_type')
                    ->label('OS Type')
                    ->searchable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->searchable()->dateTime(),
                TextColumn::make('last_used_at')
                    ->label('Last Used At')
                    ->searchable()->dateTime(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('last_used_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->before(function ($records) {
                        $records->each(function ($record) {
                            Token::removeCache($record->token->token); //
                        });
                    }),
                ]),
            ])->poll("10s");
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
            ConnectionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}

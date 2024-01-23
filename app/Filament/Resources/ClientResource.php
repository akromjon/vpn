<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Filament\Resources\ClientResource\RelationManagers\ConnectionsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\LogsRelationManager;
use App\Models\Client\Client;
use App\Models\Client\Enum\ClientModeType;
use App\Models\Token;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
                Select::make("mode_type")->label("Mode Type")->options(ClientModeType::class),
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
                    ->searchable(),

                TextColumn::make("mode_type")->label("Mode Type")->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(fn(Client $client) => match ($client->status) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->badge()
                    ->searchable(),

                TextColumn::make('logs_count')
                    ->counts('logs')
                    ->label('Total Logs'),

                TextColumn::make('connections_count')
                    ->counts('connections')
                    ->label('Total Connections'),

                TextColumn::make('os_type')
                    ->label('OS Type')
                    ->searchable(),

                TextColumn::make('os_version')
                    ->label('OS Version')
                    ->searchable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),

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
            ]);
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

<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Faker\Provider\ar_EG\Text;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ip_address')
                    ->required()
                    ->maxLength(255),
                TextInput::make('action')
                    ->required()
                    ->maxLength(255),
                TextInput::make('country_code')
                    ->required()
                    ->maxLength(255),
                TextInput::make('region_code')
                    ->required()
                    ->maxLength(255),
                TextInput::make('time_zone')
                    ->required()
                    ->maxLength(255),
                TextInput::make('city')
                    ->maxLength(255),

                TextInput::make('latitude')
                    ->maxLength(255),

                TextInput::make('longitude')
                    ->maxLength(255),

                TextInput::make('created_at')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                TextColumn::make('action'),
                TextColumn::make('ip_address'),
                TextColumn::make('country_code'),
                TextColumn::make('region_code'),
                TextColumn::make('time_zone'),
                TextColumn::make('city'),
                TextColumn::make('latitude'),
                TextColumn::make('longitude'),
                TextColumn::make('created_at'),
            ])
            ->filters([
            ])
            ->actions([
                ActionsDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

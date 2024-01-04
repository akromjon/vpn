<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'connections';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                TextInput::make("connected_at")
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
               TextColumn::make("id")->label("ID"),
               TextColumn::make("client.os_type")->label("OS Type"),
               TextColumn::make("pritunlUser.id")->label("PritunlUser ID"),
               TextColumn::make("pritunlUser.internal_user_id")->copyable()->label("Internal User ID"),
               IconColumn::make("pritunlUser.is_online")->boolean()->label("Online"),
               IconColumn::make("pritunlUser.disabled")->boolean()->label("Disabled"),
               TextColumn::make('status')->color(fn($record) => match ($record->status) {
                    'connected' => 'success',
                    'disconnected' => 'danger',
                   default => "gray",
               })->label('Connection Status')->badge(),
               TextColumn::make('connected_at')->label('Connected At')->dateTime(),
               TextColumn::make('disconnected_at')->label('Disconnected At')->dateTime(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

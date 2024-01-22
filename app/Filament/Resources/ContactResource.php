<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->autofocus()
                    ->required()
                    ->maxLength(255)
                    ->label('John Doe'),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->label('Subject'),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->label('Email'),
                Textarea::make('message')
                    ->required()
                    ->maxLength(5000)
                    ->label('Message'),
                TextInput::make('ip_address')
                    ->required()
                    ->ip()
                    ->label('IP Address'),
                Toggle::make('is_read')
                    ->label('Is Read'),
                Toggle::make('is_responded')
                    ->label('Is Responded'),
                Textarea::make('responded_by')
                    ->label('Responded By'),
                DatePicker::make('responded_at')
                    ->label('Responded At'),
                DatePicker::make('created_at')
                    ->disabled()
                    ->label('Created At'),
                DatePicker::make('updated_at')
                    ->disabled()
                    ->label('Updated At'),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('message')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_read')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_responded')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responded_by')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responded_at')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->date()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PritunlUserResource\Pages;
use App\Filament\Resources\PritunlUserResource\RelationManagers;
use App\Models\Pritunl\Enum\PritunlUserStatus;
use App\Models\Pritunl\Pritunl;
use App\Models\Pritunl\PritunlUser;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PritunlUserResource extends Resource
{
    protected static ?string $model = PritunlUser::class;

    protected static ?string $navigationGroup="VPN";

    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("pritunl_id")->label("Server")
                    ->relationship("pritunl","id")
                    ->getOptionLabelFromRecordUsing(function(Pritunl $record){
                        return "{$record->server->public_ip_address}-{$record->server->region}";
                    })
                    ->required(),
                TextInput::make("name")->default(fn()=>Str::random(6))->label("Name")->maxLength(50)->required(),
                Select::make("status")->options(PritunlUserStatus::class)->hiddenOn("create")->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("server_ip")->label("Server")->searchable()->sortable(),
                TextColumn::make("status")->badge()->label("Status")->searchable()->sortable(),
                TextColumn::make("name")->label("Name")->searchable()->sortable(),
                TextColumn::make("internal_user_id")->label("User ID")->searchable()->sortable(),
                IconColumn::make("is_online")->boolean()->label("Online"),
                IconColumn::make("disabled")->boolean()->label("Disabled"),
                // TextColumn::make("vpn_config_path")->label("VPN Config")->searchable()->sortable()->limit(10),
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
            'index' => Pages\ListPritunlUsers::route('/'),
            'create' => Pages\CreatePritunlUser::route('/create'),
            'edit' => Pages\EditPritunlUser::route('/{record}/edit'),
        ];
    }
}

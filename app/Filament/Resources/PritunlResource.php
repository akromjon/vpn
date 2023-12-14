<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PritunlResource\Pages;
use App\Jobs\Pritunl\RestartInternalServer;
use App\Models\Pritunl\Enum\InternalServerStatus;
use App\Models\Pritunl\Enum\PritunlStatus;
use App\Models\Pritunl\Pritunl;
use App\Models\Server\Enum\ServerStatus;
use App\Models\Server\Server;
use Faker\Provider\ar_EG\Text;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class PritunlResource extends Resource
{
    protected static ?string $model = Pritunl::class;

    protected static ?string $navigationGroup="Server";

    public static function getNavigationBadge(): ?string
    {
        return Pritunl::where("status",PritunlStatus::ACTIVE)->count(). "/". Pritunl::count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return "success";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("server_id")->label("Server")
                    ->relationship("server","public_ip_address")
                    ->getOptionLabelFromRecordUsing(fn (Server $record) => "{$record->public_ip_address}-{$record->region}")
                    ->required(),

                TextInput::make("user_limit")->required()->default(100)->label("User Limit")->numeric()->nullable(),

                TextInput::make("username")->default(function(){
                    return config("pritunl.username");
                })->label("Username")->maxLength(100)->required(),

                TextInput::make("password")->default(function(){
                    return config("pritunl.password");
                })->prefixIcon("heroicon-o-arrow-right-on-rectangle")->password()->label("Password")->maxLength(100)->required(),

                Select::make("status")->options(PritunlStatus::class)->label("Status")->hiddenOn("create")->nullable(),
                TextInput::make("user_count")->label("Total User")->hiddenOn("create")->numeric()->nullable(),
                TextInput::make("organization_id")->label("Organization ID")->hiddenOn("create")->maxLength(100)->nullable(),
                TextInput::make("internal_server_id")->label("Internal Server ID")->hiddenOn("create")->maxLength(100)->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("server.public_ip_address")->label("Server")->searchable()->sortable(),
                TextColumn::make('status')->searchable()->sortable()->badge(),
                TextColumn::make('internal_server_status')->label("Internal Status")->badge()->searchable()->sortable(),
                TextColumn::make('online_user_count')->label("Online")->searchable()->sortable(),
                TextColumn::make('user_limit')->label("Limit")->searchable()->sortable(),
                TextColumn::make('user_count')->label("Total Users")->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make("Restart")->icon('heroicon-o-arrow-path')->color("info")->requiresConfirmation("Are you sure you want to restart this Pritunl?")->action(function(Pritunl $pritunl){
                    RestartInternalServer::dispatch($pritunl);
                })->disabled(function(Pritunl $pritunl){
                    return $pritunl->internal_server_status !== InternalServerStatus::ONLINE;
                }),

                EditAction::make(),
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPritunls::route('/'),
            'create' => Pages\CreatePritunl::route('/create'),
            'edit' => Pages\EditPritunl::route('/{record}/edit'),
        ];
    }
}

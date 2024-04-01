<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PritunlResource\Pages;
use Modules\Server\Models\Server;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Modules\Pritunl\Jobs\CreateNumberOfUsers;
use Modules\Pritunl\Jobs\Deletion;
use Modules\Pritunl\Jobs\EnableReverseAction;
use Modules\Pritunl\Jobs\InternalServerOperation;
use Modules\Pritunl\Jobs\User\Synchronization;
use Modules\Pritunl\Models\Enum\InternalServerStatus;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\Pritunl;

class PritunlResource extends Resource
{
    protected static ?string $model = Pritunl::class;

    protected static ?string $navigationGroup = "VPN";

    protected static ?string $navigationLabel = 'Pritunl';


    public static function getNavigationBadge(): ?string
    {
        return Pritunl::where(function ($query) {
            $query = $query->where("status", PritunlStatus::ACTIVE);
            return $query->Where("internal_server_status", InternalServerStatus::ONLINE);
        })->count();
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
                    ->relationship("server", "ip")
                    ->getOptionLabelFromRecordUsing(fn(Server $record) => "{$record->ip}-{$record->name}")
                    ->required(),
                TextInput::make("port")->numeric()->nullable(),

                Toggle::make("reverse_action_enabled")->label("Reverse Enabled")->afterStateUpdated(function($record){

                })->default(false)->required(),
                TextInput::make("reverse_value")->label("reverse Value")->nullable(),

                TextInput::make("user_count")->default(25)->label("Total User")->required()->numeric()->nullable(),
                TextInput::make("online_user_count")->label("Online Users")->numeric()->nullable(),


                TextInput::make("username")->prefixIcon("heroicon-o-user")->default(function () {
                    return config("pritunl.username");
                })->label("Username")->maxLength(100)->required(),

                TextInput::make("password")->default(function () {
                    return config("pritunl.password");
                })->prefixIcon("heroicon-o-arrow-right-on-rectangle")->label("Password")->maxLength(100)->required(),

                Select::make("status")->options(PritunlStatus::class)->label("Status")->hiddenOn("create")->nullable(),
                TextInput::make("organization_id")->label("Organization ID")->hiddenOn("create")->maxLength(100)->nullable(),
                TextInput::make("internal_server_id")->label("Internal Server ID")->hiddenOn("create")->maxLength(100)->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("server.ip")->label("Server")->searchable()->sortable(),

                TextColumn::make("server.country")->label("Country")->searchable()->sortable(),

                TextColumn::make('status')->searchable()->sortable()->badge(),

                TextColumn::make('internal_server_status')->badge()->label("Internal Status")->searchable()->sortable(),

                TextColumn::make('sync_status')->label("Sync Status")->badge()->searchable()->sortable(),

                ToggleColumn::make('reverse_action_enabled')->label("Reverse Enabled")->afterStateUpdated(function($record){

                    if($record->reverse_action_enabled){
                        EnableReverseAction::dispatch($record,$record->server->ip,config("app.url"),config("app.pritunl.token"));
                    }

                })->searchable()->sortable(),

                TextColumn::make('online_user_count')->label("Online")->searchable()->sortable(),

                TextInputColumn::make('user_count')->disabled(function($record){
                    return $record->status != PritunlStatus::ACTIVE;
                })
                ->updateStateUsing(function ($record, $state) {

                    if ($state <= $record->user_count) {

                        return Notification::make()
                            ->title('You cannot decrease the number of users')
                            ->danger()
                            ->duration(5000)
                            ->send();

                    }

                    CreateNumberOfUsers::dispatch($record, $state-$record->user_count);

                })->label("Total Users")->searchable()->sortable(),
            ])

            ->filters([
                //
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Action::make("Sync Users")->icon("heroicon-o-arrow-path")->color("info")->requiresConfirmation("Are you sure you want to sync users?")->action(function (Pritunl $pritunl) {
                    Synchronization::dispatch($pritunl);
                })->disabled(function (Pritunl $pritunl) {
                    return $pritunl->internal_server_status != InternalServerStatus::ONLINE || $pritunl->sync_status == PritunlSyncStatus::SYNCING;
                }),
                Action::make("Start")->icon("heroicon-o-cursor-arrow-rays")->color("success")->requiresConfirmation("Are you sure you want to start this Pritunl?")->action(function (Pritunl $pritunl) {
                    InternalServerOperation::dispatch($pritunl, "start");
                })->disabled(function (Pritunl $pritunl) {
                    return $pritunl->internal_server_status != InternalServerStatus::OFFLINE;
                }),
                Action::make("Stop")->icon("heroicon-o-x-circle")->color("danger")->requiresConfirmation("Are you sure you want to stop this Pritunl?")->action(function (Pritunl $pritunl) {
                    InternalServerOperation::dispatch($pritunl, "stop");
                })->disabled(function (Pritunl $pritunl) {
                    return $pritunl->internal_server_status != InternalServerStatus::ONLINE;
                }),

                Action::make("Restart")->icon('heroicon-o-arrow-path')->color("info")->requiresConfirmation("Are you sure you want to restart this Pritunl?")->action(function (Pritunl $pritunl) {
                    InternalServerOperation::dispatch($pritunl, "restart");
                })->disabled(function (Pritunl $pritunl) {
                    return $pritunl->internal_server_status != InternalServerStatus::ONLINE;
                }),

                EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make("Delete")->action(function (Collection $records) {

                        $records->each(function (Pritunl $record) {

                            Deletion::dispatch($record);

                        });

                        Notification::make()
                            ->title('Pritunl will be deleted shortly')
                            ->success()
                            ->duration(5000)
                            ->send();

                        return redirect(PritunlResource::getUrl());

                    })->label("Delete Pritunl")->color("danger")->icon("heroicon-o-trash"),
                ]),
            ])->poll("5s");
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

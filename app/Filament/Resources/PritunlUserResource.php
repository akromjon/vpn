<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PritunlUserResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\IconColumn\IconColumnSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Modules\Pritunl\Jobs\User\DeletionPritunlUser;
use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Pritunl\Models\Pritunl as ModelsPritunl;
use Modules\Pritunl\Models\PritunlUser;

class PritunlUserResource extends Resource
{
    protected static ?string $model = PritunlUser::class;

    protected static ?string $navigationGroup = "VPN";

    protected static ?string $navigationLabel = 'Users';

    public static function getNavigationBadge(): ?string
    {
        return PritunlUser::where(function($query){
            $query= $query->where("status", PritunlUserStatus::ACTIVE)
                ->where("is_online", false);
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
                Select::make("pritunl_id")->label("Server")
                    ->relationship("pritunl", "id")
                    ->getOptionLabelFromRecordUsing(function (ModelsPritunl $record) {
                        return "{$record->server->ip}-{$record->server->region}";
                    })
                    ->required(),
                TextInput::make("name")->default(fn() => Str::random(6))->label("Name")->maxLength(50)->required(),
                TextInput::make("internal_user_id")->label("User ID")->maxLength(50),
                Select::make("status")->options(PritunlUserStatus::class)->hiddenOn("create")->required(),
                Toggle::make("disabled")->label("Enabled")->default(false)->required(),
                Toggle::make("is_online")->label("Online")->default(false)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("id")->label("ID")->searchable()->sortable(),
                TextColumn::make("server_ip")->label("Server")->searchable()->sortable(),
                TextColumn::make("pritunl.server.country")->label("Country")->searchable()->sortable(),
                TextColumn::make("status")->badge()->label("Status")->searchable()->sortable(),
                TextColumn::make("internal_user_id")->label("User ID")->searchable()->sortable(),
                ToggleColumn::make("is_online")->afterStateUpdated(function ($record, $state) {

                })->label("Online"),
                ToggleColumn::make("disabled")->action(function (PritunlUser $record) {

                }),
                IconColumn::make("vpn_config_path")->size(IconColumnSize::Large)->icon("heroicon-o-arrow-down-tray")->action(function (PritunlUser $record) {
                    return response()->download($record->vpn_config_path);
                })->color("warning")->label("VPN Config")->searchable()->sortable(),
            ])
            ->defaultSort('last_active', 'desc')
            ->filters([
                    //
                ])
            ->actions([
                    Tables\Actions\EditAction::make(),
                ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([

                        BulkAction::make("Delete")->action(function (Collection $records) {

                            $records->each(function (PritunlUser $record) {

                                if ($record->status != PritunlUserStatus::FAILED_TO_DELETE) {

                                    DeletionPritunlUser::dispatch($record);
                                }
                            });

                            Notification::make()
                                ->title('Pritunl Users will be deleted shortly')
                                ->success()
                                ->duration(5000)
                                ->send();
                            return redirect(PritunlUserResource::getUrl());

                        })->label("Delete Users")->color("danger")->icon("heroicon-o-trash"),
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

<?php

namespace App\Filament\Resources;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use App\Models\Server\Enum\CloudProviderType;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use App\Models\Server\Enum\ServerStatus;
use App\Jobs\Server\Deletion;
use App\Jobs\Server\Reboot;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use App\Filament\Resources\ServerResource\Pages;
use App\Jobs\Server\Creation;
use App\Models\Server\Server;
use Filament\Resources\Resource;
use Filament\Tables\Table;


class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationGroup="Servers";


    public static function getNavigationBadge(): ?string
    {
        return Server::where("status",ServerStatus::ACTIVE)->count(). "/". Server::count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return "success";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('uuid')->label('UUID')->maxLength(255)->hiddenOn("create"),
                Select::make('cloud_provider_type')->label("Cloud Provider Type")->options(CloudProviderType::class)->default(CloudProviderType::DigitalOcean)->required(),
                TextInput::make('name')->live(onBlur: true)->minLength(2)->required()->afterStateUpdated(function (Set $set, $state) {
                    return $set('name', Str::slug($state));
                }),
                Select::make('region')->options(self::regionOptions())->required(),
                Select::make('size')->options(self::sizeOptions())->required()->label("Size"),
                Select::make('image_id')->options(self::imageOptions())->required()->label("Image"),
                CheckboxList::make('ssh_key_ids')->options(self::sshKeyOptions())->required()->label("SSH Keys")->default(array_key_first(self::sshKeyOptions())),
                Select::make('project_id')->options(self::projectOptions())->required()->default(array_key_first(self::projectOptions()))->label("Project"),
                Select::make('status')->options(ServerStatus::class)->label("Status")->hiddenOn("create"),
                TextInput::make('public_ip_address')->ip()->readOnly()->hiddenOn("create")->maxLength(45),
                TextInput::make('private_ip_address')->ip()->readOnly()->hiddenOn("create")->maxLength(45),
                DateTimePicker::make('server_created_at')->hiddenOn("create"),
                TextInput::make('price')->numeric()->prefix('$')->hiddenOn("create"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('uuid')->label('UUID')->searchable(),
            TextColumn::make('status')->badge()->searchable(),
            TextColumn::make('public_ip_address')->label("Ip Address")->searchable()->copyable()->copyable()->copyMessage('IP Address copied')->copyMessageDuration(1500),
            TextColumn::make("size")->searchable(),
            SelectColumn::make('region')->options(self::regionOptions())->disabled(function ($record) {
                return $record->status !== ServerStatus::UNAVAILABLE;
            })->searchable()->afterStateUpdated(function ($record, $state) {
                if ($record->status === ServerStatus::UNAVAILABLE) {
                    Creation::dispatch($record);
                }
            }),
            TextColumn::make('server_created_at')->label("Creation")->dateTime()->sortable(),
            TextColumn::make('price')->money()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([
                //
            ])
            ->actions(self::actions())
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make("Delete")->action(function (Collection $records) {
                        $records->each(function ($record) {
                            Server::fireDeleteJob($record);
                        });
                    })->color("danger")->icon("heroicon-o-trash")->requiresConfirmation("Are you sure you want to delete the servers?")
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
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }

    protected static function sizeOptions(): array
    {
        $sizes = DigitalOceanClient::connect(config("digitalocean.token"))->sizes();

        return collect($sizes)
            ->mapWithKeys(fn($size) => [$size["slug"] => $size["slug"] . "-$" . $size["price_monthly"] . "/m"])
            ->toArray();
    }

    protected static function regionOptions(): array
    {
        $regions = DigitalOceanClient::connect(config("digitalocean.token"))->regions();

        return collect($regions)
            ->mapWithKeys(fn($region) => [$region["slug"] => $region["name"]])
            ->sort()
            ->toArray();
    }

    protected static function imageOptions(): array
    {
        $images = DigitalOceanClient::connect(config("digitalocean.token"))->snapshots();

        return collect($images)
            ->mapWithKeys(fn($image) => [$image["id"] => $image["name"]])
            ->sort()
            ->toArray();
    }

    protected static function sshKeyOptions(): array
    {
        $keys = DigitalOceanClient::connect(config("digitalocean.token"))->sshKeys();

        return collect($keys)
            ->mapWithKeys(fn($sshKey) => [$sshKey["id"] => $sshKey["name"]])
            ->sort()
            ->toArray();
    }

    protected static function projectOptions(): array
    {
        $projects = DigitalOceanClient::connect(config("digitalocean.token"))->projects();

        return collect($projects)
            ->mapWithKeys(fn($project) => [$project["id"] => $project["name"]])
            ->partition(function ($value, $key) {
                return str_contains($value, config("app.env"));
            })
            ->map(function ($group) {
                return $group->sort();
            })
            ->collapse()
            ->toArray();
    }

    protected static function fireDeleteJob(Server $server): void
    {
        if (ServerStatus::DELETING !== $server->status) {

            $server->status = ServerStatus::DELETING;

            $server->save();

            Deletion::dispatch($server);

            Notification::make()
                ->title('Server is being deleted and will be removed from the list shortly!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Server is already being deleted and will be removed from the list shortly!')
                ->warning()
                ->send();
        }
    }
    protected static function actions(): array
    {
        return [
            Action::make("Reboot")->color('gray')->icon('heroicon-o-power')
                ->requiresConfirmation("Are you sure you want to reboot the servers?")
                ->disabled(function ($record) {
                    return ServerStatus::ACTIVE !== $record->status;
                })->action(function (Server $server) {
                    if (ServerStatus::ACTIVE === $server->status) {
                        $server->status = ServerStatus::REBOOTING;
                        $server->save();
                        Reboot::dispatch($server->public_ip_address, 'root', $server);
                        Notification::make()
                            ->title('Server is being rebooted!')
                            ->success()
                            ->send();
                    }
                }),

            EditAction::make(),

            Action::make("Delete")->color("danger")->icon("heroicon-o-trash")
                ->requiresConfirmation("Are you sure you want to delete the server?")->action(function (Server $server) {
                    self::fireDeleteJob($server);
                })->disabled(function ($record) {
                    return ServerStatus::NEW === $record->status;
                }),


        ];
    }
}

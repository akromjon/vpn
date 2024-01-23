<?php

namespace App\Filament\Resources;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Modules\Server\Models\Enum\CloudProviderType;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Modules\Server\Models\Enum\ServerStatus;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use App\Filament\Resources\ServerResource\Pages;

use Modules\Server\Models\Server;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Get;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Columns\ImageColumn;
use Modules\Server\Jobs\Deletion;
use Modules\Server\Jobs\Reboot;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationGroup = "Servers";


    public static function getNavigationBadge(): ?string
    {
        return Server::where("status", ServerStatus::ACTIVE)->count();
    }

    private static function getCountryNames(): array
    {
        return collect(self::countries())
            ->mapWithKeys(fn($country) => [$country["name"] => $country["name"]])
            ->toArray();
    }

    private static function countries(): array
    {
        return json_decode(File::get(public_path("json/countries.json"), true), true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Server Configuration')
                    ->schema([
                        TextInput::make("uuid")->label("Uuid")->maxLength(255),
                        TextInput::make("name")->default(fn() => Str::random(6))->label("Name")->maxLength(255)->required(),
                        Select::make("provider")->label("Provider")->options(CloudProviderType::class)->live()->required(),
                        Select::make("status")->label("Status")->options(ServerStatus::class)->required(),
                        TextInput::make("ip")->label("IP")->maxLength(100),
                        Select::make("country")->options(self::getCountryNames())->label("Country")->afterStateUpdated(function (Set $set, $state) {
                            $code = Str::lower(collect(self::countries())->where("name", $state)->first()["code"]);
                            $set("country_code", $code);
                            $set('image', asset("flags/1x1/$code.svg"));
                            $set('flag', asset("flags/1x1/$code.svg"));
                        })
                            ->reactive()->required(),
                        TextInput::make("city")->label("City")->required(),
                        TextInput::make("country_code")->live()->label("Country Code")->minLength(2)->maxLength(3)->required(),
                        TextInput::make("flag")->live()->label("Flag")->required(),
                        ViewField::make("image")->view('filament.forms.components.image')->formatStateUsing(function (Get $get) {
                            return $get("flag");
                        })->live(),
                        TextInput::make("price")->label("Price"),
                    ])
                    ->live()
                    ->columns(3),

                Fieldset::make('DIGITALOCEAN')
                    ->hidden(function (Get $get) {
                        return "digitalocean" != $get("provider");
                    })
                    ->schema([
                        Select::make("config.project")->label("Project")->options(self::projectOptions())->required(),
                        Select::make("config.region")->label("Region")->options(self::regionOptions())->required(),
                        Select::make("config.size")->label("Size")->options(self::sizeOptions())->required(),
                        Select::make("config.image")->label("Image")->options(self::imageOptions())->required(),
                        Select::make("config.ssh_keys")->label("SSH Key")->options(self::sshKeyOptions())->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make("name")->label("Name")->searchable()->sortable(),
            TextColumn::make("price")->label("Price")->searchable()->sortable(),
            TextColumn::make("provider")->label("Provider")->searchable()->sortable(),
            TextColumn::make("status")->label("Status")->badge()->searchable()->sortable(),
            TextColumn::make("ip")->label("IP")->searchable()->sortable(),
            TextColumn::make("country")->label("Country")->searchable()->sortable(),
            TextColumn::make("city")->label("City")->searchable()->sortable(),
            ImageColumn::make("flag")->label("Flag")->circular()->searchable()->sortable(),

        ])
        ->filters([
            //
        ])
        ->defaultSort("created_at", "desc")
        ->actions(self::actions())
        ->bulkActions([
            BulkActionGroup::make([
                BulkAction::make("Delete")->action(function (Collection $records) {
                    $records->each(function ($record) {
                        self::fireDeleteJob($record);
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
                        Reboot::dispatch($server->ip, 'root', $server);
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

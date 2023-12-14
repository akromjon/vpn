<?php

namespace App\Models\Server;

use Akromjon\DigitalOceanClient\DigitalOceanClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use App\Enum\CloudProviderTypeEnum;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use App\Enum\ServerEnum;
use App\Events\CreateServerEvent;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Tables\Columns\SelectColumn;

class Server extends Model
{
    use HasFactory, Actions;
    protected $casts=[
        "server_created_at"=>"datetime",
        "status"=>ServerEnum::class,
        "cloud_provider_type"=>CloudProviderTypeEnum::class,
        "ssh_key_ids"=>"array",
    ];

    protected static function booted()
    {
        static::created(function (self $server) {
            CreateServerEvent::fire($server);
        });
        static::updated(function (self $server) {
            self::regionChanged($server);
        });
    }

    protected static function regionChanged(self $server):void
    {
        if($server->status===ServerEnum::UNAVAILABLE && array_key_exists("region",$server->getDirty())){
            CreateServerEvent::fire($server);
        }
    }

    protected static function sizeOptions():array
    {
        $sizes=DigitalOceanClient::connect(config("digitalocean.token"))->sizes();

        return collect($sizes)
            ->mapWithKeys(fn($size) => [$size["slug"] => $size["slug"]."--$".$size["price_monthly"]."/m"])
            ->toArray();
    }

    protected static function regionOptions():array
    {
        $regions=DigitalOceanClient::connect(config("digitalocean.token"))->regions();

        return collect($regions)
                ->mapWithKeys(fn($region) => [$region["slug"] => $region["name"]])
                ->sort()
                ->toArray();
    }

    protected static function imageOptions():array
    {
        $images=DigitalOceanClient::connect(config("digitalocean.token"))->snapshots();

        return collect($images)
                ->mapWithKeys(fn($image) => [$image["id"] => $image["name"]])
                ->sort()
                ->toArray();
    }

    protected static function sshKeyOptions():array
    {
        $keys=DigitalOceanClient::connect(config("digitalocean.token"))->sshKeys();

        return  collect($keys)
                ->mapWithKeys(fn($sshKey) => [$sshKey["id"] => $sshKey["name"]])
                ->sort()
                ->toArray();
    }

    protected static function projectOptions():array
    {
        $projects=DigitalOceanClient::connect(config("digitalocean.token"))->projects();

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

    public static function form(Form $form):Form
    {
        return $form
            ->schema([
                TextInput::make('uuid')->label('UUID')->maxLength(255)->hiddenOn("create"),
                Select::make('cloud_provider_type')->label("Cloud Provider Type")->options(CloudProviderTypeEnum::class)->default(CloudProviderTypeEnum::DigitalOcean)->required(),
                TextInput::make('name')->required(),
                Select::make('region')->options(self::regionOptions())->required(),
                Select::make('size')->options(self::sizeOptions())->required()->label("Size"),
                Select::make('image_id')->options(self::imageOptions())->required()->label("Image"),
                CheckboxList::make('ssh_key_ids')->options(self::sshKeyOptions())->required()->label("SSH Keys")->default(array_key_first(self::sshKeyOptions())),
                Select::make('project_id')->options(self::projectOptions())->required()->default(array_key_first(self::projectOptions()))->label("Project"),
                Select::make('status')->options(ServerEnum::class)->label("Status")->hiddenOn("create"),
                TextInput::make('public_ip_address')->ip()->readOnly()->hiddenOn("create")->maxLength(45),
                TextInput::make('private_ip_address')->ip()->readOnly()->hiddenOn("create")->maxLength(45),
                DateTimePicker::make('server_created_at')->hiddenOn("create"),
                TextInput::make('price')->numeric()->prefix('$')->hiddenOn("create"),
            ]);
    }

    public static function table(Table $table):Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')->label('UUID')->searchable(),
                TextColumn::make('status')->badge()->searchable(),
                TextColumn::make('public_ip_address')->label("Ip Address")->searchable()->copyable()->copyable()->copyMessage('IP Address copied')->copyMessageDuration(1500),
                TextColumn::make("size")->searchable(),
                SelectColumn::make('region')->options(self::regionOptions())->disabled(function($record){
                    return $record->status!==ServerEnum::UNAVAILABLE;
                })->searchable(),
                TextColumn::make('server_created_at')->label("Creation")->dateTime()->sortable(),
                TextColumn::make('price')->money()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

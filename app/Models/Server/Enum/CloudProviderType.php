<?php

namespace App\Models\Server\Enum;

use Filament\Support\Contracts\HasLabel;
enum CloudProviderType:string  implements HasLabel{
    case DIGITALOCEAN="digitalocean";
    case KAMATERA="kamatera";
    case MVPS="mvps";
    case OTHER="other";


    public function getLabel(): ?string
    {
        return match ($this) {
            self::DIGITALOCEAN => "DigitalOcean",
            self::KAMATERA => "Kamatera",
            self::OTHER => "Other",
            self::MVPS => "MVPS",
        };
    }
}

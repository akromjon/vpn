<?php

namespace App\Models\Server\Enum;

use Filament\Support\Contracts\HasLabel;
enum CloudProviderType:string  implements HasLabel{
    case DigitalOcean="digitalocean";
    case Kamatera="kamatera";
    case Hetzner="hetzner";
    case Other="other";


    public function getLabel(): ?string
    {
        return match ($this) {
            self::DigitalOcean => "Digital Ocean",
            self::Kamatera => "Kamatera",
            self::Hetzner => "Hetzner",
            self::Other => "Other",
        };
    }
}

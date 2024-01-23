<?php
namespace App\Models\Client\Enum;

use Filament\Support\Contracts\HasLabel;

enum ClientModeType: string implements HasLabel
{
    case FREE = "free";
    case ADS = "ads";
    case SUBSCRIPTION = "subscription";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FREE => "FREE",
            self::ADS => "ADS",
            self::SUBSCRIPTION => "SUBSCRIPTION"
        };
    }
}

<?php

namespace Modules\Client\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum ClientMonetizationType: string implements HasLabel
{
    case FREE = "free";
    case REWARDED="rewarded";
    case APP_OPEN="app_open";
    case INTERSTITIAL = "interstitial";
    case SUBSCRIPTION = "subscription";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FREE => "FREE",
            self::REWARDED => "REWARDED",
            self::APP_OPEN => "APP_OPEN",
            self::SUBSCRIPTION => "SUBSCRIPTION",
            self::INTERSTITIAL => "INTERSTITIAL",
        };
    }
}

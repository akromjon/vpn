<?php

namespace Modules\Client\Models\Enum;

use Filament\Support\Contracts\HasLabel;

enum ClientMonetizationType: string implements HasLabel
{
    case FREE = "free";
    case INTERACTIVE_ADS="interactive_ads";
    case VIDEO_ADS="video_ads";
    case SUBSCRIPTION = "subscription";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FREE => "FREE",
            self::INTERACTIVE_ADS => "INTERACTIVE_ADS",
            self::VIDEO_ADS => "VIDEO_ADS",
            self::SUBSCRIPTION => "SUBSCRIPTION"
        };
    }
}

<?php

namespace App\Models\Pritunl\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum InternalServerStatus:string implements HasLabel,HasColor {
    case ONLINE="online";
    case DELETED="deleted";
    case OFFLINE="offline";

    case RESTARTING="restarting";
    case FAILED_TO_RESTART="failed_to_restart";

    public function getLabel(): ?string{
        return match ($this) {
            self::ONLINE => "Online",
            self::OFFLINE => "Offline",
            self::DELETED => "Deleted",
            self::FAILED_TO_RESTART => "Failed to restart",
            self::RESTARTING => "Restarting",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ONLINE => "success",
            self::OFFLINE => "warning",
            self::DELETED => "danger",
            self::FAILED_TO_RESTART => "danger",
            self::RESTARTING => "warning",
        };
    }
}

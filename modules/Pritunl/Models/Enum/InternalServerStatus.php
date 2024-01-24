<?php

namespace Modules\Pritunl\Models\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum InternalServerStatus:string implements HasLabel,HasColor {
    case ONLINE="online";
    case STARTING="starting";
    case STOPPING="stopping";
    case STOPPED="stopped";
    case DELETING="deleting";
    case DELETED="deleted";
    case OFFLINE="offline";
    case RESTARTING="restarting";

    case FAILED_TO_DELETE="failed_to_delete";
    case FAILED_TO_START="failed_to_start";
    case FAILED_TO_STOP="failed_to_stop";
    case FAILED_TO_RESTART="failed_to_restart";

    public function getLabel(): ?string{
        return match ($this) {
            self::ONLINE => "Online",
            self::OFFLINE => "Offline",
            self::DELETED => "Deleted",
            self::FAILED_TO_RESTART => "Failed to restart",
            self::RESTARTING => "Restarting",
            self::STARTING => "Starting",
            self::STOPPING => "Stopping",
            self::STOPPED => "Stopped",
            self::FAILED_TO_START => "Failed to start",
            self::FAILED_TO_STOP => "Failed to stop",
            self::DELETING => "Deleting",
            self::FAILED_TO_DELETE => "Failed to delete",

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
            self::STARTING => "warning",
            self::STOPPING => "warning",
            self::STOPPED => "warning",
            self::FAILED_TO_START => "danger",
            self::FAILED_TO_STOP => "danger",
            self::DELETING => "warning",
            self::FAILED_TO_DELETE => "danger",
        };
    }
}

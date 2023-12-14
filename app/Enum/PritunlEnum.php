<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;


enum PritunlEnum : string implements HasLabel {
    case ACTIVE="active";
    case STARTED_INSTALLING="started_installing";
    case INSTALLED="installed";
    case FAILED_TO_INSTALL="failed_to_install";
    case INACTIVE="inactive";
    case FAILED="failed";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => "Active",
            self::STARTED_INSTALLING => "Started Installing",
            self::INSTALLED => "Installed",
            self::FAILED_TO_INSTALL => "Failed To Install",
            self::INACTIVE => "Inactive",
            self::FAILED => "Failed",
        };
    }

}

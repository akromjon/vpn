<?php

namespace App\Models\Pritunl\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum PritunlStatus:string implements HasLabel,HasColor {
    case ACTIVE="active";
    case CREATING="creating";
    case INACTIVE="inactive";
    case DELETED="deleted";
    case RESTARTING="restarting";
    case FAILED="failed";


    public function getLabel(): ?string{
        return match ($this) {
            self::ACTIVE => "Active",
            self::INACTIVE => "Inactive",
            self::DELETED => "Deleted",
            self::RESTARTING => "Restarting",
            self::FAILED => "Failed",
            self::CREATING => "Creating",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ACTIVE => "success",
            self::INACTIVE => "warning",
            self::DELETED => "danger",
            self::RESTARTING => "warning",
            self::FAILED => "danger",
            self::CREATING => "warning",
        };
    }
}

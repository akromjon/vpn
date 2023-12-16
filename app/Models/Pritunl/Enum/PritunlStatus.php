<?php

namespace App\Models\Pritunl\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum PritunlStatus:string implements HasLabel,HasColor {
    case ACTIVE="active";
    case CREATING="creating";
    case INACTIVE="inactive";
    case DELETING="deleting";
    case DELETED="deleted";
    case RESTARTING="restarting";
    case FAILED="failed";
    case FAILED_TO_CREATE="failed_to_create";
    case FAILED_TO_DELETE="failed_to_delete";
    case FAILED_TO_RESTART="failed_to_restart";



    public function getLabel(): ?string{
        return match ($this) {
            self::ACTIVE => "Active",
            self::INACTIVE => "Inactive",
            self::DELETED => "Deleted",
            self::RESTARTING => "Restarting",
            self::FAILED => "Failed",
            self::CREATING => "Creating",
            self::DELETING => "Deleting",
            self::FAILED_TO_CREATE => "Failed to create",
            self::FAILED_TO_DELETE => "Failed to delete",
            self::FAILED_TO_RESTART => "Failed to restart",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ACTIVE => "success",
            self::INACTIVE => "danger",
            self::DELETED => "danger",
            self::RESTARTING => "warning",
            self::FAILED => "danger",
            self::CREATING => "warning",
            self::DELETING => "warning",
            self::FAILED_TO_CREATE => "danger",
            self::FAILED_TO_DELETE => "danger",
            self::FAILED_TO_RESTART => "danger",
        };
    }
}

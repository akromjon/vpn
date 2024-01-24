<?php

namespace Modules\Pritunl\Models\Enum;



use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum PritunlStatus:string implements HasLabel,HasColor {
    case ACTIVE="active";
    case ENABLING_REVERSE_ACTION="enabling_reverse_action";
    case CREATING="creating";
    case INACTIVE="inactive";
    case DELETING="deleting";
    case DELETED="deleted";
    case RESTARTING="restarting";
    case FAILED="failed";
    case FAILED_TO_CREATE="failed_to_create";
    case FAILED_TO_DELETE="failed_to_delete";
    case FAILED_TO_RESTART="failed_to_restart";
    case FAILED_REVERSE_ACTION="failed_reverse_action";



    public function getLabel(): ?string{
        return match ($this) {
            self::ACTIVE => "Active",
            self::INACTIVE => "Inactive",
            self::DELETED => "Deleted",
            self::RESTARTING => "Restarting",
            self::ENABLING_REVERSE_ACTION => "Enabling reverse action",
            self::FAILED => "Failed",
            self::CREATING => "Creating",
            self::DELETING => "Deleting",
            self::FAILED_TO_CREATE => "Failed to create",
            self::FAILED_TO_DELETE => "Failed to delete",
            self::FAILED_TO_RESTART => "Failed to restart",
            self::FAILED_REVERSE_ACTION => "Failed to enable reverse action",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ACTIVE => "success",
            self::INACTIVE => "danger",
            self::DELETED => "danger",
            self::RESTARTING => "warning",
            self::ENABLING_REVERSE_ACTION => "warning",
            self::FAILED => "danger",
            self::CREATING => "warning",
            self::DELETING => "warning",
            self::FAILED_TO_CREATE => "danger",
            self::FAILED_TO_DELETE => "danger",
            self::FAILED_TO_RESTART => "danger",
            self::FAILED_REVERSE_ACTION => "danger",
        };
    }
}

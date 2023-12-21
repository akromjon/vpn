<?php

namespace App\Models\Pritunl\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum PritunlUserStatus:string implements HasLabel,HasColor {

    case CREATING="creating";
    case IN_USE="in_use";
    case ACTIVE="active";
    case INACTIVE="inactive";
    case UPDATING="updating";
    case UPDATED="updated";
    case DELETING="deleting";
    case DELETED="deleted";
    case FAILED_TO_CREATE="failed_to_create";
    case FAILED_TO_UPDATE="failed_to_update";
    case FAILED_TO_DELETE="failed_to_delete";


    public function getLabel(): ?string{
        return match ($this) {
            self::IN_USE=>"In use",
            self::CREATING => "Creating",
            self::INACTIVE => "Inactive",
            self::ACTIVE => "Active",
            self::UPDATING => "Updating",
            self::UPDATED => "Updated",
            self::DELETING => "Deleting",
            self::DELETED => "Deleted",
            self::FAILED_TO_CREATE => "Failed to create",
            self::FAILED_TO_UPDATE => "Failed to update",
            self::FAILED_TO_DELETE => "Failed to delete",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::IN_USE=>"primary",
            self::CREATING => "warning",
            self::INACTIVE => "danger",
            self::ACTIVE => "success",
            self::UPDATING => "warning",
            self::UPDATED => "success",
            self::DELETING => "warning",
            self::DELETED => "danger",
            self::FAILED_TO_CREATE => "danger",
            self::FAILED_TO_UPDATE => "danger",
            self::FAILED_TO_DELETE => "danger",
        };
    }
}

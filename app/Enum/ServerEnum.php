<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ServerEnum:string implements HasLabel, HasColor{
    case NEW="new";
    case UNAVAILABLE="unavailable";
    case ACTIVE="active";
    case INACTIVE="inactive";
    case DELETED="deleted";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => "New",
            self::ACTIVE => "Active",
            self::INACTIVE => "Inactive",
            self::DELETED => "Deleted",
            self::UNAVAILABLE => "Unavailable",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::NEW => "gray",
            self::ACTIVE => "success",
            self::INACTIVE => "warning",
            self::DELETED => "danger",
            self::UNAVAILABLE => "danger",
        };
    }


}



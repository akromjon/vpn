<?php

namespace App\Models\Pritunl\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
enum PritunlSyncStatus:string implements HasLabel,HasColor {

    case NOT_SYNCED="not_synced";
    case SYNCING="syncing";
    case SYNCED="synced";
    case FAILED_TO_SYNC="failed_to_sync";


    public function getLabel(): ?string{
        return match ($this) {
            self::SYNCING => "Syncing",
            self::SYNCED => "Synced",
            self::FAILED_TO_SYNC => "Failed to sync",
            self::NOT_SYNCED => "Not synced",
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SYNCING => "warning",
            self::SYNCED => "success",
            self::FAILED_TO_SYNC => "danger",
            self::NOT_SYNCED => "danger",
        };
    }
}

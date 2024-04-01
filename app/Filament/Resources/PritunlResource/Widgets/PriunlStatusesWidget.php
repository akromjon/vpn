<?php

namespace App\Filament\Resources\PritunlResource\Widgets;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\Pritunl\Models\Enum\PritunlStatus;
use Modules\Pritunl\Models\Enum\PritunlSyncStatus;
use Modules\Pritunl\Models\Pritunl;

class PriunlStatusesWidget extends BaseWidget
{
    protected static ?string $heading = 'Servers';
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pritunl::query()->where('status', PritunlStatus::ACTIVE)
                    ->where('sync_status', PritunlSyncStatus::SYNCED)
                    ->orderBy('online_user_count', 'desc')
            )
            ->columns([
                TextColumn::make("server.ip")->copyable()->label("Server")->searchable()->sortable(),
                TextColumn::make("online_user_count")->searchable()->sortable()->label("Online"),
                TextColumn::make("server")->formatStateUsing(function ($state, Pritunl $pritunl) {
                    return $pritunl?->server?->city . ", " . $pritunl?->server?->country;
                })->label("Country")->searchable()->sortable(),
                TextColumn::make("status")->label("Status")->badge()->searchable()->sortable(),

            ])->poll(15);
    }
}

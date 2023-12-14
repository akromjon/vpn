<?php

namespace App\Events;

trait BaseEvent
{
    public function __construct(protected mixed $data)
    {

    }
    public static function fire(mixed ...$args): void
    {
        event(new self(...$args));
    }
}

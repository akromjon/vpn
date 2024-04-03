<?php


return [
    "token" => env("TELEGRAM_TOKEN"),
    "chat_id" => env("TELEGRAM_CHAT_ID"),
    "database_backup_chat_id" => env("TELEGRAM_DATABASE_BACKUP_CHAT_ID", env("TELEGRAM_CHAT_ID")),
];

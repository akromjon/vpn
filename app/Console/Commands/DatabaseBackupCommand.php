<?php

namespace App\Console\Commands;

use Akromjon\DBBackup\Backup;
use Akromjon\Telegram\App\Telegram;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database Backup Command - Dumps Databases and sends to Telegram Channel';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info("Database Backup was started!");

        $backup = Backup::run();

        if (!$backup) {

            return $this->error("Database Backup Failed");
        }

        $this->info("Database Backup was finished successfully!");

        $telegram = Telegram::set(config('telegram.token'));

        $telegram->sendFile(

            method: "sendDocument",

            filePath: $backup->getFullPath(),

            params: [
                'chat_id' => config('telegram.database_backup_chat_id')
            ]
        );

        $this->info("Database Backup was send to Telegram Channel!");

        $deleted = $backup->delete();

        if (!$deleted) {
            return $this->error("Database Backup was not deleted!");
        }

        $this->info("Database Backup was deleted successfully!");
    }
}

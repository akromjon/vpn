<?php

namespace Akromjon\DBBackup;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;


class Backup
{
    protected static string $dir = "backups";
    public static function run(): bool|ProcessFailedException
    {
        $process = new Process([
            'mysqldump',
            '-u' . config('database.connections.mysql.username'),
            '-p' . config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            '--result-file=' . self::getDir()
        ]);

        $process->run();

        if (!$result = $process->isSuccessful()) {

            throw new ProcessFailedException($process);
        }

        return $result;
    }

    public static function getDir()
    {
        $path = storage_path(self::$dir);

        if (!File::isDirectory($path)) {

            File::makeDirectory($path);
        }

        return $path . "/" . now()->format('d_m_Y_h:m:s') . ".sql";
    }
}

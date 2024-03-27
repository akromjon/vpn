<?php

namespace Akromjon\DBBackup;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class Backup
{
    protected static string $dir = "backups";
    public static function run():bool|ProcessFailedException
    {
        $process = new Process([
            'mysqldump',
            '-u'.config('database.connections.mysql.username'),
            '-p'.config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            '--result-file=' . self::getDir()
        ]);

        $process->run();

        if (!$result=$process->isSuccessful()) {

            throw new ProcessFailedException($process);
        }

        return $result;
    }

    public static function getDir()
    {
        return storage_path(self::$dir."/".now()->format('d_m_Y_h:m:s').".sql");
    }
}

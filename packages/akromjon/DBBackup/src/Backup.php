<?php

namespace Akromjon\DBBackup;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class Backup
{
    protected static string $dir = "backups";
    protected string $fullPath = "";
    protected bool $status;
    public function handle(): bool|ProcessFailedException
    {
        $process = new Process([
            'mysqldump',
            '-u' . config('database.connections.mysql.username'),
            '-p' . config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            '--result-file=' . $this->getDir()
        ]);

        $process->run();

        $this->status = $process->isSuccessful();

        if (!$this->status) {

            throw new ProcessFailedException($process);
        }

        return $this->status;
    }

    public function isSuccessful(): bool
    {
        return $this->status;
    }
    public static function run(): self
    {
        $self = new self();

        $self->handle();

        return $self;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    protected function getDir(): string
    {
        $path = storage_path(self::$dir);

        if (!File::isDirectory($path)) {

            File::makeDirectory($path);
        }

        $this->fullPath = $path . "/" . now()->format('d_m_Y_h:m:s') . ".sql";

        return $this->fullPath;
    }
}

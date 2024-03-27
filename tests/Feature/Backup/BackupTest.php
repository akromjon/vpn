<?php

use Akromjon\DBBackup\Backup;

class BackupTest extends Tests\TestCase
{
    public function test_it_can_get_backup_dir()
    {
        $dir = Backup::getDir();

        $this->assertIsString($dir);
    }

    public function test_it_can_command()
    {
        $cmd=Backup::run();

        $this->assertTrue($cmd);
    }
}

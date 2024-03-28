<?php

use Akromjon\DBBackup\Backup;

class BackupTest extends Tests\TestCase
{
    public function test_it_can_get_backup_dir()
    {
        $bu=new Backup;

        $dir =$bu->getFullPath();

        $this->assertIsString($dir);
    }

    public function test_it_can_command()
    {
        $cmd=Backup::run();

        $this->assertTrue($cmd->isSuccessful());

        $this->assertIsString($cmd->getFullPath());
    }
}

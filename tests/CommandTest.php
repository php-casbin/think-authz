<?php

namespace tauthz\tests;

class CommandTest extends TestCase
{
    public function testPublish()
    {
        $this->refreshApplication();
        // delete published files
        $this->deletePublishedFiles();
        // run command
        $this->app->console->call('tauthz:publish');

        $this->assertFileExists($this->app->getRootPath() . '/database/migrations/20181113071924_create_rules_table.php');
        $this->assertFileExists(config_path() . 'tauthz-rbac-model.conf');
        $this->assertFileExists(config_path() . 'tauthz.php');
    }

    protected function deletePublishedFiles()
    {
        $destination = $this->app->getRootPath() . '/database/migrations';
        if (file_exists($destination . '/20181113071924_create_rules_table.php')) {
            unlink($destination . '/20181113071924_create_rules_table.php');
            rmdir($destination);
        }
        if (file_exists(config_path() . 'tauthz-rbac-model.conf')) {
            unlink(config_path() . 'tauthz-rbac-model.conf');
        }
        if (file_exists(config_path() . 'tauthz.php')) {
            unlink(config_path() . 'tauthz.php');
        }
    }
}

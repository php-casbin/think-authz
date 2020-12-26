<?php

namespace tauthz\tests;

use Closure;
use think\App;
use PHPUnit\Framework\TestCase as BaseTestCase;
use tauthz\TauthzService;
use tauthz\model\Rule;

class TestCase extends BaseTestCase
{

    protected $app;

    protected $migrate = true;

    public function createApplication()
    {

        // 应用初始化
        $app = new App(__DIR__ . '/../vendor/topthink/think/');

        $app->register(TauthzService::class);

        $app->initialize();

        $app->console->call("tauthz:publish");

        return $app;
    }

    /**
     * 初始数据
     *
     * @return void
     */
    protected function initTable()
    {
        Rule::where("1 = 1")->delete(true);
        Rule::create(['ptype' => 'p', 'v0' => 'alice', 'v1' => 'data1', 'v2' => 'read']);
        Rule::create(['ptype' => 'p', 'v0' => 'bob', 'v1' => 'data2', 'v2' => 'write']);
        Rule::create(['ptype' => 'p', 'v0' => 'data2_admin', 'v1' => 'data2', 'v2' => 'read']);
        Rule::create(['ptype' => 'p', 'v0' => 'data2_admin', 'v1' => 'data2', 'v2' => 'write']);
        Rule::create(['ptype' => 'g', 'v0' => 'alice', 'v1' => 'data2_admin']);
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    protected function testing(Closure $closure)
    {
        $this->_setUp();

        $closure();

        $this->_tearDown();
    }

    /**
     * This method is called before each test.
     */
    protected function _setUp()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->app->console->call("migrate:run");

        $this->initTable();
    }

    /**
     * This method is called after each test.
     */
    protected function _tearDown()
    {
        if ($this->migrate) {
            $this->app->console->call("migrate:rollback");
        }
    }
}
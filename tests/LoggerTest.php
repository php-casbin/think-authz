<?php

namespace tauthz\Tests;

use Mockery;
use tauthz\Logger;

class LoggerTest extends TestCase
{
    public function testLogger()
    {
        $writer = Mockery::mock($this->app->log);

        $logger = new Logger($writer);

        $logger->enableLog(false);
        $this->assertFalse($logger->isEnabled());

        $logger->enableLog(true);
        $this->assertTrue($logger->isEnabled());

        $writer->shouldReceive('info')->once()->with('foo');
        $logger->write('foo');

        $writer->shouldReceive('info')->once()->with('foo1foo2');
        $logger->write('foo1', 'foo2');

        $writer->shouldReceive('info')->once()->with(json_encode(['foo1', 'foo2']));
        $logger->write(['foo1', 'foo2']);

        $writer->shouldReceive('info')->once()->with(sprintf('There are %u million cars in %s.', 2, 'Shanghai'));
        $logger->writef('There are %u million cars in %s.', 2, 'Shanghai');
    }
}

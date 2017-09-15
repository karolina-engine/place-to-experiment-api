<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Mockery as m;


class EventFunctionalTest extends TestCase
{


    public function __construct () {

        parent::__construct();


    }

    public function tearDown()
    {
        m::close();
    }


    public function createApplication()
    {
        $app = require __DIR__.'/../../silex/AppTests.php';
        $app['debug'] = true;
        unset($app['exception_handler']);
        return $app;
    }


    public function testBeatPulse () {

        putenv('emailtesting=mailtrap');

        $app = $this->createApplication();
        $platform = $app['platform'];
        $pulse = new \Karolina\Event\Pulse($platform);
        $pulse->beat();

    }



}

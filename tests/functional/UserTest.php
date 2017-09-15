<?php
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Karolina\User\User;

class UserTest extends TestCase
{


    private $userMockFactory;

    public function __construct () {

        $this->userMockFactory = new \Karolina\User\UserMockFactory();
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

    public function testGetProfile () {

        $currentUser = $this->userMockFactory->getUser();

        $getProfile = new Karolina\User\Action\GetProfile(
            $currentUser, 
            $this->userMockFactory->getUserRepository(), 
            "https://www.example.com/images/"
            );

        $getProfile = $getProfile->forUserId(123);

        $response = $getProfile->getResponse();

//        $this->assertContains($response, array());        


    }

    public function testGetProfileForCurrentUser () {

        $currentUser = $this->userMockFactory->getUser();

        $getProfile = new Karolina\User\Action\GetProfile(
            $currentUser, 
            $this->userMockFactory->getUserRepository(), 
            "https://www.example.com/images/"
            );

        $response = $getProfile->forUserId("123")->getResponse();

        $this->assertEquals($response['profile']['email'], $currentUser->getEmail()); // We should see the e-mail


    }


    public function testGetProfileForAnotherUser () {

        $currentUser = new User('9999', 'anotherexample@example.com', 'Different', 'User');

        $getProfile = new Karolina\User\Action\GetProfile(
            $currentUser, 
            $this->userMockFactory->getUserRepository(), 
            "https://www.example.com/images/"
            );

        $response = $getProfile->forUserId("123")->getResponse();

        $this->assertEquals($response['profile']['email'], NULL); // We're not supposed to see the e-mail


    }    

    

}

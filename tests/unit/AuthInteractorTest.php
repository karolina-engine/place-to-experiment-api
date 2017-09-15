<?php
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Karolina\User\AuthInteractor;

class AuthInteractorTest extends TestCase
{

    public function tearDown()
    {
        m::close();
    }

    public function testLoginWithEmailAndPassword () {

        $authInteractor = new AuthInteractor();


    }

}

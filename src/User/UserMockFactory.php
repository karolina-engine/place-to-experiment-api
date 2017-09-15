<?php

namespace Karolina\User;

use Karolina\User\User;
use Mockery as m;


/******

This class is for quicly creating mocks for tests.

******/

Class UserMockFactory {
	

	public function getUser () {

		$user = new User ("123", "example@example.com", "John", "Smith");
		$profile = new Profile();
		$profile->setShortDescription("I'm a mock user, not much to it.");
		$profile->setDescription("I'm a mock user, here is some more about me.");

		$user->setProfile($profile);

		return $user;

	}


	public function getUserRepository () {

    	$repo = m::mock('Karolina\User\UserRepository');
    	$repo->shouldReceive('getById')->andReturn($this->getUser());
    	return $repo;		

	}	

}
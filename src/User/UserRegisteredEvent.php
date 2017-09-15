<?php

namespace Karolina\User;
use Karolina\Event\Event;

Class UserRegisteredEvent implements Event {

	private $userId;

	public function __construct ($userId) {

		$this->userId = $userId;
		$this->occuredOn = time();

	}

	public function getUserId () {

		return $this->userId;

	}

	public function occurredOn () {

		return $this->occurredOn;
		
	}

}
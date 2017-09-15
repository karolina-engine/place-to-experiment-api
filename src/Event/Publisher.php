<?php 

namespace Karolina\Event;

Class Publisher {
	
	private $subscribers;
	private static $instance = null;

	public static function instance () {

		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;

	}

	private function __construct () {

		$this->subscribers = [];

	}


	public function __clone  () {

		throw new \BadMethodCallException('Clone is not supported');

	}

	public function subscribe (Subscriber $aDomainEventSubscriber) {

		$this->subscribers[] = $aDomainEventSubscriber;

	}

	public function publish (Event $anEvent) {

		foreach ($this->subscribers as $aSubscriber) {

			if ($aSubscriber->isSubscribedTo($anEvent)) {
				$aSubscriber->handle($anEvent);
			}

		}

	}


}
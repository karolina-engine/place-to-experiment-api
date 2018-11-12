<?php

namespace Karolina\User;

use \Karolina\Event\Subscriber;

Class UserEveryMinuteSubscription implements Subscriber {


	public function __construct ()

	{


	}

	public function handle ($EveryMinuteEvent) {

		$notification = new \Karolina\Notification();
		$notification->setSubject('There has been a new minute!');
		$notification->setSingleRecipient('admin@karolina.io');
		$notification->setBodyFromPlaintext('A new minute, new opportunities.');
		$notification->send();

	}

	public function isSubscribedTo ($aDomainEvent) {


		if ($aDomainEvent instanceof \Karolina\Event\EveryMinuteEvent) {

			return true;

		}

		return false;

	}

}

<?php

namespace Karolina\User;

use \Karolina\Event\Subscriber;

class UserEveryMinuteSubscription implements Subscriber
{
    public function __construct()
    {
    }

    public function handle($EveryMinuteEvent)
    {
        $notification = new \Karolina\Notification();
        $notification->setSubject('There has been a new minute!');
        $notification->setSingleRecipient('arnarfjodur@gmail.com');
        $notification->setBodyFromPlaintext('A new minute, new opportunities.');
        $notification->send();
    }

    public function isSubscribedTo($aDomainEvent)
    {
        if ($aDomainEvent instanceof \Karolina\Event\EveryMinuteEvent) {
            return true;
        }

        return false;
    }
}

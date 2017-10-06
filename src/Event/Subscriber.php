<?php 

namespace Karolina\Event;

interface Subscriber
{
    public function handle($aDomainEvent);

    public function isSubscribedTo($aDomainEvent);
}

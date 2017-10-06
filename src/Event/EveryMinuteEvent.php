<?php

namespace Karolina\Event;

use Karolina\Event\Event;

class EveryMinuteEvent implements Event
{

    // in seconds
    const DAY = 86400;
    const HOUR = 3600;

    public function __construct()
    {
        $this->occuredOn = time();
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }
}

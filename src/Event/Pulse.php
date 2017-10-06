<?php

namespace Karolina\Event;

use \Karolina\Event\Publisher;

// This class performs periodic (cronjob-ish) functions


    // in seconds
    const MINUTE = 60;
    const DAY = 86400;
    const HOUR = 3600;
class Pulse
{
    private $platform;

    public function __construct($platform)
    {
        $this->platform = $platform;
    }

    public function beat()
    {
        $this->everyMinute();
    }

    public function everyMinute()
    {
        if ($this->isLimited('beat-minute', MINUTE)) {
            Publisher::instance()->publish(new EveryMinuteEvent());
        }
    }

    public function isLimited($action, $time)
    {
        try {
            $this->platform->limit($action, 1, $time);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

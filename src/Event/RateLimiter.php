<?php

namespace Karolina\Event;

/*

This rate limiter uses a database table (via eloquent model) to store event rates. It is not practical for throttling massive traffick, but is intended to limit certain important actions from happening more often than is allowed.

*/

class RateLimiter
{
    public function happen($happenedKey, $limit, $timeframe)
    {

        // Clean up expired events in the database
        $this->cleanExpired();

        $events = \Karolina\Database\Table\RateLimitedEvent::where('set_time', ">", time() - $timeframe)->where('key', $happenedKey)->first();
        
        if (count($events)) {
            if ($events->allowed_occurances < 1) {
                throw new \Karolina\Exception('This action is rate limited. It can only happen '.$limit.' time(s) within '.$timeframe.' seconds.', 'rate_limit');
            } else {
                return true;
                $events->allowed_occurances = $events->allowed_occurances - 1;
                $events->save();
            }
        } else {
            $this->create($happenedKey, $limit, $timeframe);
            return true;
        }
    }

    public function cleanExpired()
    {
        $storeOldForSecs = 60 * 60;

        $deletedRows = \Karolina\Database\Table\RateLimitedEvent::where('expire_time', "<", time() - $storeOldForSecs)->delete();
    }

    public function create($happenedKey, $limit, $timeframe)
    {
        $event = new \Karolina\Database\Table\RateLimitedEvent();

        $event->key = $happenedKey;
        $event->set_time = time();
        $event->expire_time = time() + $timeframe;
        $event->allowed_occurances = $limit - 1;


        $event->save();
        return true;
    }
}

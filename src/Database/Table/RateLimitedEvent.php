<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

class RateLimitedEvent extends Model
{
    protected $table = 'kf_rate_limited_events';
    protected $primaryKey = 'rate_limited_event_id';
    protected $guarded = [];
}

<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Setting extends Model {

    protected $table = 'kf_settings';
    protected $primaryKey = 'settings_id';
    protected $guarded = [];


}
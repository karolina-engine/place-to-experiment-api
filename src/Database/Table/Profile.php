<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'kf_profiles';
    protected $primaryKey = 'id';
    public $timestamps = false;
}

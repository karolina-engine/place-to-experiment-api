<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Team extends Model {

    protected $table = 'kf_team_members';
    protected $primaryKey = 'team_member_id';
    protected $guarded = [];


}
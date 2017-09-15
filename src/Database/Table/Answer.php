<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class Answer extends Model {

    protected $table = 'kf_answers';
    protected $primaryKey = 'answer_id';
    protected $guarded = [];


}
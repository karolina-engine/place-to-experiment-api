<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

Class Relationship extends Model {

    protected $table = 'kf_relationships';
    protected $primaryKey = 'relationship_id';
    protected $guarded = [];

//    use SoftDeletes;
//    protected $dates = ['deleted_at'];


}
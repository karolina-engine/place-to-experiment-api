<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


Class Experiment extends Model {

    protected $table = 'kf_experiments';
    protected $primaryKey = 'experiment_id';
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('disabled', function (Builder $builder) {
            $builder->where('disabled', '=', 0);
        });
    }    

}
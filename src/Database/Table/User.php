<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class User extends Model
{
    protected $table = 'kf_users';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $guarded = ['email', 'password'];


    public function profile()
    {
        return $this->hasOne('Karolina\Database\Table\Profile', 'user_id', 'id');
    }


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('active', '=', 1);
        });
    }
}

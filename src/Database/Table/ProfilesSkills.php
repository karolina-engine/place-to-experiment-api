<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

Class ProfilesSkills extends Model {

    protected $table = 'kf_profiles_skills';
    protected $primaryKey = 'profile_skill_id';
    protected $guarded = [];
    public $timestamps = false;


    public function skill () {
    
        return $this->hasOne('Karolina\Database\Table\Skill', 'skill_id', 'skill_id');
        
    }
}
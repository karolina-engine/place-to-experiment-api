<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'kf_questions';
    protected $primaryKey = 'question_id';
    protected $guarded = [];
}

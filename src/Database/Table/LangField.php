<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;

class LangField extends Model
{
    protected $table = 'kf_lang_fields';
    protected $primaryKey = 'lang_field_id';
    protected $fillable = ['content', 'format', 'content_key', 'lang_code', 'object_id', 'object_type', 'type'];
}

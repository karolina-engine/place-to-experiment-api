<?php

namespace Karolina;

use Respect\Validation\Validator as v;

class Keyname
{
    private $name;

    public function __construct($name, $type = 'unknown')
    {
        if (v::alnum('_.-')->noWhitespace()->validate($name)) {
            $this->name = $name;
        } else {
            throw new Exception('Key name for '.$type.' must only contain alphanum, underscores, dots or dashes. Not allowed: '.htmlentities($name), 'invalid_arguments');
        }
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    public function get()
    {
        return (string) $this->name;
    }
}

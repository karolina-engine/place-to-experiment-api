<?php

namespace Karolina\User\Hashers;


class NativeHasher implements HasherInterface
{

    private $cost = 12;

    public function hash($value)
    {

        $options['cost'] = $this->cost;

        if (! $hash = password_hash($value, PASSWORD_DEFAULT)) {

            throw new \Karolina\Exception('Error hashing. Check if your verison of PHP supports password_hash()');
            
        }

        return $hash;
    }


    public function check($value, $hashedValue)
    {
        return password_verify($value, $hashedValue);
    }

    public function getHashType () {

        return "native";

    }
}

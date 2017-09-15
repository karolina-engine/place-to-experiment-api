<?php

namespace Karolina\User\Hashers;


interface HasherInterface
{

    public function hash($value);

    public function check($value, $hashedValue);

    public function getHashType();


}

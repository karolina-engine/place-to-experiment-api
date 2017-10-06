<?php

namespace Karolina\User\Hashers;

class IonAuthLegacyHasher implements HasherInterface
{
    private $saltLength = 10;

    public function __construct()
    {
    }


    public function hash($value)
    {
        throw new \Karolina\Exception("IonAuthLegacy hashing method is outdated. Please don't try to make more hashes with this. Hasher is only provided for compatability with old hashes.");
    }

    public function check($password, $hash)
    {
        $salt = substr($hash, 0, $this->saltLength);

        $new_hash = $salt . substr(sha1($salt . $password), 0, -$this->saltLength);

        if ($hash === $new_hash) {
            return true;
        } else {
            return false;
        }
    }


    public function getHashType()
    {
        return "ion";
    }
}

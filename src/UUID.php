<?php

namespace Karolina;

use Ramsey\Uuid\Uuid as Ramsey;
use Respect\Validation\Validator as v;

class UUID
{
    private $uuid;

    public function __construct($uuid = false)
    {
        if ($uuid === false or $uuid === null) {
            $this->setUUID(Ramsey::uuid4()->toString());
        } else {
            $uuid = (string) $uuid;
            $this->setUUID($uuid);
        }
    }

    private function setUUID($uuid)
    {
        if ($this->isValid($uuid)) {
            $this->uuid = $uuid;
        } else {
            throw new KarolinaException('Incorrect UUID');
        }
    }

    private function isValid($uuid)
    {
        /// Good enough
        $uuid = str_replace('-', "", $uuid);
        return v::alnum()->length(32, 32)->validate($uuid);
    }

    public function getString()
    {
        return $this->uuid;
    }

    public function __toString()
    {
        return (string) $this->uuid;
    }
}

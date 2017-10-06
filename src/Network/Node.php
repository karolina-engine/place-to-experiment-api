<?php

namespace Karolina\Network;

class Node
{
    private $type;
    private $id;

    public function __construct($type, $id = null)
    {
        $this->type = $type;
        $this->id = $id;
    }

    public function getNetworkType()
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->id;
    }
}

<?php 

namespace Karolina;

class KarolinaException extends \Exception
{
    private $statusKey;

    public function __construct($message = "Something's wrong", $code = 0, Exception $previous = null, $statusKey = "error")
    {
        $this->statusKey = $statusKey;
    
        parent::__construct($message, $code, $previous);
    }
    
    public function getStatusKey()
    {
        return $this->statusKey;
    }
}

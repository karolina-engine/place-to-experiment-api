<?php 

namespace Karolina;

class Exception extends \Exception
{
    private $statusKey;
    private $httpCode;

    public function __construct($message = "Something's wrong", $statusKey = "error", $code = 500, Exception $previous = null)
    {
        $this->statusKey = $statusKey;
        $this->httpCode = $code;
    
        parent::__construct($message, $code);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getStatusKey()
    {
        return $this->statusKey;
    }
}

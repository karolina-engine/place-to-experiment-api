<?php 

namespace Karolina;


Class Exception extends \Exception {

    private $statusKey;
    private $httpCode;

    public function __construct($message = "Something's wrong", $statusKey = "error", $code = 500, Exception $previous = null) {

        $this->statusKey = $statusKey;
        $this->httpCode = $code;
    
        parent::__construct($message, $code);
    }

    function getHttpCode () {
    	return $this->httpCode;
    }

    function getStatusKey () {
        
        return $this->statusKey;
    }

}
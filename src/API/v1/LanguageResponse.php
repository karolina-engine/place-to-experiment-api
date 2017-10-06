<?php

namespace Karolina\API\v1;

class LanguageResponse extends Response
{
    private $document;
    private $langCode;
        
    public function __construct($document, $langCode)
    {
        $this->document = $document;
        $this->langCode = $langCode;
    }
 
    public function get()
    {
        $doc = $this->document;
        $langCode = strtoupper($this->langCode);

        $response = array();

        // Return the language for that languge code
        if (isset($doc[$langCode])) {
            $response = $doc[$langCode];
        } else { // If it doesn't exist, just return any language that exists

            $response = current($doc);
        }

        return $response;
    }
}

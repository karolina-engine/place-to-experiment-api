<?php

namespace Karolina\API\v1;

Class TagsResponse extends Response {

	private $document;
	private $langCode;
		
	public function __construct ($document, $langCode) {

		$this->document = $document;
		$this->langCode = $langCode;

	}
 
 	public function get () {

 		$doc = $this->document;
 		$langCode = $this->langCode;

 		$response = array();

 		foreach ($doc as $tagId => $tag) {

	 		// Return the language for that language code
			if (isset($tag[$langCode])) {

				$bit = $tag[$langCode];

			} else { // If it doesn't exist, just return any language that exists

				$bit = current($tag);

			}

			$tagResponse['label'] = trim($bit['label']['value']);
			$tagResponse['id'] = $tagId;

			$response[] = $tagResponse;

 		}
		return $response;

 	}

 }
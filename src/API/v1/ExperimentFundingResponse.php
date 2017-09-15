<?php
namespace Karolina\API\v1;

Class ExperimentFundingResponse extends Response {

	private $document;
		
	public function __construct ($document) {

		$this->document = $document;

	}

	public function get () {

		$doc = $this->document;

		if ($doc['sources'] == NULL) {

			unset($doc['sources']);

		}
		$default['goal'] = 0;
		$default['currency'] = "EUR";
		$default['sources']['state']['raised'] = 0;
		$default['sources']['organizations']['raised'] = 0;
		$default['sources']['crowd']['raised'] = 0;
		$default['sources']['crowd']['api'] = NULL;
		$default['sources']['crowd']['campaign_id'] = NULL;

//		$doc['sources']['state']['raised'] = 12;

		$response = array_replace_recursive($default, $doc);


		return $response;

	}

}
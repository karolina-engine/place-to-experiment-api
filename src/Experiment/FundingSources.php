<?php

namespace Karolina\Experiment;
use Respect\Validation\Validator as v;
use Karolina\Keyname;

Class FundingSources {
	
	private $currency;
	private $sources = array();

	public function __construct ($currencyKey = "EUR") {

		$this->setFundingCurrency($currencyKey);

	}
   
	public function setFundingCurrency ($currencyKey) {

		$this->currency = new \Karolina\Currency($currencyKey);

	}

	public function setFundingSource ($sourceKey, $amount) {

		$sourceKey = (string) new Keyname($sourceKey);

        if ($amount != NULL and !v::intVal()->not(v::negative())->validate($amount)) {

            throw new \Karolina\Exception("Amount for funding source amount ".$sourceKey." not valid. Amount not allowed: ".htmlentities($amount));

        }

		$this->sources[$sourceKey]['raised'] = $amount;
		$this->sources[$sourceKey]['type'] = "internal";

	}

	public function setExternalFundingSource ($sourceKey, $apiURL, $campaignId) {

		if ($apiURL != NULL and !v::url()->validate($apiURL)) {

			throw new \Karolina\Exception("API url does not appear to be valid.");

		}

		$sourceKey = (string) new Keyname($sourceKey);		
		$this->sources[$sourceKey]['api'] = $apiURL;
		$this->sources[$sourceKey]['type'] = "external";
		$this->sources[$sourceKey]['campaign_id'] = (string) $campaignId;


	}

	public function getDocument () {

		return $this->sources;

	}

	public function removeFundingSource ($sourceKey) {

		unset($this->sources[$sourceKey]);

	}

	public function setFromDocument ($doc) {

		foreach ($doc as $sourceKey => $source) {

			if ($source['type'] == "internal") {

				$this->setFundingSource($sourceKey, $source['raised']);

			} else if ($source['type'] == "external") {

				$this->setExternalFundingSource($sourceKey, $source['api'], $source['campaign_id']);

			}

		}

	}

}
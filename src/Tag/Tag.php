<?php

namespace Karolina\Tag;
use Karolina\Language\LanguageFields;

Class Tag {

	private $id;
	private $langFields;
	private $man;

	public function __construct () {

		$this->id = NULL;

		$this->langFields = new LanguageFields();

	}

	public function setId ($id) {

		$this->id = $id;

	}

	public function getId () {

		return $this->id;

	}

	public function getLabelDocument () {

		return $this->langFields->getDocument();
	}

	public function getLabelLanguageFields () {

		return $this->langFields->getArray();

	}

	public function setLabelLanguageFields ($langFieldsArray) {

		$this->langFields =  new \Karolina\Language\LanguageFields($langFieldsArray);

	}

	public function setLabel ($value, $langCode = "en") {

		$this->langFields->set('label', $langCode, $value, 'plaintext');

	}

	public function getLabel ($langCode = "en") {

		return $this->langFields->get('label', $langCode, 'Label')->getValue();

	}

}

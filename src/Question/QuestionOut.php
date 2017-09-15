<?php

namespace Karolina\Question;

use \Karolina\Keyname;

Class Question {
	
	private $id;
	private $sentence;
	private $context;
	private $objectType;
	private $objectId;
	private $scope;

	public function __construct () {

		$this->sentence = new \Karolina\Language\LanguageFields();
		$this->makePrivate();

	}

	public function makePrivate () {

		$this->scope = 'private';

	}

	public function makePublic () {

		$this->scrope = 'public';

	}

	public function isPublic () {

		if ($this->scope == 'public') {

			return true;

		} else {

			return false;
		}
	}

	public function getObjectTypeOfQuestion () {

		return $this->object;

	}

	public function getObjectIdOfQuestion () {

		return $this->objectId;

	}

	public function addContext ($contextKey) {

		$context[] = (string) new Keyname($contextKey);

	}

	public function setSentence ($sentenceString, $langCode = 'EN', $format = "plaintext") {

		$this->sentence->set('question_sentence', $langCode, $sentenceString, $format);

	}

	public function getSentence ($langCode = 'EN') {

		return $this->sentence->get('question_sentence', $langCode, '');

	}

	public function setSentenceLanguageFields (\Karolina\LangugeFields $fields) {

		$this->sentenceFields = $fields;

	}

	public function getId () {

		return $this->id;

	}

	public function setId ($id) {

		$this->id = $id;
	}

}
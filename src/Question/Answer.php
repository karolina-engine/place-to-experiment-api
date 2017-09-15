<?php

namespace Karolina\Question;

use \Karolina\Keyname;

Class Answer {


	public function __construct () {


	}

	/*
	public function getQuestionId () {
		return $this->question->getId();
	}

	public function setQuestion ($question) {

		$this->question = $question;
		$this->setObjectType('question');
		$this->setObjectId($question->getId());

	}

	public function getQuestionSentence ($langCode = 'EN') {

		return $this->question->getSentence($langCode);

	}
	*/


	public function setSentence ($sentenceString, $langCode = 'EN', $format = "plaintext") {

		$this->set('answer', $langCode, $sentenceString, $format);

	}

	public function getSentence ($langCode = 'EN') {

		return $this->get('answer', $langCode, '');

	}



}

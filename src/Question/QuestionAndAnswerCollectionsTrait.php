<?php

namespace Karolina\Question;

Trait QuestionAndAnswerCollectionsTrait {

	public function addQuestion (Question $question) {

		$this->questions[$question->getId()] = $question;

	}

	public function addAnswer (Answer $answer) {

		

		$this->answers[$answer->getQuestionId()] = $answer;

	}

	public function getAnswers () {

		return $questions;

	}

	public function getQuestions () {

		return $answers;

	}

	
}
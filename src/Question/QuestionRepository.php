<?php

namespace Karolina\Question;

class QuestionRepository
{
    private $languageFieldsRepository;

    public function __construct()
    {
        $this->languageFieldsRepository = new \Karolina\Language\languageFieldsRepository();
    }

    public function getAnswer(Question $question)
    {
        $model =
            \Karolina\Database\Table\Answer::where('question_id', $question->getId())->first();

        if (count($model) > 0) {

            // Language fields for the answer
            $languageFields = $this->languageFieldsRepository->getLanguageFields('answer', $question->getAnswerId(), 'answer');

            $question->setAnswerId($model->answer_id);
            $question->setAnswerLanguageFields($languageFields);
            return $question;
        } else {

            // No change
            return $question;
        }
    }

    public function saveAnswer(Question $question)
    {

        // First we must save the question model to get an ID
        if ($question->getAnswerId() == null) {

            // Has not been saved before
            $model = new \Karolina\Database\Table\Answer;
        } else {
            $model = \Karolina\Database\Table\Answer::get($question->getAnswerId());
        }

        $model->object_id = $question->getObjectId();
        $model->object_type = $question->getObjectType();
        $model->question_id = $question->getId();
        $model->save();

        $question->setAnswerId($model->answer_id);

        if ($question->getAnswerLanguageFields()) {
            $this->languageFieldsRepository->saveLanguageFields(
                $question->getAnswerLanguageFields()
                );
        }
    }

    public function saveQuestion(Question $question)
    {

        // First we must save the question model to get an ID
        if ($question->getId() == null) {

            // Has not been saved before
            $model = new \Karolina\Database\Table\Question;
        } else {
            $model = \Karolina\Database\Table\Question::get($question->getId());
        }

        $model->object_id = $question->getObjectId();
        $model->object_type = $question->getObjectType();
        $model->context = $question->getContext();
        $model->scope = $question->getScope();
        $model->save();

        $question->setId($model->question_id);

        // Now we can save the language field

        if ($question->getSentenceLanguageFields()) {
            $this->languageFieldsRepository->saveLanguageFields(
                $question->getSentenceLanguageFields()
                );
        }

        $this->saveAnswer($question);
    
        return $model->question_id;
    }

    public function getQuestions($objectType, $objectId)
    {
        $models = \Karolina\Database\Table\Question::where('object_type', '=', $objectType)->where('object_id', '=', $objectId)->get();


        $questions = array();

        foreach ($models as $model) {
            $question = new Question();
            $question->setId($model->question_id);
            $question->setScope($model->scope);
            $question->setObject($model->object_type, $model->object_id);
            $question->setContext($model->context);

            // And now the language fields for the question
            $languageFields = $this->languageFieldsRepository->getLanguageFields('question', $model->question_id, 'question');
            $question->setSentenceLanguageFields($languageFields);

            // Get the answer as well
            $question = $this->getAnswer($question);

            $questions[] = $question;
        }

        return $questions;
    }
}

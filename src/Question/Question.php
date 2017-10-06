<?php

namespace Karolina\Question;

use Karolina\Keyname;

class Question extends \Karolina\Language\LanguageFields
{
    private $id = null;
    private $sentence = null;
    private $questionId;
    private $context = 'default';
    private $objectId;
    private $objectType;
    private $scope = "private";
    private $answer = null;
    private $answerId = null;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;

        if ($this->sentence) {
            $this->sentence->setObjectId($id);
        }
    }

    public function setAnswerId($id)
    {
        $this->answerId = $id;

        if ($this->answer) {
            $this->answer->setObjectId($id);
        }
    }

    public function getAnswerId()
    {
        return $this->answerId;
    }

    public function makePrivate()
    {
        $this->scope = 'private';
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function makePublic()
    {
        $this->scope = 'public';
    }

    public function isPublic()
    {
        if ($this->scope == 'public') {
            return true;
        } else {
            return false;
        }
    }

    public function setContext($context)
    {
        $this->context = (string) new Keyname($context, 'question context');
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObjectType()
    {
        return $this->objectType;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setObjectType($type)
    {
        $this->objectType = $type;
    }

    public function setObjectId($id)
    {
        $this->objectId = $id;
    }

    public function setObject($type, $id)
    {
        $this->objectType = $type;
        $this->objectId = $id;
    }

    public function getSentenceLanguageFields()
    {
        return $this->sentence;
    }

    public function getAnswerLanguageFields()
    {
        return $this->answer;
    }


    public function setSentenceLanguageFields(\Karolina\Language\LanguageFields $fields)
    {
        $this->sentence = $fields;
        $this->sentence->setObjectId($this->getId());
        $this->sentence->setObjectType('question');
        $this->sentence->setType('question');
    }

    public function setAnswerLanguageFields(\Karolina\Language\LanguageFields $fields)
    {
        $this->answer = $fields;
        $this->answer->setObjectId($this->getAnswerId());
        $this->answer->setObjectType('answer');
        $this->answer->setType('answer');
    }

    public function getDocument()
    {
        $questionDoc = $this->getSentenceLanguageFields()->getDocument();

        foreach ($questionDoc as $langCode => $fields) {
            $questionDoc[$langCode] = $fields['question'];
        }
        
        return $questionDoc;
    }

    public function getAnswerDocument()
    {
        if ($this->getAnswerLanguageFields()) {
            $answerDoc = $this->getAnswerLanguageFields()->getDocument();
            foreach ($answerDoc as $langCode => $fields) {
                $answerDoc[$langCode] = $fields['answer'];
            }
        
            return $answerDoc;
        } else {
            return array();
        }
    }

    public function setAnswer($sentenceString, $langCode = 'EN', $format = "plaintext")
    {
        if ($this->answer == null) {
            $sentenceFields = new \Karolina\Language\LanguageFields();
            $this->setAnswerLanguageFields($sentenceFields);
        }

        $this->answer->set('answer', $langCode, $sentenceString, $format);
    }

    public function setSentence($sentenceString, $langCode = 'EN', $format = "plaintext")
    {
        if ($this->sentence == null) {
            $sentenceFields = new \Karolina\Language\LanguageFields();
            $this->setSentenceLanguageFields($sentenceFields);
        }

        $this->sentence->set('question', $langCode, $sentenceString, $format);
    }

    public function getSentence($langCode = 'EN')
    {
        return $this->sentence->get('question', $langCode, '');
    }

    public function getAnswer($langCode = 'EN')
    {
        return $this->answer->get('answer', $langCode, '');
    }
}

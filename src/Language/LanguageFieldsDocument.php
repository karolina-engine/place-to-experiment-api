<?php

namespace Karolina\Language;

class LanguageFieldsDocument
{
    private $languageFieldsDocumentArray = array();

    public function __construct(\Karolina\Language\LanguageFields $languageFields)
    {
        $this->setArrayFromObject($languageFields);
    }

    private function setArrayFromObject($fields)
    {
        $fieldsArray = $fields->getArray();

        foreach ($fieldsArray as $langCode => $fieldArray) {
            $langFields = $fields->getAll($langCode);
            foreach ($langFields as $key => $langField) {
                $this->languageFieldsDocumentArray[$langCode]['value'] = (string) $langField; // Raw value
                $this->languageFieldsDocumentArray[$langCode]['html'] = $langField->getAsHTML(); // HTML formatted
                $this->languageFieldsDocumentArray[$langCode]['format'] = $langField->getFormat(); // Original format
            }
        }
    }

    public function get()
    {
        return $this->languageFieldsDocumentArray;
    }

    public function getAsJson()
    {
        return json_encode($this->get());
    }
}

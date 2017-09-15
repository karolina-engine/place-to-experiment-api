<?php

namespace Karolina\Language;

use Karolina\KarolinaException;
use Karolina\Language\Field;
use Respect\Validation\Validator as v;
use \Karolina\Keyname;

Class LanguageFields {

    private $fields;
    private $objectType;
    private $objectId;
    private $type;
    private $context = 'default';

    public function __construct(array $fields = array())
    {
        $this->fields = $fields;
    }

    public function setContext($context) {

        $this->context = (string) new Keyname($context, 'language fields context');

    }

    public function getContext () {

        return $this->context;
    }

    public function setObjectType ($type) {

        $this->objectType = $type;

    }

    public function getObjectType () {

        return $this->objectType;
    }

    public function setObjectId ($id) {

        $this->objectId = $id;

    }

    public function getObjectId () {

        return $this->objectId;
    }

    public function setType ($type) {

        $this->type = $type;

    }

    public function getType () {

        return $this->type;
    }

    public function getDocument () {

        $doc = array();

        $languages = $this->getAllLanguages();
        $contentKeys = $this->getAllContentKeys();

        foreach ($languages as $langCode) {

            foreach ($contentKeys as $contentKey) {
                $field = $this->get($contentKey, $langCode);
                $doc[$langCode][$contentKey]['html'] = $field->getAsHTML();
                $doc[$langCode][$contentKey]['value'] = $field->getValue();
                $doc[$langCode][$contentKey]['format'] = $field->getFormat();

            }

        }
        return $doc;
    }

    private function getAllContentKeys () {

        $contentKeys = array();
        foreach ($this->fields as $langCode => $content) {
            foreach ($content as $contentKey => $value) {
                $contentKeys[] = $contentKey;
            }
        }
        return $contentKeys;

    }

    private function getAllLanguages () {

        $langCodes = array();
        foreach ($this->fields as $langCode => $content) {
            $langCodes[] = $langCode;
        }

        return $langCodes;
    }

    public function getAll ($langCode) {

        $languages = $this->fields;
        $allFields = array();

        foreach ($languages as $values) {

            foreach ($values as $field => $value) {
                $allFields[] = $field;
            }

        }

        $return = array();

        foreach ($allFields as $singleField) { 

            $return[$singleField] = $this->get($singleField, $langCode);

        }

        return $return;

    }

    public function get ($field, $langCode = 'en', $fallback = "") {

        $langCode = strtoupper($langCode);

        $langValues = $this->fields;

        // Do we have a value in this language?
        if (isset($langValues[$langCode][$field])) {

            $found = $langValues[$langCode][$field];
            return new Field($found['value'], $found['format']);

        } else {

            // Get the next language
            foreach ($langValues as $language) {

                if (isset($language[$field])) {

                    return new Field($language[$field]['value'], $language[$field]['format']);
                }

            }

            // No language at all? Use fallback
           return new Field($fallback);

        }

    }

    public function duplicateFields ($from, $to) {

        foreach ($this->fields as $lang => $field) {

            if (isset($field[$from])) {

                $this->fields[$lang][$to] = $this->fields[$lang][$from];

            }

        }

    }

    public function set ($field, $langCode, $value, $format = "plaintext") {

        $field = (string) new \Karolina\Keyname($field, 'language field');

        // Validate language code
        if (!v::languageCode()->validate(strtolower($langCode))) {

            throw new KarolinaException('Invalid language code', 500, null, "invalid_arguments");
        }

        // Validate format
        if ($format != "json" and $format != "html" and $format != "plaintext") {

            throw new KarolinaException('Supported formats: json, html, plaintext', 500, null, "invalid_arguments");

        } 

        $langCode = strtoupper($langCode);
        $this->fields[$langCode][$field]['value'] = $value;
        $this->fields[$langCode][$field]['format'] = $format;

    }

    public function getArray () {

        return $this->fields;

    }

}
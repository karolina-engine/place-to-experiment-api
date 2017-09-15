<?php

namespace Karolina\Language;

trait CustomLanguageTrait {
	

    public function getAllCustomLanguage ($langCode = 'EN') {

        return $this->customLanguageFields->getAll($langCode);
    }

    public function getCustomLanguage ($fieldName, $langCode = 'EN') {

        return $this->customLanguageFields->get($fieldName, $langCode);
    }

    public function setCustomLanguage ($fieldName, $langCode, $value, $format = "plaintext") {

        $this->customLanguageFields->set($fieldName, $langCode, $value, $format);

    }

    public function setCustomLanguageFields (\Karolina\Language\LanguageFields $fields) {

        $this->customLanguageFields = $fields;

    }

    public function getCustomLanguageFields () {
        return $this->customLanguageFields;
    }

}

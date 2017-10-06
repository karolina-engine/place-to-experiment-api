<?php

namespace Karolina\Language;

trait LanguageTrait
{
    public function setLanguageFields(\Karolina\Language\LanguageFields $languageFields)
    {
        $this->languageFields = $languageFields;
    }

    public function getLanguageFields()
    {
        return $this->languageFields;
    }


    private function setLanguage($fieldName, $langCode, $value)
    {
        $this->languageFields->set($fieldName, $langCode, $value);
    }

    public function setShortDescription($description, $langCode)
    {
        $this->setLanguage('short_description', $langCode, $description);
    }

    public function getShortDescription($langCode = 'EN')
    {
        return $this->languageFields->get('short_description', $langCode);
    }

    private function getLanguage($fieldName, $langCode = 'EN')
    {
        return $this->languageFields->get($fieldName, $langCode);
    }

    public function getTitle($langCode = 'EN')
    {
        return $this->languageFields->get('title', $langCode);
    }


    public function setTitle($title, $langCode)
    {
        $this->setLanguage('title', $langCode, $title);
    }


    public function getOwnerName($langCode = 'EN')
    {
        return $this->languageFields->get('owner_name', $langCode);
    }


    public function setOwnerName($title, $langCode)
    {
        $this->setLanguage('owner_name', $langCode, $title);
    }
}

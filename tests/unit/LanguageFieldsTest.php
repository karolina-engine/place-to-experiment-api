<?php
use PHPUnit\Framework\TestCase;
use Karolina\Language\LanguageFields;

class LanguageFieldsTest extends TestCase
{


    public function testGetStringByLangCodeAndField()
    {

        $LanguageFields = new LanguageFields();
        
        $LanguageFields->set('name', 'en', 'This is in English');
        $LanguageFields->set('name', 'is', 'Þetta er á Íslensku');


        $this->assertEquals('Þetta er á Íslensku', $LanguageFields->get('name', 'is', 'fallback'));
    }

    function testDuplicateFields () {


        $LanguageFields = new LanguageFields();
        
        $LanguageFields->set('name', 'en', 'My name');

        $LanguageFields->duplicateFields('name', 'another_name');

        $this->assertEquals($LanguageFields->get('name', 'en', 'fallback'), $LanguageFields->get('another_name', 'en', 'fallback'));


    }

    public function testGetInexistentString()
    {



        $LanguageFields = new LanguageFields();

        $LanguageFields->set('name', 'en', 'This is in English');
        $LanguageFields->set('name', 'es', 'Esto es en Esponaol');

        $this->assertEquals('This is in English', $LanguageFields->get('name', 'is'));
    }

    public function testGetFallback()
    {

        $fieldsArray['en']['name']['value'] = "This is in English";
        $fieldsArray['is']['name']['value'] = "Þetta er á Íslensku";
        $fieldsArray['is']['short_description']['value'] = "Þetta er stutt lýsing á íslensku";
        $fieldsArray['es']['name']['value'] = "Esto es en Espanol";

        $LanguageFields = new LanguageFields($fieldsArray);

        $this->assertEquals('My fallback string', $LanguageFields->get('doesnt_exist', 'is', 'My fallback string'));
    }


}

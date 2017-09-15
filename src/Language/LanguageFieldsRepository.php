<?php

namespace Karolina\Language;

use Karolina\Language\LanguageFields;

Class LanguageFieldsRepository  {


	public function getLanguageFields ($object_type, $object_id, $type) {

    	$models = \Karolina\Database\Table\LangField::where('type', $type)->where('object_type', $object_type);

        // Get any object of the type
        if ($object_id != NULL) {
            $models = $models->where('object_id', $object_id);
        }

        $models = $models->get();

    	$languageFields = new LanguageFields();

    	foreach ($models as $model) {

    		$languageFields->set(
    			$model->content_key, 
    			$model->lang_code, 
    			$model->content, 
    			$model->format
    		);

            if ($model->context) {
                $languageFields->setContext($model->context);
            }

    	}

      $languageFields->setType($type);
      $languageFields->setObjectType($object_type);
      $languageFields->setObjectId($object_id);


    	return $languageFields;

	}

	public function saveLanguageFields (LanguageFields $languageFields) {



        $object_type = $languageFields->getObjectType();
        $object_id = $languageFields->getObjectId();
        $type = $languageFields->getType();

    	$customLanguageArray = $languageFields->getArray();

        // NULL (stored as 0) means get any object of type
        if ($object_id == NULL) {
            $object_id = 0;
        }

    	foreach ($customLanguageArray as $lang => $contentKeys) {

    		foreach ($contentKeys as $key => $entry) {

    			$langFieldModel = \Karolina\Database\Table\LangField::updateOrCreate(
				    [
				    'object_type' => $object_type, 
				    'object_id' => $object_id, 
				    'type' => $type,
				    'content_key' => $key,
				    'lang_code' => $lang
				    ],
				    [
				    'content' => $entry['value'],
				    'format' => $entry['format']
				    ]);


    		}

    	}

	}

}
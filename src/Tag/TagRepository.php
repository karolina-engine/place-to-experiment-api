<?php

namespace Karolina\Tag;

Class TagRepository {

	public function save (Tag $tag) {

		// First we must save the tag model to get an ID
		if ($tag->getId() == NULL) { 

			// Has not been saved before
			$model = new \Karolina\Database\Table\Tag;

		} else {

			$model = \Karolina\Database\Table\Tag::get($tag->getId());

		}

		$model->label_language = json_encode($tag->getLabelLanguageFields());

		$model->save();

	}
	

	public function getById ($id) {

		$model = \Karolina\Database\Table\Tag::findOrFail($id);

		$tag = new Tag();

		$tag->setLabelLanguageFields(json_decode($model->label_language, true));

		$tag->setId($model->tag_id);

		return $tag;

	}



	public function getAll () {

		$models = \Karolina\Database\Table\Tag::all();

		$tags = array();
		foreach ($models as $model) {

			$tag = new Tag();
			$tag->setLabelLanguageFields(json_decode($model->label_language, true));
			$tag->setId($model->tag_id);
			$tags[] = $tag;

		}

		return $tags;

	}

	public function getTagsFromTagIdCollection ($tagIdCollection) {

		$tags = array();

		foreach ($tagIdCollection as $tagId) {

			$tag = $this->getById($tagId);
			$tags[] = $tag;
		}

		return $tags;

	}

}
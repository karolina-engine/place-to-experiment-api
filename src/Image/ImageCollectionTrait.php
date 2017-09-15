<?php

namespace Karolina\Image;

Trait ImageCollectionTrait {
	
	public function addImage (Image $image) {

		$this->images[$image->getContentKey()] = $image;

	}
	
	public function getImage ($contentKey) {

		if (isset($this->images[$contentKey])) {
			return $this->images[$contentKey];
		}

		return NULL; 
	}

	public function getImageCollectionDocument () {

		$doc = array();

		foreach ($this->images as $contentKey => $image) {

			$doc[$contentKey]['filename'] = $image->getFilename();

		}
		return $doc;
	}

	public function setImageCollectionFromDocument ($doc) {

		foreach ($doc as $contentKey => $imageData) {
			$image = new Image($imageData['filename'], $contentKey);
			$this->addImage($image);
		}

	}
}
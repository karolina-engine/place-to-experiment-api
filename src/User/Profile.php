<?php 

namespace Karolina\User;



use Karolina\Language\Field;

Class Profile {

private $shortDescription;
private $description;	


	function setShortDescription ($content) {

		if (is_a($content, 'Karolina\Language\Field')) {

			$this->shortDescription = $content;

		} else {

			$this->shortDescription = new Field($content);

		}


	}


	function setDescription ($content) {

		if (is_a($content, 'Karolina\Language\Field')) {
			
			$this->description = $content;

		} else {

			$this->description = new Field($content);

		}

	}


	function getDescription () {

		return $this->description;

	}

	function getShortDescription () {

			return $this->shortDescription;
		
	}


}
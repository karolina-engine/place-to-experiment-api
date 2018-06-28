<?php


namespace Karolina\User;


class UserRepositoryFilter {

	private $models;

	public function __construct ($models) {

		$this->models = $models;

	}

	public function fromArguments ($args) {

		if (isset($args['tag'])) {
			$this->byTag($args['tag']);
		}

		if (isset($args['limit'])) {
			$this->models = $this->models->limit((int)$args['limit']);
		}

		if (isset($args['offset'])) {
			$this->models = $this->models->offset((int)$args['offset']);
		}

	}

	public function byTag ($tag) {

		$this->models = $this->models->whereRaw('json_contains(tags, "['.$tag.']")');

	}

	public function getModels () {

		return $this->models;
	}

}
<?php

namespace Karolina\Network;

Class Node {

	private $type;
	private $id;

	public function __construct ($type, $id = NULL) {

		$this->type = $type;
		$this->id = $id;

	}

	function getNetworkType () {

		return $this->type;
	}

	function getId () {

		return $this->id;
		
	}

}
<?php

namespace Karolina\Network;


Class Relationship {

	public $follower;
	public $followed;
	private $type;

	public function getFollower () {

		return $this->follower;

	}

	public function setFollower ($follower) {

		$this->follower = $follower;

	}

	public function getFollowed () {

		return $this->followed;

	}

	public function setFollowed ($followed) {

		$this->followed = $followed;

	}

	public function setRelationshipType ($type) {

		$this->type = $type;

	}

	public function getRelationshipType () {

		return $this->type;

	}

}
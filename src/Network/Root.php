<?php

namespace Karolina\Network;


Class Root {

	private $rootObject;
	private $followersOfRoot = array();
	private $followedByRoot = array();
	private $trashed = array();

	public function __construct ($rootObject) {

		$this->rootObject = $rootObject;

	}

	public function getRootType () {

		return $this->rootObject->getNetworkType();

	}

	public function getRootId () {

		return $this->rootObject->getId();

	}

	public function removeFollower ($follower, $relationshipType) {

		unset($this->followersOfRoot[$relationshipType][$follower->getId()]);

	}

	public function addFollower ($follower, $relationshipType) {

		$relationship = new Relationship();
		$relationship->setFollower($follower);
		$relationship->setFollowed($this->rootObject);
		$relationship->setRelationshipType($relationshipType);

		$this->followersOfRoot[$relationshipType][$follower->getId()] = $relationship;

	}


	public function addLeader ($leader, $relationshipType) {

		$relationship = new Relationship();
		$relationship->setFollower($this->rootObject);
		$relationship->setFollowed($leader);
		$relationship->setRelationshipType($relationshipType);

		$this->followedByRoot[$relationshipType][$leader->getId()] = $relationship;

	}

	public function getLeaders () {

		return $this->followedByRoot;

	}

	public function getFollowers () {

		return $this->followersOfRoot;

	}


	public function getLeadersByRelationship ($type) {

		if (isset($this->followedByRoot[$type])) {

			return $this->followedByRoot[$type];

		} else {

			return array();
		}

	} 

	public function getLeadersIdsByRelationship ($type) {

		$followers = $this->getLeadersByRelationship($type);

		$idArray = array();

		foreach ($followers as $leader) {

			$idArray[$leader->followed->getId()] = $leader->followed->getNetworkType();

		}

		return $idArray;

	}

	public function getFollowersByRelationship ($type) {

		if (isset($this->followersOfRoot[$type])) {

			return $this->followersOfRoot[$type];

		} else {

			return array();
		}

	} 

	public function getFollowerIdsByRelationship ($type) {

		$followers = $this->getFollowersByRelationship($type);

		$idArray = array();

		foreach ($followers as $follower) {

			$idArray[$follower->follower->getId()] = $follower->follower->getNetworkType();

		}

		return $idArray;

	}

	public function countFollowersByRelationship ($type) {

		$followers = $this->getFollowersByRelationship($type);
		return count($followers);

	}


}
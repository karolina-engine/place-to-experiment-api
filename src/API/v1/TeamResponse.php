<?php
namespace Karolina\API\v1;

Class TeamResponse extends Response {

	private $document;
	private $imagePath;

	public function __construct ($document, $imagePath) {

		$this->document = $document;
		$this->imagePath = $imagePath;
	}

	public function get () {

		$doc = $this->document;
		$imgStorageUrl = $this->imagePath;

		$response = array();

		// Put absolute urls for teams
		foreach ($doc as $memberId => $memberData) {

            $member = array();

			if ($doc[$memberId]['image'] != NULL) {

				$member['image'] = $imgStorageUrl."/".$memberData['image'];

			} else {

				$member['image'] = NULL;

			}

			$member['profile_id'] = (string) $memberId;
			$member['first_name'] = $memberData['first_name'];
			$member['last_name'] = $memberData['last_name'];

            $response[] = $member;
		}

		return $response;

	}

}

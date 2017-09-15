<?php

namespace Karolina\API\v1;

Class ExperimentResponse extends Response {

	private $document;
	private $platform;
	private $langCode;
		
	public function __construct ($document, $langCode, $platform) {

		$this->document = $document;
		$this->platform = $platform;
		$this->langCode = $langCode;

	}

	public function getPreview () {

		$doc = $this->document;
		$imgStorageUrl = $this->platform->getImgStorageUrl();

		$response['stage'] = (int) $doc['stage'];
		$response['like_count'] = (int) $doc['like_count'];
		$response['experiment_id'] = (string) $doc['experiment_id'];

		$language = (new LanguageResponse($doc['language'], $this->langCode))->get();

		if (isset($language['title']['value'])) {
			$response['title'] = $language['title']['value'];
		} else {
			$response['title'] = "Untitled";	
		}

		if (isset($language['short_description']['value'])) {
			$response['short_description'] = $language['short_description']['value'];
		} else {
			$response['short_description'] = "";	
		}

		if (isset($language['owner_name']['value'])) {
			$response['owner_name'] = $language['owner_name']['value'];
		} else {
			$response['owner_name'] = "";	
		}


		$images = (new ImageCollectionResponse($doc['image_collection'], $imgStorageUrl))->get();

		if (isset($images['top_image']['url'])) {
			$response['image'] = $images['top_image']['url'];
		} else {
			$response['image'] = NULL;
		}


		$response['show_in'] = $doc['show_in'];

		return $response;


	}

	public function get () {

		$doc = $this->document;
		$imgStorageUrl = $this->platform->getImgStorageUrl();

		$response['stage'] = (int) $doc['stage'];
		$response['geographic_location'] = (string) $doc['geographic_location'];

		$response['like_count'] = (int) $doc['like_count'];

		$response['experiment_id'] = (string) $doc['experiment_id'];
		$response['custom_language'] = (new LanguageResponse($doc['custom_language'], $this->langCode))->get();
		$response['language'] = (new LanguageResponse($doc['language'], $this->langCode))->get();

		$response['image_collection'] = (new ImageCollectionResponse($doc['image_collection'], $imgStorageUrl))->get();
		$response['team'] = (new TeamResponse($doc['team'], $imgStorageUrl))->get();

		$response['disabled'] = (bool) $doc['disabled'];

		if (count($doc['show_in']) == 0) {
			
			$response['show_in'] = NULL;

		} else {

			$response['show_in'] = $doc['show_in'];

		}


		$response['funding'] = (new ExperimentFundingResponse($doc['funding']))->get();

		$response['status'] = "success";

		$response['tags'] = (new TagsResponse($doc['tags'], $this->langCode))->get();

		$response['links'] = $doc['links'];

		return $response;

	}

	public function getMyRelationships ($currentUser) {

		if (isset($this->document['relationships'])) {
			$relationshipsDoc = $this->document['relationships'];

		}
		$relationships = array();

		if ($currentUser) {

			if (isset($relationshipsDoc['like'][$currentUser->getId()])) {

				$relationships[] = "like";

			}
		}

		return $relationships;

	}
	public function getACL ($currentUser) {

		$doc = $this->document;
		$acl = ['view'];

		if ($currentUser) {
			// If user is in the team or is admin, invite to edit
			if (isset($doc['team'][$currentUser->getId()]) or $currentUser->isAdmin()) {
				$acl[] = 'edit';
			}

			// If user is in the team, add edit
			if ($currentUser->isAdmin()) {
				$acl[] = 'admin';
			}
		}

		return $acl;

	}

}
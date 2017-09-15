<?php

namespace Karolina\Experiment;

Class ExperimentInteractor {

	private $experiment;
	private $repository;
	private $currentUser = false;
	
	public function __construct ($experimentRepository) {

		$this->repository = $experimentRepository;

	}

	public function getRepository () {

		return $this->repository;
		
	}

	public function setCurrentUser (\Karolina\User\User $user) {

		$this->currentUser = $user;

	}

	public function getExperiment () {

		return $this->experiment;

	}

	public function setExperimentById ($id) {

		$this->experiment = $this->repository->getById($id);

	}

	public function getDocument ($experimentId, $langCode = false) {

		$document = $this->repository->getDocument($experimentId, $langCode);
		return $document;

	}

	public function addLike () {

		$this->experiment->addLike($this->currentUser);
		$this->repository->save($this->experiment);

	}


	public function removeLike () {

		$this->experiment->removeLike($this->currentUser);
		$this->repository->save($this->experiment);

	}


	public function getAllPreviewDocumentsByTeamMember ($userId, $langCode = 'EN') {

		$filterArguments['team_member_id'] = $userId;

		return $this->getPreviewDocuments($langCode, ['team_member_id' => $userId]);

	}


	public function getPreviewDocuments ($langCode, $filterArguments = array()) {

		$documents = $this->repository->getDocuments($filterArguments);

		return $documents;

		$langCode = strtoupper($langCode);


		$previews = array();
		foreach ($documents as $doc) {


			$preview['experiment_id'] = $doc['experiment_id'];
			$preview['stage'] = (int) $doc['stage'];

			if (isset($doc['language'][$langCode]['title'])) {
				$preview['title'] = $doc['language'][$langCode]['title']['value'];
			} else {
				$preview['title'] = 'Untitled Experimenter!';
			}

			if (isset($doc['language'][$langCode]['short_description'])) {
				$preview['short_description'] = $doc['language'][$langCode]['short_description']['value'];
			} else {
				$preview['short_description'] = '';
			}

			if (isset($doc['language'][$langCode]['owner_name'])) {
				$preview['owner_name'] = $doc['language'][$langCode]['owner_name']['value'];
			} else {
				$preview['owner_name'] = '';
			}


			if (isset($doc['image_collection']['top_image'])) {
				$preview['image'] = $doc['image_collection']['top_image']['filename'];
			} else {
				$preview['image'] = null;
			}

			if (isset($doc['like_count'])) {
				$preview['like_count'] = $doc['like_count'];				
			} else {
				$preview['like_count'] = 0;
			}

			$previews[] = $preview;

		}

		return $previews;
	}

	private function setIfExists ($toSet, $fallback = '') {


	}

}
<?php

namespace Karolina\Experiment;

Class ExperimentInteractor {

	private $experiment;
	/** @var  ExperimentRepository */
	private $repository;
	private $currentUser = false;

	public function __construct ($experimentRepository) {

		$this->repository = $experimentRepository;

	}

	public function getStats ($showIn = false) {

        return $this->repository->getExperimentStats($showIn);
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
	}

	private function setIfExists ($toSet, $fallback = '') {


	}

}

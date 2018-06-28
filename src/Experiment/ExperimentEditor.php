<?php

namespace Karolina\Experiment;

Class ExperimentEditor {

	private $currentUser;
	private $interactor;
	private $repository;

	public function __construct ($currentUser, $interactor, $repository) {

		$this->currentUser = $currentUser;
		$this->interactor = $interactor;
		$this->repository = $interactor->getRepository();

	}

	public function createNew ($stage = false) {

		$newExperiment = new Experiment();
		$newExperiment->addCreator($this->currentUser);

        $newExperiment->setPlaceToShow('index', TRUE);

		if ($stage) {
			$newExperiment->setStage($stage);

		}

		$experimentId = (string) $this->repository->create($newExperiment);

		return $experimentId;

	}

	public function setExperimentById ($experimentId) {

		$this->interactor->setExperimentById($experimentId);

	}

	public function updateSettings ($newSettings) {

		$experiment = $this->interactor->getExperiment();

		// Can we edit this?
		if ($experiment->canEditSettings($this->currentUser)) {


			if (isset($newSettings['geographic_location'])) {

				$experiment->setGeographicLocation($newSettings['geographic_location']);

			}

			if (isset($newSettings['stage'])) {

				$experiment->setStage($newSettings['stage']);

			}

			if (isset($newSettings['disabled']) and $experiment->canEditAbility($this->currentUser)) {

				if ($newSettings['disabled'] == true) {
					$experiment->disable();
				} else {
					$experiment->enable();
				}

			}

			if (isset($newSettings['show_in']) and $experiment->canEditWhereToShow($this->currentUser)) {

				foreach ($newSettings['show_in'] as $place => $set) {

					$experiment->setPlaceToShow($place, $set);

				}
			}


			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update settings", "access_denied", 401);

		}

	}


	public function updateImages ($imageCollectionData) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			$experiment->setImageCollectionFromDocument($imageCollectionData);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update images", "access_denied", 401);
		}

	}




	public function moveToNextStage () {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditSettings($this->currentUser)) {

			$experiment->moveToNextStage();
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to change the stage", "access_denied", 401);
		}

	}

	public function replaceTags ($tagIdCollection) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$tagRepository = new \Karolina\Tag\TagRepository();
			$tags = $tagRepository->getTagsFromTagIdCollection($tagIdCollection);
			$experiment->replaceTags($tags);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}

	}

	public function replaceLinks ($links) {

		$experiment = $this->interactor->getExperiment();

		if ($this->currentUser and $experiment->canEditContent($this->currentUser)) {

			$experiment->setLinks($links);
			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update tags", "access_denied", 401);
		}

	}

	public function updateFunding ($funding) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			if (isset($funding['goal'])) {

				$experiment->setFundingGoalAmount($funding['goal']);

			}

			if (isset($funding['currency'])) {

				$experiment->setFundingGoalCurrency($funding['currency']);

			}

			if (isset($funding['sources']['state'])) {

				$experiment->setStateFunding($funding['sources']['state']['raised']);
			}

			if (isset($funding['sources']['organizations'])) {

				$experiment->setOrganizationsFunding($funding['sources']['organizations']['raised']);
			}

			if (isset($funding['sources']['crowd']['api'])) {


				if ($funding['sources']['crowd']['api'] === NULL or $funding['sources']['crowd']['api'] == "") {

					$experiment->setCrowdFunding(NULL, NULL);


				} else if (isset($funding['sources']['crowd']['api']) and isset($funding['sources']['crowd']['campaign_id'])) {

					$apiUrl = $funding['sources']['crowd']['api'];
					$campaignId = $funding['sources']['crowd']['campaign_id'];
					$experiment->setCrowdFunding($apiUrl, $campaignId);

				} else {

					throw new \Karolina\Exception('Please set both campaign ID and platform API URL.');

				}

			}

		}

		$this->repository->save($experiment);

	}

	public function updateFundingGoalAmount ($goal) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			$experiment->setFundingGoalAmount($goal);

		}
		$this->repository->save($experiment);

	}

	public function updateFundingGoalCurrency ($currency) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			$experiment->setFundingGoalCurrency($currency);

		}
		$this->repository->save($experiment);

	}


	public function updateFundingSources ($fundingSources) {


		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditFunding($this->currentUser)) {

			if (isset($fundingSources['state'])) {

				$experiment->setStateFunding($fundingSources['state']['raised']);
			}

			if (isset($fundingSources['organizations'])) {

				$experiment->setOrganizationsFunding($fundingSources['organizations']['raised']);
			}

			if (isset($fundingSources['crowd'])) {

				$apiUrl = $fundingSources['crowd']['api'];
				$campaignId = $fundingSources['crowd']['campaign_id'];

				$experiment->setCrowdFunding($apiUrl, $campaignId);

			}


		}

		$this->repository->save($experiment);

	}


	public function updateCustomLanguage ($customLanguageFields, $langCode) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			foreach ($customLanguageFields as $fieldName => $content) {

				$experiment->setCustomLanguage($fieldName, $langCode, $content['value'], $content['format']);
			}

			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update custom language", "access_denied", 401);
		}

	}


	public function updateDefaultLanguage ($defaultLanguageFields, $langCode) {

		$experiment = $this->interactor->getExperiment();

		if ($experiment->canEditContent($this->currentUser)) {

			if (isset($defaultLanguageFields['title']['value'])) {
				$experiment->setTitle($defaultLanguageFields['title']['value'], $langCode);
			}

			if (isset($defaultLanguageFields['short_description']['value'])) {
				$experiment->setShortDescription($defaultLanguageFields['short_description']['value'], $langCode);
			}

			if (isset($defaultLanguageFields['owner_name']['value'])) {
				$experiment->setOwnerName($defaultLanguageFields['owner_name']['value'], $langCode);
			}


			$this->repository->save($experiment);

		} else {

			throw new \Karolina\Exception("Current user is not authorized to update language", "access_denied", 401);
		}

	}

}

<?php

namespace Karolina\Experiment;

use Illuminate\Database\Eloquent\ModelNotFoundException;


Class ExperimentRepository {

	private $languageFieldsRepository;

	public function __construct ($ci) {

		$this->languageFieldsRepository = new \Karolina\Language\LanguageFieldsRepository();
		$this->questionRepository = new \Karolina\Question\QuestionRepository();
		$this->userRepository = new \Karolina\User\UserRepository($ci);
		$this->teamRepository = new \Karolina\Team\TeamRepository($this->userRepository);

	}

	private function modelToExperiment ($model) {

		$experiment = new \Karolina\Experiment\Experiment();

		$experiment->setStage($model->stage);
		$experiment->setCreatedAt($model->created_at);
		$experiment->setUpdatedAt($model->updated_at);
		$experiment->setStage($model->stage);
		$experiment->setSettingsGroup($this->getSettingsGroup($model->settings));
		$experiment->setId($model->experiment_id);

		if ($model->disabled) {
			$experiment->disable();
		} else {
			$experiment->enable();
		}

		// Places to show
		if ($model->show_in) {
			$placesToShowData = json_decode($model->show_in, true);

			foreach ($placesToShowData as $place => $set) {
				$experiment->setPlaceToShow($place, $set);
			}
		}

		// Retrieve and set team
		$team = $this->teamRepository->getTeam ('experiment', $model->experiment_id);
		$experiment->setTeam($team);

		// Retrieve and set custom language fields
		$customLanguageFields =
		$this->languageFieldsRepository->getLanguageFields ('experiment', $model->experiment_id, 'custom');
		$experiment->setCustomLanguageFields($customLanguageFields);

		// Retrieve and set default language fields
		$defaultLanguageFields =
		$this->languageFieldsRepository->getLanguageFields ('experiment', $model->experiment_id, 'default');
		$experiment->setLanguageFields($defaultLanguageFields);

		// Retrieve and set questions
		$questions = $this->questionRepository
		->getQuestions ('experiment', $model->experiment_id);
		foreach ($questions as $question) {
			$experiment->addQuestion($question);
		}

		// Retrieve and set image collection
		if ($model->images) {
			$imagesData = json_decode($model->images, true);
			$experiment->setImageCollectionFromDocument($imagesData);
		}

		// Retreave and set funding
		if ($model->funding) {
			$fundingSources = json_decode($model->funding, true);
			$experiment->setFundingFromDocument($fundingSources);
		}

		// Set links
		if ($model->links) {
			$experiment->setLinks(json_decode($model->links, true));
		}

		// Retrieve and set tags
		if ($model->tags) {
			$tagRepository = new \Karolina\Tag\TagRepository();
			$tags = $tagRepository->getTagsFromTagIdCollection(json_decode($model->tags, true));
			$experiment->replaceTags($tags);
		}

		$experiment->setNetwork($this->getNetwork($experiment->getId()));

		return $experiment;
	}


    private function getSettingsGroup ($fetchedSettings) {

		$settings = json_decode($fetchedSettings, true);

		$settingsGroup = new \Karolina\Setting\SettingsGroup();

		if ($settings) {
			foreach ($settings as $variableName => $value) {
				$settingsGroup->set($variableName, $value);
			}

		}

		return $settingsGroup;

    }

	private function experimentToModel (Experiment $experiment, $model) {

		$model->stage = $experiment->getStage();
		if($experiment->getCreatedAt()){
			$model->created_at = $experiment->getCreatedAt();
		}
		if($experiment->getUpdatedAt()){
			$model->updated_at = $experiment->getUpdatedAt();
		}

		if ($experiment->isEnabled()) {
			$model->disabled = 0;
		} else {
			$model->disabled = 1;
		}

		// Places to show
		$model->show_in = json_encode($experiment->getPlacesToShow());

		// Save team
		$team = $this->teamRepository
		->saveTeam ($experiment->getTeam(), 'experiment', $experiment->getId());


		// Save questions
		foreach ($experiment->getQuestions() as $question) {
			$this->questionRepository->saveQuestion($question);
		}

		// Save custom langugae fields
		$this->languageFieldsRepository->saveLanguageFields(
			$experiment->getCustomLanguageFields());

		// Save default language fields
		$this->languageFieldsRepository->saveLanguageFields(
			$experiment->getLanguageFields());

		// Save funding sources
		$model->funding = json_encode($experiment->getFundingDocument());

		$model->links = json_encode($experiment->getLinks());

		$model->settings = json_encode($experiment->getAllSettings());

		// Tags
		$tagIdArray = array();
		foreach ($experiment->getTags() as $tag) {
			$tagIdArray[] = $tag->getId();
		}
		$model->tags = json_encode($tagIdArray);

		$model->images = json_encode($experiment->getImageCollectionDocument());

		$this->saveNetwork($experiment->getNetwork());

		return $model;

	}

	private function experimentToJsonDocument (Experiment $experiment) {

		$doc['last_updated'] = time();
		$doc['created_at'] = $experiment->getCreatedAt();
		$doc['updated_at'] = $experiment->getUpdatedAt();
		$doc['experiment_id'] = (string) $experiment->getId();
		$doc['stage'] = (string) $experiment->getStage();
		$doc['custom_language'] = $experiment->getCustomLanguageFields()->getDocument();
		$doc['language'] = $experiment->getLanguageFields()->getDocument();
		$doc['image_collection'] = $experiment->getImageCollectionDocument();
		$doc['team'] = $experiment->getTeamDocument();
		$doc['question_contexts'] = $experiment->getQuestionsDocument();
		$doc['show_in'] = $experiment->getPlacesToShow();
		$doc['disabled'] = (int) !$experiment->isEnabled();
		$doc['tags'] = $experiment->getTagsDocument();
		$doc['funding'] = $experiment->getFundingDocument();
		$doc['like_count'] = $experiment->countLikes();
		$doc['relationships']['like'] = $experiment->getLikers();
		$doc['links'] = $experiment->getLinks();
		$doc['geographic_location'] = $experiment->getGeographicLocation();
		$doc['team_emails'] = $experiment->getTeamEmails();

		return json_encode($doc);


	}

	public function getDocument ($id, $langCode = false) {

    	try {

			$model = \Karolina\Database\Table\Experiment::findOrFail($id);

    	} catch (ModelNotFoundException $e) {

    		throw new \Karolina\Exception("Experiment not found. It may have been removed or you have the wrong URL.", 'not_found', 404);

		}

		catch (\Exception $e) {

    		throw new \Karolina\Exception("There was a problem getting the experiment from the database.", 'error', 500);

		}

		$document = json_decode($model->document, true);

		if ($langCode) {

			if (isset($document['question_stages']) and $document['question_stages']) {
				foreach ($document['question_stages'] as $keyC => $context) {

					foreach ($context as $keyQ => $question) {

						if (isset($question[$langCode])) {

							$document['question_stages'][$keyC][$keyQ] = $question[$langCode];

						} else {
							$document['question_stages'][$keyC][$keyQ] = current($question);
						}

					}

				}
			} else {

				$document['question_stages'] = array ();

			}


		}

		return $document;
	}

    public function getById($id) {

		$model = \Karolina\Database\Table\Experiment::findOrFail($id);
		$experiment = $this->modelToExperiment($model);

		return $experiment;
	}

	public function save (Experiment $experiment) {

		$model = \Karolina\Database\Table\Experiment::findOrFail($experiment->getId());

		$model = $this->experimentToModel($experiment, $model);

		$model->document = $this->experimentToJsonDocument($experiment);

		$model->save();

	}

	private function getFilteredModels ($filterArguments) {

		$filter = new ExperimentRepositoryFilter(
			new \Karolina\Database\Table\Experiment()
			);

		$filter->fromArguments($filterArguments);

		return $filter->getModels();

	}

	public function getDocuments ($filterArguments) {

		$models = $this->getFilteredModels($filterArguments)->get();

		$docs = array();
		foreach ($models as $model) {
			if ($model->document) {
				$docs[] = json_decode($model->document, true);
			}
		}

		return $docs;

	}

	public function reWriteAllDocuments () {

		$models = \Karolina\Database\Table\Experiment::get();

		foreach ($models as $model) {

			$experiment = $this->modelToExperiment($model);
			$document = $this->experimentToJsonDocument($experiment);
			$model->document = $document;
			$model->save();
		}

		return count($models);

	}

	public function getExperimentStats ($showIn = false) {

			if ($showIn) {

					$stats['count'] = \Karolina\Database\Table\Experiment::whereRaw('json_contains(show_in, "true", "$.'.$showIn.'")')->count();

			} else {

					$stats['count'] = \Karolina\Database\Table\Experiment::count();

			}

	    return $stats;

    }


	public function create (Experiment $experiment) {

		$model = new \Karolina\Database\Table\Experiment;
		$model->save();

		$experiment->setId($model->experiment_id);

		$model = $this->experimentToModel($experiment, $model);
		$model->document = $this->experimentToJsonDocument($experiment);
		$model->save();

		return (string) $model->experiment_id;

	}

	public function getNetwork ($experimentId) {

		$networkRepository = new \Karolina\Network\NetworkRepository();
		$network = $networkRepository->getNetwork('experiments', $experimentId);
		return $network;

	}

	public function saveNetwork ($network) {

		$networkRepository = new \Karolina\Network\NetworkRepository();
		$networkRepository->saveNetwork($network);


	}

}

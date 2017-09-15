<?php 

namespace Karolina\Experiment;

Class ExperimentRepositoryFilter {

	private $models;

	public function __construct ($models) {

		$this->models = $models; 

	}

	public function fromArguments ($args) {

		if (isset($args['team_member_id'])) {

			$this->byTeamMember($args['team_member_id']);
		}

		if (isset($args['place'])) {
			$this->byPlaceToShowIn($args['place']);
		}

		if (isset($args['stage'])) {
			$this->byStage($args['stage']);
		}

		if (isset($args['limit'])) {
			$this->models = $this->models->limit($args['limit']);
		}

		if (isset($args['offset'])) {
			$this->models = $this->models->offset((int)$args['offset']);
		}

	}

	public function byTeamMember($userId) {

		$this->models = $this->models->where('document->team->'.$userId.'->in_team', true);

	}

	public function byPlaceToShowIn($place) {

		$this->models = $this->models->where('show_in->'.$place	, true);
		
	}

	public function byStage ($stage) {

		$this->models = $this->models->where('stage', "=", (int) $stage);
		
	}

	public function getModels () {

		return $this->models;
	}

}
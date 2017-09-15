<?php

namespace Karolina\Team;


Class TeamRepository  {

    private $userRepository;

    public function __construct (\Karolina\User\UserRepository $userRepository) {

        $this->userRepository = $userRepository;
    }

	public function getTeam ($object_type, $object_id) {

    	$models = \Karolina\Database\Table\Team::where('object_id', $object_id)->where('object_type', $object_type)->get();

    	$team = new Team();

    	foreach ($models as $model) {

            $user = $this->userRepository->getById($model->user_id);
            $roles = json_decode($model->roles, true);

    		$team->addMember($user, $roles);


    	}

    	return $team;

	}

	public function saveTeam (Team $team, $objectType, $objectId) {

    	$teamMembers = $team->getMembers();

    	foreach ($teamMembers as $member) {

    		$langFieldModel = \Karolina\Database\Table\Team::updateOrCreate(
    		    [
    		    'object_type' => $objectType, 
                'object_id' => $objectId, 
    		    'user_id' => $member['user']->getId()
    		    ],
    		    [
    		    'roles' => json_encode($member['roles'])
    		    ]);


    	}

	}

}
<?php

namespace Karolina\User;
use Communicator\SignedJwt as Message;
use Karolina\Exception;
use Karolina\User\User;

Class UserEditor {

	private $user;
	private $userRepository;
	private $platform;

	public function __construct ($userRepository) {

		$this->userRepository = $userRepository;

	}

	public function setPlatform ($platform) {

		$this->platform = $platform;
	}

	public function setUser (User $user) {

		$this->user = $user;

	}

	public function updateSettings ($newSettings) {

		foreach ($newSettings as $variable => $value) {

			$this->user->setSetting($variable, $value);
		}

		$this->userRepository->save($this->user);

	}

	public function setUserById ($id) {

		$this->user = $this->userRepository->getById($id);

	}

	public function updateProfileImage ($imageFile) {

		$this->user->updateProfileImage($imageFile);
		$this->userRepository->save($this->user);
	}


	public function replaceLinks ($links) {

		$this->user->setLinks($links);
		$this->userRepository->save($this->user);

	}


	public function replaceSkills ($skills) {

		$this->user->setSkills($skills);
		$this->userRepository->save($this->user);

	}
	
	public function replaceTags ($tagIdCollection) {

		$tagRepository = new \Karolina\Tag\TagRepository();
		$tags = $tagRepository->getTagsFromTagIdCollection($tagIdCollection);
		$this->user->replaceTags($tags);
		$this->userRepository->save($this->user);

	}


	public function updateProfile ($profile) {

		if ($profile['shortDescription']) 
		$this->user->setProfileShortDescription($profile['shortDescription']);

		if ($profile['description']) 
		$this->user->setProfileDescription($profile['description']);

		if ($profile['firstName']) 
		$this->user->setFirstName($profile['firstName']);

		if ($profile['lastName']) 
		$this->user->setLastName($profile['lastName']);

		$this->userRepository->save($this->user);

	}


	private function getMessageClaimsFromToken ($token) {

        try {

	        $message = new Message($this->platform->getSecretKey());
        	
        } catch (Exception $e) {

    		return false;

        }

        try {

	        $message->fromTokenString($token);
	        return $message->readAll();
        	
        } catch (Exception $e) {

    		return false;    	
        }

	}


	public function updatePasswordWithToken ($email, $token, $newPassword) {




		// Read from token
		$messageClaims = $this->getMessageClaimsFromToken($token);
		$tokenUserId = $messageClaims['change_pass_for_user_id'];
		$userAccordingToToken = $this->userRepository->getById($tokenUserId);

		if (strtolower($userAccordingToToken->getEmail()) === strtolower($email)) {

			$this->setUserById($userAccordingToToken->getId());
			$this->user->setNewPassword($newPassword);
	
			$this->platform->limit("change-pass-with-token".sha1($token), 1, 86400);
			$this->userRepository->save($this->user);

		} else {

			throw new \Karolina\Exception('Email supplied does not match token.', 'error', 400);

		}

	}


	public function updatePassword ($newPassword, $oldPassword) {

		$this->user->setNewPassword($newPassword);
		$this->user->setOldPassword($oldPassword);

		$this->userRepository->save($this->user);

	}

}
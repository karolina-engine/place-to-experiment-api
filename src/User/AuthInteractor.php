<?php

namespace Karolina\User;
use Communicator\SignedJwt as Message;
use Respect\Validation\Validator as v;

Class AuthInteractor {

	private $password;
	private $identifier;
	private $userRepository;
	private $platform;
	private $notificationService;

	public function __construct () {


	}

	public function setPlatform (\Karolina\Platform $platform) {

		$this->platform = $platform;

	}

	public function setUserRepository ($repo) {

		$this->userRepository = $repo;

	}

	public function setNotificationService ($service) {

		$this->notificationService = $service;

	} 

	public function setEmailAndPassword ($email, $password) {

		if (!$password) {

			throw new \Karolina\Exception('Password must be provided.', 'missing_credentials', 400);

		}

		$this->setEmail($email);


		if (v::stringType()->length(6, 1260)->validate($password)) {

			$this->password = $password;

		} else {

			throw new \Karolina\Exception('Password must be provided and be more than 6 characters.', 'missing_credentials', 400);

		}


	}

	public function setEmail ($email) {

		if (!$email) {

			throw new \Karolina\Exception('Email must be provided.', 'missing_credentials', 400);

		}

		if (v::email()->validate($email)) {

			$this->identifier = $email;

		} else {

			throw new \Karolina\Exception('Not a valid e-mail', 'missing_credentials', 400);

		} 


	}

	public function resetPassword () {

		$user = $this->getUserByIdentifier();
		$notification = $this->notificationService;

		// Create password reset auth token
		$token = $this->getPasswordResetToken($user);

		$notification->setSubject("Password reset for ".$this->platform->conf('name'));
		$data['reset_token'] = $token;
		$data['platform_name'] = $this->platform->conf('name');
		$data['reset_url'] = $this->platform->getPasswordResetUrl();

		$notification->setBodyFromTemplate('password_reset', $data);
		$notification->setSingleRecipient($user->getEmail());
		$notification->send();

	}


	private function getPasswordResetToken ($user, $duration = 60 * 60) { // duration default one hour

	        // Create an authorization
	        $message = new Message($this->platform->getSecretKey(), $duration); 
	        $message->write('change_pass_for_user_id', $user->getId());
	        $token = $message->getTokenString();
	        return $token;

	}


	private function getUserByIdentifier () {

		return $this->userRepository->getByIdentifier($this->identifier);

	}

	private function getAuthenticatedUser () {


		try {
	
			$user = $this->getUserByIdentifier();

		} catch (\Exception $e) {

			// This is not true
			throw new \Karolina\Exception('User does not exist.', 'wrong_credentials', 401);

		}


		if ($user->isAuthentic($this->identifier, $this->password)) {

			return $user;

		} else {

			throw new \Karolina\Exception('Password or e-mail incorrect, or user does not exist.', 'wrong_credentials', 401);

		}


	}

	public function getUserFromBearerToken ($token) {

		$messageClaims = $this->getMessageClaimsFromToken($token);
		$userId = $messageClaims['user_id'];
		$user = $this->userRepository->getById($userId);
		return $user;

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


	public function getBearerTokenIfAuthenticated ($duration = 43200) { // duration default twelve hours

		if ($user = $this->getAuthenticatedUser()) {

	        // Create an authorization
	        $message = new Message($this->platform->getSecretKey(), $duration); 
	        $message->write('user_id', $user->getId());
	        $token = $message->getTokenString();
	        return $token;

		}


	}


}
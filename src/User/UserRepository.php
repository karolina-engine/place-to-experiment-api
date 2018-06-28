<?php 

namespace Karolina\User;

use Karolina\Language\Field;
use Karolina\KarolinaException;
use Karolina\Models;
use Karolina\User\Profile;
use Karolina\User\User;
use Ramsey\Uuid\Uuid;
use Communicator\SignedJwt as Message;

Class UserRepository {
    
    private $ci;
    
    public function __construct ($ci = false) {
        
        if ($ci) {
            
            $this->ci = $ci;

        }
        
    }

    public function getAll () {
        $eloquentModels = \Karolina\Database\Table\User::all();

        $users = array();

        foreach ($eloquentModels as $model) {

            $user = $this->getFromEloquentModel($model);
            $users[] = $user;
        }

        return $users;

    }

    private function generateRandomUsername () {

        return Uuid::uuid4()->toString();

    }

    public function createNew (User $user) {

    
        // Make extra sure that there is no other e-mail address like this registered
        $alreadyRegistered = \Karolina\Database\Table\User::withoutGlobalScope('enabled')->where('email', $user->getEmail())->count();

        if ($alreadyRegistered) {

            throw new KarolinaException(
                'A user with this e-mail address already exists.',
                500,
                null,
                "error"
            );
               
        }

        $profileModel = new \Karolina\Database\Table\Profile();
        $userModel = new \Karolina\Database\Table\User();

        $userModel->password = $user->getHashString();
        $userModel->hash_type = $user->getHashType();            
        $userModel->group_id = $user->getGroupId();            
        $userModel->ip_address = $_SERVER['REMOTE_ADDR'];            
        $userModel->username = $this->generateRandomUsername();            
        $userModel->email = $user->getEmail();            
        $userModel->created_on = time();            
        $userModel->active = true;            

        $stuffedUserModel = $this->userToUserModel($user, $userModel);
        $stuffedUserModel->save();


        $stuffedProfileModel = $this->userToModel($user, $profileModel);
        $stuffedProfileModel->user_id = $stuffedUserModel->id;
        $stuffedProfileModel->save();

        // Create an authorization
        $message = new Message(getenv('platform_secret_key'), 43200); // twelve hours
        $message->write('user_id', $stuffedUserModel->id);
        $token = $message->getTokenString();

        return $token;



    }

    private function getClaimsFromMessage ($token) {

        $message = new \Communicator\SignedJwt(getenv('platform_secret_key'));
        $message->fromTokenString($token);
        $claims = $message->readAll();
        return $claims;
        
    }

    private function createFromEmail ($email, $name = false) {
        
        $username = $this->generateRandomUsername();
        $password = bin2hex(openssl_random_pseudo_bytes(16));


        if ($name) {
            $additional_data['first_name'] = $name['first_name'];
            $additional_data['last_name'] = $name['last_name'];

        } else {
            $additional_data['first_name'] = "Unknown";
            $additional_data['last_name'] = "User";

        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {

            $user_id = $this->ci->ion_auth->register($username, $password, $email, $additional_data);

            return $user_id;

        } else {

            throw new KarolinaException("Error creating user. Invalid e-mail.");
            
        }        
        
    }
    
    public function findOrCreateFromEmail ($email, $name) {

            // Incredibly stupid hack to add space after email, some bug with active records?
            $user_email = $email.' '; 
            $userModel = $this->ci->ion_auth->get_user_by_email($user_email);

            if (empty($userModel)) {
                
                $userId = $this->createFromEmail($email, $name);
                $userModel = $this->ci->ion_auth->get_user($userId);
                
            }  
            
            return $this->getFromUserModel($userModel);
        
    }

    private function getFromEloquentModel ($userEloquentModel) {

        $profileModel = $userEloquentModel->profile;

        if ($profileModel) {

            $user = new User($userEloquentModel->id, $userEloquentModel->email, $profileModel->first_name, $profileModel->last_name);

            if ($userEloquentModel->group_id === 1) {

                $user->makeAdmin();
            }

            $user->setHashString($userEloquentModel->password);

            if ($userEloquentModel->hash_type === "ion") {

                $hasher = new \Karolina\User\Hashers\IonAuthLegacyHasher();

            } else if ($userEloquentModel->hash_type === "native") {

                $hasher = new \Karolina\User\Hashers\NativeHasher();

            } else {

                $hasher = new \Karolina\User\Hashers\NativeHasher();

            }
            
            $user->setHasher($hasher);

            if ($profileModel->links) {
                $user->setLinks(json_decode($profileModel->links, true));

            }

            $profile = new Profile();


            // Retrieve and set image collection
            if ($profileModel->images) {
                $imagesData = json_decode($profileModel->images, true);
                $user->setImageCollectionFromDocument($imagesData);
            }

            // Retrieve and set tags
            if ($profileModel->tags) {

                $tagRepository = new \Karolina\Tag\TagRepository();
                $tags = $tagRepository->getTagsFromTagIdCollection(json_decode($profileModel->tags, true));
                $user->replaceTags($tags);            

            }


            $profile->setShortDescription($profileModel->description);

            // Is it stored as JSON?
            if (@json_decode($profileModel->content)) {
                $profile->setDescription(new Field($profileModel->content, 'json'));
            } else {
                $profile->setDescription(new Field($profileModel->content, 'html'));                
            }

            $user->setSettingsGroup($this->getSettingsGroup($userEloquentModel->settings));

            $user->setProfile($profile);
            
            $user->setSkills($this->getSkills($profileModel->id));

            $user->setNetwork($this->getNetwork($userEloquentModel->id));
            

        } else {

            $user = new User($userEloquentModel->id, $userEloquentModel->email, 'Missing', 'profile');

            

        }
        return $user;

    }

    private function getSkills ($profileId) {

        $skillsRecords = \Karolina\Database\Table\ProfilesSkills::where('profile_id', $profileId)->get();

        $skills = array();
        foreach ($skillsRecords as $record) {

            if (isset($record->skill->name)) {
                $skills[] = $record->skill->name;
            }

        }
        return $skills;

    }

    public function getFromUserModel ($userModel) {

        $userEloquentModel = \Karolina\Database\Table\User::where('id', $userModel->id)->firstOrFail();

        return $this->getFromEloquentModel($userEloquentModel);

        

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



    public function userToUserModel (User $user, $model) {

        $model->settings = json_encode($user->getAllSettings());

        return $model;   

    }

    public function userToModel (User $user, $model) {

        $model->description = $user->getProfileShortDescription();
        $model->content = $user->getProfileDescription();
        $model->first_name = $user->getFirstName();
        $model->last_name = $user->getLastName();

        return $model;   

    }

    public function getWithToken ($authHeader) {

        $split = explode(" ", $authHeader);
        $token = $split[1];

        $claims = $this->getClaimsFromMessage($token);

        if (isset($claims['user_id'])) {

            $user = $this->getById($claims['user_id']);

        } else if (isset($claims['user_email'])) {

            // Incredibly stupid hack to add space after email, some bug with active records?

            $user = $this->getByIdentifier($claims['user_email']);


        } else {

            return false;

        }

        return $user;
    }

    public function getById($userId) {

        $userEloquentModel = \Karolina\Database\Table\User::where('id', $userId)->firstOrFail();
        return $this->getFromEloquentModel($userEloquentModel);

    }

    private function saveSkills ($user, $profileModel) {



        $skillsArray = $user->getSkills();
        $skillIdArray = array();

        foreach ($skillsArray as $skillName) {

            $skillRecord = \Karolina\Database\Table\Skill::firstOrCreate(['name' => $skillName]);
            $skillIdArray[] = array('skill_id' => $skillRecord->skill_id, 'profile_id' => $profileModel->id);

        }

        // Remove current
        $deletedRows = \Karolina\Database\Table\ProfilesSkills::where('profile_id', $profileModel->id)->delete();

        foreach ($skillIdArray as $insert) {

            $skillRecord = new \Karolina\Database\Table\ProfilesSkills();
            $skillRecord->skill_id = $insert['skill_id'];
            $skillRecord->profile_id = $insert['profile_id'];
            $skillRecord->save();

        }

    }


    public function save (User $user) {

        $profileModel = \Karolina\Database\Table\Profile::where('user_id', $user->getId())->firstOrFail();
        $userModel = \Karolina\Database\Table\User::where('id', $user->getId())->firstOrFail();

        // New password?
        if ($user->getNewPassword() != null) {

            $userModel->password = $user->getHashString();
            $userModel->hash_type = $user->getHashType();            

        }

        $this->saveSkills($user, $profileModel);


        $profileModel->images = json_encode($user->getImageCollectionDocument());
        $profileModel->links = json_encode($user->getLinks());

        // Tags
        $tagIdArray = array();
        foreach ($user->getTags() as $tag) {
            $tagIdArray[] = $tag->getId();
        }
        $profileModel->tags = json_encode($tagIdArray);

		$profileModel->document = $this->userToJsonDocument($user);

        $stuffedProfileModel = $this->userToModel($user, $profileModel);
        $stuffedProfileModel->save();


        $stuffedUserModel = $this->userToUserModel($user, $userModel);
        $stuffedUserModel->save();



        return true;

    }

	private function userToJsonDocument (User $user) {

		$doc['last_updated'] = time();
		$doc['user_id'] = (string) $user->getId();
		$doc['first_name'] = (string) $user->getFirstName();
		$doc['last_name'] = (string) $user->getLastName();
		$doc['short_description'] = (string) $user->getProfileShortDescription()->getValue();
		$doc['links'] = $user->getLinks();
		$doc['tags'] = $user->getTagsDocument();
		$doc['image'] = $user->getImageCollectionDocument();

		return json_encode($doc);


	}

    public function getAllDocuments ($filterArguments) {
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

		$models = \Karolina\Database\Table\Profile::get();

		foreach ($models as $model) {

			$user = $this->getFromUserModel($model);
			$document = $this->userToJsonDocument($user);
			$model->document = $document;
			$model->save();
		}

		return count($models);

	}

	private function getFilteredModels ($filterArguments) {

		$filter = new UserRepositoryFilter(
			new \Karolina\Database\Table\Profile()
		);

		$filter->fromArguments($filterArguments);

		return $filter->getModels();

	}


    public function getByIdentifier ($identifier) { // email

        try {
    
            $userEloquentModel = \Karolina\Database\Table\User::where('email', $identifier)->firstOrFail();

        } catch (\Exception $e) {

            throw new \Karolina\Exception('Could not find user', 'no_user_found', 404);

        }        

        return $this->getFromEloquentModel($userEloquentModel);

    }

    public function getLoggedinWithCookie () {

            throw new \Karolina\Exception("Cookie login disabled", "login_method_disabled");

    }


    public function getNetwork ($userId) {

        $networkRepository = new \Karolina\Network\NetworkRepository();
        $network = $networkRepository->getNetwork('users', $userId);
        return $network;

    }

    public function saveNetwork ($network) {

        $networkRepository = new \Karolina\Network\NetworkRepository();
        $networkRepository->saveNetwork($network);


    }

    public function getUserPreviewsByIds ($idsArray) {

        $models = \Karolina\Database\Table\Profile::findMany($idsArray);

        $previews = array();
        foreach ($models as $model) {

            $preview = $this->profileModelToUserPreview($model);
            $previews[] = $preview;
        }

        return $previews;
    }

    private function profileModelToUserPreview ($model) {

        $userPreview = new UserPreview();
        $userPreview->setFirstName($model->first_name);
        $userPreview->setLastName($model->last_name);
        $userPreview->setDescription($model->description);
        $userPreview->setProfileImage($model->profile_pic);
        $userPreview->setUserId($model->user_id);

        return $userPreview;


    }


    public function getUserStats () {

        $stats['count'] = \Karolina\Database\Table\User::count();
        return $stats;

    }

}
<?php

namespace Karolina\User;
use Karolina\Exception;
use Karolina\KarolinaException;
use Karolina\User\Profile;
use Karolina\Network\Root;
use Respect\Validation\Validator as v;

Class User {

    private $email;
    protected $id;
    private $firstName = "Unknown";
    private $lastName = "Anonymous";
    private $newPassword = null;
    private $oldPassword = null;
    private $profile;
    private $isAdmin = false;

    private $settings;  use \Karolina\Setting\SettingTrait;
    private $images = array(); use \Karolina\Image\ImageCollectionTrait;

    private $hasher;
    private $hashString;

    private $groupId = 2;
    private $links = array();

    private $tags = array();

    private $skills;

    private $network;

    // For network relationships
    public function getNetworkType () {

    	return "users";
    }

	public function __construct($id, $email, $firstName, $lastName)

	{
	    $this->setEmail($email);
		$this->id = $id;
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setProfile(new Profile());
		$this->setProfileShortDescription('');
		$this->skills = new \Karolina\User\Skills();
        $this->network = new \Karolina\Network\Root(
            new \Karolina\Network\Node('experiments')
            );

		$this->settings = new \Karolina\Setting\SettingsGroup();

	}

	public function setAdminOfTags ($tagIds) {

        $this->setSetting('admin_of_tags', $tagIds);

    }


	public function setNetwork (Root $network) {

		$this->network = $network;
	}


	public function isSame ($user) {

		if ($user and $user->getId() === $this->getId()) {
			return true;
		}

		return false;

	}

	public function setSkills (array $skills) {

		$this->skills->set($skills);

	}

	public function getSkills () {

		return $this->skills->getAll();
	}

    public function setLinks (array $links) {

        $newLinks = array();
        foreach ($links as $link) {

            $newLinks[] = new \Karolina\Content\Link($link['url'], $link['title']);

        }

        $this->links = $newLinks;

    }

    public function getLinks () {

        $linkDocs = array();
        foreach ($this->links as $link) {

            $doc = new \Karolina\Content\LinkDocument($link);
            $linkDocs[] = $doc->get();

        }
        return $linkDocs;

    }


    /*
        Tags
    */

    public function replaceTags ($tags) {

        $this->tags = array();

        foreach ($tags as $tag) {

            $this->addTag($tag);
        }

    }

    public function addTag(\Karolina\Tag\Tag $tag) {

        $this->tags[$tag->getId()] = $tag;
    }

    public function removeTag($tagId) {

        unset($this->tags[$tagId]);

    }

    public function getTags () {

        return $this->tags;

    }

    public function getTagsDocument () {

        $doc = array();
        foreach ($this->tags as $tag) {
            $doc[$tag->getId()] = $tag->getLabelDocument();
        }

        return $doc;

    }



	public function getGroupId () {

		return $this->groupId;
	}

	public function getHashString () {

		return $this->hashString;

	}

	public function setHashString ($hash) {

		$this->hashString = (string) $hash;

	}

	public function getHashType () {

		return $this->hasher->getHashType();
	}

	public function setHasher (\Karolina\User\Hashers\HasherInterface $hasher) {

		$this->hasher = $hasher;

	}

	public function isAuthentic ($identifier, $password) {

		if ($identifier != $this->getEmail()) {

			throw new Exception('Trying to authenticate a user with the wrong e-mail address.');

		}

		if (!$this->hasher) {

			throw new Exception("Can't authenticate user. No hasher is set.");

		}

		if ($this->hasher->check($password, $this->hashString)) {

			return true;

		} else {

			return false;
		}



	}

	public function updateProfileImage ($imageFile) {

		$imageCollectionData['profile']['filename'] = $imageFile;
		$this->setImageCollectionFromDocument($imageCollectionData);

	}

	public function getProfileImage () {

		return $this->getImage('profile');

	}

	public function isAdmin () {

		return $this->isAdmin;

	}

	public function makeAdmin () {

		$this->isAdmin = true;

	}

	public function setProfile (Profile $profile) {

		$this->profile = $profile;

	}

	public function setProfileDescription ($content) {

		$this->profile->setDescription($content);

	}

	public function setProfileShortDescription ($content) {

		$this->profile->setShortDescription($content);

	}


	public function getProfileDescription () {

		return $this->profile->getDescription();


	}

	public function getProfileShortDescription () {

		return $this->profile->getShortDescription();

	}


	public function setFirstName ($name) {

		if (v::stringType()->length(1, 500)->validate($name)) {

			$this->firstName = $name;

		} else {

			throw new KarolinaException(
				'First name must be at least 1 characters',
				500,
				null,
				"invalid_arguments"
			);

		}

	}

	public function setLastName ($name) {

		if (v::stringType()->length(1, 500)->validate($name)) {

			$this->lastName = (string) $name;

		} else {

			throw new KarolinaException(
				'Last name must be at least 1 characters',
				500,
				null,
				"invalid_arguments"
			);

		}

	}

	public function setNewPassword ($password) {

		// Set hasher
		$this->setHasher(new \Karolina\User\Hashers\NativeHasher());

		if (v::stringType()->length(6, 1260)->validate($password)) {

			$this->newPassword = (string) $password;

			$this->setHashString(
				$this->hasher->hash($password)
				);

		} else {

			throw new KarolinaException(
				'Password must be at least 6 characters',
				500,
				null,
				"password_too_short"
			);

		}

	}

	public function setEmail ($email) {

		if (v::email()->validate($email)) {

			$this->email = (string) $email;

		} else {

			throw new KarolinaException(
				'E-mail address is not valid.',
				500,
				null,
				"invalid_email"
			);

		}

	}

	public function getNewPassword () {

		return $this->newPassword;

	}

	public function getOldPassword () {

		return $this->oldPassword;

	}

	public function setOldPassword ($password) {

		$this->oldPassword = $password;

	}


	public function getEmail () {

	    return $this->email;

	}

	public function getId () {

	    return $this->id;

	}

	public function getName () {

		return $this->firstName." ".$this->lastName;

	}

	public function getFirstName () {

		return $this->firstName;

	}

	public function getLastName () {

		return $this->lastName;

	}


}

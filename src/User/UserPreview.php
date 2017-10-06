<?php
/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 25/07/17
 * Time: 14:20
 */

namespace Karolina\User;

use Karolina\User\User as User;

class UserPreview
{
    private $firstName = "Anonimous";
    private $lastName = "User";
    private $profileImage;
    private $description = "";
    private $userId;


    public function setFromUser(User $user)
    {
        $this->setFirstName($user->getFirstName());
        $this->setLastName($user->getLastName());
        $this->setDescription($user->getProfileDescription());
        $this->setProfileImage($user->getProfileImage());
        $this->setUserId($user->getId());
    }


    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return UserPreview
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return UserPreview
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * @param mixed $profileImage
     * @return UserPreview
     */
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return UserPreview
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}

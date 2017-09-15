<?php
/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 25/07/17
 * Time: 19:57
 */

namespace Karolina\API\v1;


use Karolina\User\UserPreview;

class UserPreviewResponse
{

    private $userPreview;
    private $imgStorageUrl;

    public function __construct(UserPreview $userPreview, $imgStorageUrl)
    {

        $this->userPreview = $userPreview;
        $this->imgStorageUrl = $imgStorageUrl;

    }

    public function get () {

        $user = $this->userPreview;
        $response = array();

        $response['user_id'] = $user->getUserId();
        $response['first_name'] = $user->getFirstName();
        $response['last_name'] = $user->getLastName();
        $response['short_description'] = $user->getDescription();
        $response['links'] = array();
        $response['tags'] = array();

        if ($user->getProfileImage()) {
            $response['image'] = $this->imgStorageUrl."/".$user->getProfileImage();
            $response['image_filename'] = $user->getProfileImage();
        } else {
            $response['image'] = NULL;
            $response['image_filename'] = NULL;
        }


        return $response;
    }

}
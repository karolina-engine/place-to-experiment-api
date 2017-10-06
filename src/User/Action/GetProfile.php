<?php
namespace Karolina\User\Action;

class GetProfile
{
    private $currentUser = false;
    private $userRepository;
    private $imgStorageUrl;
    private $response;
    private $user;
    private $platform;

    public function __construct($currentUser, $userRepisitory, $imgStorageUrl, $platform = false)
    {
        $this->currentUser = $currentUser;
        $this->userRepository = $userRepisitory;
        $this->imgStorageUrl = $imgStorageUrl;
        $this->platform = $platform;
    }


    public function forUserId($userId)
    {
        if ($userId === "me") {
            if (!$this->currentUser) {
                throw new \Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
            } else {
                $user = $this->currentUser;
            }
        } else {
            $userRepository = $this->userRepository;
            $user = $userRepository->getById($userId);
        }

        $this->user = $user;

        $imgStorageUrl = $this->imgStorageUrl;
        $currentUser = $this->currentUser;

        foreach ($user->getImageCollectionDocument() as $contentKey => $imageData) {
            $response['profile']['image_collection'][$contentKey]['filename'] = $imageData['filename'];
            $response['profile']['image_collection'][$contentKey]['url'] = $imgStorageUrl."/".$imageData['filename'];
        }


        $response['profile']['email'] = null;

        if ($currentUser and ($currentUser->isAdmin() or $currentUser->isSame($user))) {
            $response['profile']['email'] = $user->getEmail();
        }

        $response['profile']['experiments'] = array();

        $response['profile']['links'] = $user->getLinks();
        $response['profile']['skills'] = $user->getSkills();

        $tagResponse = new \Karolina\API\v1\TagsResponse($user->getTagsDocument(), 'EN');

        $response['profile']['tags'] = $tagResponse->get();
        $response['profile']['user_id'] = $user->getId();
        $response['profile']['first_name'] = $user->getFirstName();
        $response['profile']['last_name'] = $user->getLastName();
        $response['profile']['short_description'] = $user->getProfileShortDescription()->getValue();
        $response['profile']['long_description'] = $user->getProfileDescription()->getAsHTML();

        $this->response = $response;

        return $this;
    }

    public function withExperiments($experimentInteractor)
    {
        $experimentsPreviews = $experimentInteractor->getAllPreviewDocumentsByTeamMember($this->user->getId());
        foreach ($experimentsPreviews as $preview) {
            $this->response['profile']['experiments'][] = (new \Karolina\API\v1\ExperimentResponse($preview, 'EN', $this->platform))->getPreview();
        }

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }
}

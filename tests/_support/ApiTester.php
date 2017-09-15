<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function getTokenResponse($overrideEmail = false,
    $overridePassword = false)
    {
        $role = $this->getRole();
        switch ($role) {
            case 'visitor':
                $email = visitor_email;
                $password = visitor_password;
                break;
            case 'creator':
                $email = creator_email;
                $password = creator_password;
                break;
            case 'admin':
                $email = admin_email;
                $password = admin_password;
                break;
        }
        if ($overrideEmail) {
            $email = $overrideEmail;
        }
        if ($overridePassword) {
            $password =  $overridePassword;
        }

        $currentAuthorization = $this->getAuthorization();
        $this->setAuthorization(false);

        $endpoint = '/users/tokens/';
        $fields = ['email' => $email, 'password' => $password];

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        // $this->comment($this->grabResponse());

        // if we are using a predefined role we check if user exists and create it if it doesn't
        if (($role == 'visitor' || $role == 'creator') && ($overrideEmail == false && $overridePassword == false)) {
            $responseArray = json_decode($this->grabResponse(), true);
            try {
                $token = $responseArray['token'];
            } catch (Exception $e) {
                switch ($role) {
                    case 'visitor':
                        $firstName = visitor_first_name;
                        $lastName = visitor_last_name;
                        break;
                    case 'creator':
                        $firstName = creator_first_name;
                        $lastName = creator_last_name;
                        break;
                }
                $this->registerNewUserResponse(['email' => $email, 'password' => $password, 'first_name' => $firstName, 'last_name' => $lastName]);
            }
        }

        //$this->comment($this->grabResponse());

        $this->setAuthorization($currentAuthorization);
    }

    public function registerNewUserResponse($fields)
    {
        $endpoint = '/users/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
    }

    public function createExperimentResponse($overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function getExperimentResponse($experimentId, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
        }

        $endpoint = '/experiments/'.$experimentId.'/'.lang;

        $this->setHeaders($this);
        $this->sendGET($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
        }
    }

    public function getExperimentsPreviewResponse($overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(false);
        }

        $endpoint = '/experiments/preview/'.lang;

        $this->setHeaders($this);
        $this->sendGET($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
        }
    }

    public function getAvailableTagsForExperimentsResponse($overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(false);
        }

        $endpoint = '/experiments/tags/'.lang;

        $this->setHeaders($this);
        $this->sendGET($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
        }
    }

    public function getUsersExperimentsPreviewResponse($userId, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(false);
        }

        $endpoint = '/users/'.$userId.'/experiments/preview/'.lang;

        $this->setHeaders($this);
        $this->sendGET($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
        }
    }

    public function getMyProfileResponse($overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('admin');
        }

        $endpoint = '/users/me/profile';

        $this->setHeaders($this);
        $this->sendGET($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function setTagsForUserResponse($fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/users/me/tags/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }


 public function setSettingForExperimentResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false){
        if ($overrideRoleAndAuthorization){
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/settings/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, array('settings' => $fields) );

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization){
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function setTagsForExperimentResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/tags/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function setLinksForExperimentResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/links/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function updateExperimentSettingsResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/settings/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function setLinksForUserResponse($fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/users/me/links/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }
    public function setSkillsForUserResponse($fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/users/me/skills/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function updateExperimentFundingResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('admin');
        }

        $endpoint = '/experiments/'.$experiment_id.'/funding/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function addExperimentLikeResponse($experiment_id, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/likes/';

        $this->setHeaders($this);
        $this->sendPOST($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function removeExperimentLikeResponse($experiment_id, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/likes/';

        $this->setHeaders($this);
        $this->sendDELETE($endpoint);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function updateExperimentLanguageResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/language/'.lang.'/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function updateExperimentCustomLanguageResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/custom_language/'.lang.'/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }

    public function updateExperimentImageCollectionResponse($experiment_id, $fields, $overrideRoleAndAuthorization = false)
    {
        if ($overrideRoleAndAuthorization) {
            $currentAuthorization = $this->getAuthorization();
            $this->setAuthorization(true);
            $currentRole = $this->getRole();
            $this->setRole('creator');
        }

        $endpoint = '/experiments/'.$experiment_id.'/image_collection/';

        $this->setHeaders($this);
        $this->sendPATCH($endpoint, $fields);

        //$this->comment($this->grabResponse());
        if ($overrideRoleAndAuthorization) {
            $this->setAuthorization($currentAuthorization);
            $this->setRole($currentRole);
        }
    }
}

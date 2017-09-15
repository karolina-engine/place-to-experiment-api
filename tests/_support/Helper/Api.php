<?php
namespace Helper;

use Codeception\Util\JsonType;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{
    private $role;
    private $status;
    private $authorization;
    private $overrideToken;

    /**
     * Setter for the $role variable. Valid options are 'visitor', 'creator', 'admin'.
     * @param $role: The role to set.
     */
    //TODO: implement a check to ensure a role is valid.
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Getter for the $role variable.
     *
     * @returns $role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Setter for the $status variable. Valid options are 'success', 'failure'.
     * @param $status: The status to set.
     */
    //TODO: implement a check to ensure a role is valid.
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Getter for the $status variable.
     *
     * @returns $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Setter for the $authorization variable. Valid options are TRUE, FALSE.
     * @param $authorization: The $authorization to set.
     * @param $overrideToken: Optional. Set a token to override the correct token. Used to test response with wrong authentication. Only works if $authorization is TRUE.
     */
    public function setAuthorization($authorization, $overrideToken = false)
    {
        if ($overrideToken) {
            $this->overrideToken = $overrideToken;
        } else {
            $this->authorization = $authorization;
            $this->overrideToken = false;
        }
    }

    /**
     * Getter for the $authorization variable.
     *
     * @returns $overrideToken if it was set in setAuthorization(). Otherwise returns $authorization.
     */
    public function getAuthorization()
    {
        if ($this->overrideToken) {
            return $this->overrideToken;
        } else {
            return $this->authorization;
        }
    }

    /**
     * Sets the headers for the next request. The headers to set depend on the value of the $authorization and the $role variables.
     * @param $I: The tester object.
     */
    public function setHeaders($I)
    {
        $I->deleteHeader('Content-Type'); // hack to avoid sending duplicated headers
        $I->deleteHeader('Authorization'); // hack to avoid sending duplicated headers

        $I->haveHttpHeader('Content-Type', 'application/json');
        if ($this->getAuthorization()) {
            if ($this->getAuthorization() === true) {
                $I->getTokenResponse();
                $token = $I->decodeResponse($I, 'token');
            } else {
                $token = $this->getAuthorization();
            }
            $I->haveHttpHeader('Authorization', 'Bearer '.$token);
        }
    }

    /**
     * Checks the response code and format. The expected status code depends on the value of the $status variable.
     * @param $I: The tester object.
     * @param $checkType: Specifies the type of format to check for.
     */
    public function checkResponse($I, $checkType = false)
    {
        if ($this->getStatus() === 'success') {
            $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        } elseif ($this->getStatus() === 'failure') {
            $I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK); // not 200
        }
        if ($checkType) {
            switch ($checkType) {
                case 'token':
                    $I->seeResponseMatchesJsonType([
                        'token'    => ['string' => 'string'],
                        'status'   => 'string'
                    ]);
                    break;
                case 'message':
                    $I->seeResponseMatchesJsonType([
                        'message'  => 'string',
                        'status'   => 'string'
                    ]);
                    break;
                case 'create_experiment':
                    $I->seeResponseMatchesJsonType([
                        'experiment' => ['experiment_id' => 'string'],
                        'status'   => 'string'
                    ]);
                    break;
                case 'get_experiment':
                    $I->seeResponseMatchesJsonType([
                        'experiment' => [
                            'stage' => 'integer',
                            'experiment_id' => 'string',
                            'custom_language' => 'array|boolean',
                            'language' => 'array|boolean',
                            'image_collection' => 'array',
                            'team' => 'array',
                            'disabled' => 'boolean',
                            'show_in' => 'array|null',
                            'funding' => [
                                'goal' => 'integer|null',
                                'currency' => 'string',
                                'sources' => [
                                    'state' => [
                                        'raised' => 'integer'
                                    ],
                                    'organizations' => [
                                        'raised' => 'integer'
                                    ],
                                    'crowd' => [
                                        'raised' => 'integer',
                                        'api' => 'string|null',
                                        'campaign_id' => 'string|null'
                                    ]
                                ]
                            ],
                            'tags' => 'array'
                        ],
                        'acl' => 'array',
                        'status' => 'string'
                    ]);
                    break;
                case 'get_experiments_preview':
                    $I->seeResponseMatchesJsonType([
                        'previews' => 'array',
                        'status'    => 'string'
                    ]);
                    break;
                case 'get_tags_for_experiments':
                    $I->seeResponseMatchesJsonType([
                        'tags' => 'array',
                        'status'    => 'string'
                    ]);
                    break;
                //TODO: implement the rest of the validations
            }
        }
    }

    /**
     * Converts a JSON formatted response into a variable or an array.
     * @param $I: The tester object.
     * @param $key: Optional. The key that specifes which decoding to use. If you do not provide this parameter the return value is an array.
     *
     * @return $decodedResponse: A variable decoded from the JSON response according to the provided key. If the key is not set, returns $responseArray (An array decoded from the JSON response).
     */
    public function decodeResponse($I, $key = false)
    {
        $responseArray = json_decode($I->grabResponse(), true);
        if ($key) {
            switch ($key) {
                case 'token':
                    $decodedResponse = $responseArray['token']['string'];
                    break;
                case 'message':
                    if ($this->getStatus() === 'success') {
                        if (array_key_exists('message', $responseArray)) {
                            $decodedResponse = $responseArray['message'];
                        } else {
                            $decodedResponse = 'Response does not contain the message key.';
                        }
                    } else {
                        $decodedResponse = $responseArray['message'];
                    }
                    break;
                case 'experiment_id':
                    $decodedResponse = $responseArray['experiment']['experiment_id'];
                    break;
                case 'preview_experiment_id':
                    $decodedResponse = $responseArray['previews'][0]['experiment_id'];
                    break;
                case 'tag_id':
                    $decodedResponse = $responseArray['tags'][0]['id'];
                    break;
                case 'user_id':
                    $decodedResponse = $responseArray['profile']['user_id'];
                    break;
                case 'experiment_tags':
                    $decodedResponse = $responseArray['experiment']['tags'];
                    break;
                case 'experiment_links':
                    $decodedResponse = $responseArray['experiment']['links'];
                    break;
                case 'experiment_geographic_location':
                    $decodedResponse = $responseArray['experiment']['geographic_location'];
                    break;
                case 'users_links':
                    $decodedResponse = $responseArray['profile']['links'];
                    break;
                case 'users_tags':
                    $decodedResponse = $responseArray['profile']['tags'];
                    break;
                case 'experiment_funding':
                    $decodedResponse = $responseArray['experiment']['funding'];
                    break;
                case 'my_relationships':
                    $decodedResponse = $responseArray['my_relationships'];
                    break;
                case 'experiment_disabled':
                    $decodedResponse = $responseArray['experiment']['disabled'];
                    break;
                case 'experiment_show_in':
                    $decodedResponse = $responseArray['experiment']['show_in'];
                    break;
                case 'experiment_stage':
                    $decodedResponse = $responseArray['experiment']['stage'];
                    break;
                case 'experiment_stage':
                    $decodedResponse = $responseArray['experiment']['stage'];
                    break;
                case 'experiment_language':
                    $decodedResponse = $responseArray['experiment']['language'];
                    break;
                case 'experiment_custom_language':
                    $decodedResponse = $responseArray['experiment']['custom_language'];
                    break;
                case 'experiment_image_collection':
                    $decodedResponse = $responseArray['experiment']['image_collection'];
                    break;
                case 'status':
                    $decodedResponse = $responseArray['status'];
                    break;
                    // in case we provide a key that doesn't match (probably missing the implementation).
                    return $responseArray;
            }
            return $decodedResponse;
        } else {
            return $responseArray;
        }
    }
}

<?php
use Codeception\Util\Fixtures;

/**
 * Contains tests related to the /users/* endpoint.
 * @run Normal: codecept run api UserCest
 * @run Single: codecept run api UserCest:<functionName>$ (note: runs only the test with <functionName>)
 * @run Partial: codecept run api UserCest:<functionName> (note: runs any tests that begin with <functionName>)
 * @run With steps: codecept run api UserCest --steps (can be combined with Single or Partial)
 * @run With debug: codecept run api UserCest --debug (can be combined with Single or Partial)
 *
 * @test registerNewUser: Create a new user and verify that the new user can obtain a login token.
 * @test registerNewUserWithUsedEmail: [NEG] Create a new user with an email that has already been taken.
 * TODO @test changeMyPassword: Change a user's password and verify the new password.
 * TODO @test changeMyPasswordWithoutOldPassword: [NEG] Change a user's password without providing the old password.
 * TODO @test changeMyPasswordAndLoginWithOldPassword: [NEG] Change a user's password and try to login with the old password.
 * TODO @test sendEmailWithPasswordReset: Send an email with password reset token.
 * TODO @test changePasswordWithResetToken: Change a password using the password reset token.
 * @test getTokenAsCreator: Get a token for a creator user and verify the obtained token.
 * @test getTokenAsAdmin: Get a token for an admin user and verify the obtained token.
 * @test getTokenAsVisitor: Get a token for a visitor user and verify the obtained token.
 * @test getTokenAsVisitorWithWrongPassword: [NEG] Get a token for a visitor user providing a wrong password.
 * @test getTokenAsVisitorWithUnknownEmail: [NEG] Get a token for a visitor user providing a wrong email.
 * @test getTokenAsVisitorWithUnknownEmail: [NEG] Get a token for a visitor user providing a wrong email.
 * @test getUsersExperimentsPreviews: Get all the experiments that belong to a user.
 * TODO: @test getUsersPreviews: Get previews of all the users.
 * TODO: @test getUserProfile: Get a profile of a user.
 * TODO: @test getMyProfile: Get my profile.
 * TODO: @test updateMyProfile: Update my profile.
 * TODO: @test updateMyProfileImage: Update my profile image.
 * TODO: @test setMyTags: Set a user's tags.
 * TODO: @test setMyLinks: Set a user's links.
 */

class UserCest
{

    private function getExperimentId($I)
    {
        try {
            $experimentId = Fixtures::get('experimentId');
        } catch (Exception $e) {
            $experimentId = $this->setExperimentId($I);
        }
        return $experimentId;
    }

    private function setExperimentId($I)
    {
        $I->createExperimentResponse(true);
        $experimentId = $I->decodeResponse($I, 'experiment_id');
        Fixtures::add('experimentId', $experimentId);

        return $experimentId;
    }

    private function getUserId($I)
    {
        try {
            $userId = Fixtures::get('userId');
        } catch (Exception $e) {
            $userId = $this->setUserId($I);
        }
        return $userId;
    }

    private function setUserId($I)
    {
        $I->getMyProfileResponse(true);
        $userId = $I->decodeResponse($I, 'user_id');
        Fixtures::add('userId', $userId);

        return $userId;
    }

    private function getTestUserEmail($I)
    {
        try {
            $testUserEmail = Fixtures::get('testUserEmail');
        } catch (Exception $e) {
            $testUserEmail = $this->setTestUserEmail($I);
        }
        return $testUserEmail;
    }

    private function setTestUserEmail($I)
    {
        $date = new DateTime();
        $testUserEmail = 'test_user.'.$date->GetTimestamp().'@example.com';
        $testUserPassword = test_password;
        $testUserFirstName = test_first_name;
        $testUserLastName = test_last_name;

        $I->registerNewUserResponse(['email' => $testUserEmail, 'password' => $testUserPassword, 'first_name' => $testUserFirstName, 'last_name' => $testUserLastName]);

        Fixtures::add('testUserEmail', $testUserEmail);

        return $testUserEmail;
    }

    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    public function registerNewUser(ApiTester $I)
    {
        $date = new DateTime();
        $testUserEmail = 'test_user.'.$date->GetTimestamp().'@example.com';
        $testUserPassword = test_password;
        $testUserFirstName = test_first_name;
        $testUserLastName = test_last_name;

        $I->wantTo('succeed at registering a new user and getting a token for that user');

        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->registerNewUserResponse(['email' => $testUserEmail, 'password' => $testUserPassword, 'first_name' => $testUserFirstName, 'last_name' => $testUserLastName]);

        $I->checkResponse($I, 'token');
        $token = $I->decodeResponse($I, 'token');
        $I->comment('The obtained token is: '.$token);

        // test the user by getting a login token
        $I->getTokenResponse($testUserEmail, $testUserPassword);
        $newToken = $I->decodeResponse($I, 'token');

        // We cannot assume the token will be the same..
        // $I->assertSame($newToken, $token);
        // Instead, lets check length only
        $I->assertSame(strlen($newToken), strlen($token));

        // save this user to Fixtures to be reused
        Fixtures::add('testUserEmail', $testUserEmail);
    }

    public function registerNewUserWithUsedEmail(ApiTester $I)
    {
        $testUserPassword = test_password;
        $testUserFirstName = test_first_name;
        $testUserLastName = test_last_name;

        $I->wantTo('fail at registering a user with an email that is already taken');
        $testUserEmail = $this->getTestUserEmail($I);

        $I->setStatus('failure');
        $I->setAuthorization(false);

        $I->registerNewUserResponse(['email' => $testUserEmail, 'password' => $testUserPassword, 'first_name' => $testUserFirstName, 'last_name' => $testUserLastName]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    /**
     * @skip TODO implement
     */
    public function changeMyPassword(ApiTester $I)
    {
    }

    public function getTokenAsCreator(ApiTester $I)
    {
        $I->wantTo('succeed at getting a token as creator and test that token');

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getTokenResponse();

        $I->checkResponse($I, 'token');
        $token = $I->decodeResponse($I, 'token');
        $I->comment('The obtained token is: '.$token);

    }

    public function getTokenAsAdmin(ApiTester $I)
    {
        $I->wantTo('succeed at getting a token as admin and test that token');

        $I->setRole('admin');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getTokenResponse();

        $I->checkResponse($I, 'token');
        $token = $I->decodeResponse($I, 'token');
        $I->comment('The obtained token is: '.$token);

    }

    public function getTokenAsVisitor(ApiTester $I)
    {
        $I->wantTo('succeed at getting a token as visitor and test that token');

        $I->setRole('visitor');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getTokenResponse();

        $I->checkResponse($I, 'token');
        $token = $I->decodeResponse($I, 'token');
        $I->comment('The obtained token is: '.$token);

    }

    public function getTokenAsVisitorWithWrongPassword(ApiTester $I)
    {
        $I->wantTo('fail at getting a token as visitor with wrong password');

        $I->setRole('visitor');
        $I->setStatus('failure');
        $I->setAuthorization(false);

        $I->getTokenResponse(false, '12783495'); //provide wrong password

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    public function getTokenAsVisitorWithUnknownEmail(ApiTester $I)
    {
        $I->wantTo('fail at getting a token as visitor with wrong email');

        $I->setRole('visitor');
        $I->setStatus('failure');
        $I->setAuthorization(false);

        $I->getTokenResponse('danio.planincio@gmailo.como'); //provide wrong email

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    public function getUsersExperimentsPreviews(ApiTester $I)
    {
        $I->wantTo('succeed at getting user\'s experiments');
        $userId = $this->getUserId($I);

        $I->setRole('visitor');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getUsersExperimentsPreviewResponse($userId);

        $I->checkResponse($I, 'get_experiments_preview');
        $experimentId = $I->decodeResponse($I, 'preview_experiment_id');
        $I->comment('The ID of the first retrieved experiment is: '.$experimentId);
    }



    public function setTagsForUser(ApiTester $I)
    {
        $tagsToSet = [1, 2, 5];

        $I->wantTo('succeed at setting tags for user');

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->setTagsForUserResponse(['tags' => $tagsToSet]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);

        // test the set tags
        $I->getMyProfileResponse();
        $tags = $I->decodeResponse($I, 'users_tags');

        // convert array to match POST body
        $tempTags = [];
        for ($i = 0; $i <= count($tags)-1; $i++) {
            array_push($tempTags, $tags[$i]['id']);
        };
        $I->assertSame($tagsToSet, $tempTags);
    }

    public function setSkillsForUser(ApiTester $I)
    {
        $skillsToSet = array('computer', 'music', 'programming');
        $I->wantTo('set skills for user');
        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);
        $I->setSkillsForUserResponse(['skills' => $skillsToSet]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);

        // test the set links
        $I->getMyProfileResponse();
        $response = $I->decodeResponse($I);

        $I->assertSame($response['profile']['skills'], $skillsToSet);
    }

    public function setLinksForUser(ApiTester $I)
    {
        $linksToSet[0]['url'] = "http://www.mbl.is";
        $linksToSet[0]['title'] = "The Morning Newspaper";

        $linksToSet[1]['url'] = "https://www.karolinafund.com";
        $linksToSet[1]['title'] = "Karolina Fund";

        $I->wantTo('succeed at setting links for user');

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->setLinksForUserResponse(['links' => $linksToSet]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);

        // test the set links
        $I->getMyProfileResponse();
        $links = $I->decodeResponse($I, 'users_links');

        $I->assertSame($linksToSet[1]['url'], $links[1]['url']);
        $I->assertSame('www.karolinafund.com', $links[1]['site']);
    }

    /**
     * @skip TODO: implement
     */
    public function getUsersPreviews(ApiTester $I)
    {
        // TODO: implement
    }

    /**
     * @skip TODO: implement
     */
    public function getUserProfile(ApiTester $I)
    {
        // TODO: implement
    }

    /**
     * @skip TODO: implement
     */
    public function getMyProfile(ApiTester $I)
    {
        // TODO: implement
    }

    /**
     * @skip TODO: implement
     */
    public function updateMyProfile(ApiTester $I)
    {
        // TODO: implement
    }

    /**
     * @skip TODO: implement
     */
    public function updateMyProfileImage(ApiTester $I)
    {
        // TODO: implement
    }
}

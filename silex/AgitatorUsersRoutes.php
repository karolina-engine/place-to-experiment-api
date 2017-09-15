<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ramsey\Uuid\Uuid;

$agitator = $app['controllers_factory'];


$agitator->post('/users/tokens/', function (Request $request) use ($app) {

	$auth = $app['authInteractor'];
	$email = $request->request->get('email');
	$password = $request->request->get('password');

	$auth->setEmailAndPassword($email, $password);

	$ttl = 43200;
	$token = $auth->getBearerTokenIfAuthenticated($ttl);

	$responseData['token']['string'] = $token;
	$responseData['status'] = "success";

	return $app->json($responseData);

});



$agitator->post('/users/sso/discourse/', function (Request $request) use ($app) {

	$sso = $request->request->get('sso');
	$sig = $request->request->get('sig');

	$discourseSSOSecret = getenv('discourse_sso_secret');
//	$discourseUrl = getenv('discourse_url');

	$discourseSSO = new Karolina\Http\DiscourseSSO();
	$discourseSSO->setSecret($discourseSSOSecret);

	// validate the payload
	if (!($discourseSSO->validatePayload($sso, $sig))) {
	    // invaild, deny
	    header("HTTP/1.1 403 Forbidden");
	    echo("Bad SSO request");
	    die();
	}

	$user = $app['currentUser'];

	$query = $discourseSSO->getSignedStringForUser($user);

	$responseData['status'] = "success";
	$responseData['sso_string'] = $query;

	return $app->json($responseData);

});


// Create new user

$agitator->post('/users/', function (Request $request) use ($app) {

	$password = $request->request->get('password');
	$firstName = $request->request->get('first_name');
	$lastName = $request->request->get('last_name');
	$email = $request->request->get('email');

	$user = new \Karolina\User\User(null, $email, $firstName, $lastName);
	$user->setNewPassword($password);

	$userRepository = new \Karolina\User\UserRepository($app['ci']);

	$token = $userRepository->createNew($user);

	$response['token']['string'] = $token;
	$response['status'] = "success";
	return $app->json($response);
	
});



$agitator->post('/users/me/tags/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$tags = $request->request->get('tags');
	$userEditor->replaceTags($tags);

	$response['status'] = "success";
	$response['message'] = "Tags have been updated.";

	return $app->json($response);


});



$agitator->post('/users/me/skills/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$skills = $request->request->get('skills');
	$userEditor->replaceSkills($skills);

	$response['status'] = "success";
	$response['message'] = "Skills have been updated.";

	return $app->json($response);


});

$agitator->post('/users/me/links/', function (Request $request) use ($app) {


	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);
	$links = $request->request->get('links');
	$userEditor->replaceLinks($links);

	$response['status'] = "success";
	$response['message'] = "Links have been updated.";

	return $app->json($response);


});


$agitator->get('/users/preview/', function (Request $request) use ($app) {

	$usersRepo = new \Karolina\User\UserRepository($app['ci']);
	$users = $usersRepo->getAll();

	$responseArray = array();

	$imgStorageUrl = $app['platform']->getImgStorageUrl();
	foreach ($users as $user) {

		$userField['image'] = NULL;
		foreach ($user->getImageCollectionDocument() as $contentKey => $imageData) {
			$userField['image'] = $imgStorageUrl."/".$imageData['filename'];
		}

		$userField['user_id'] = $user->getId();
		$userField['first_name'] = $user->getFirstName();
		$userField['last_name'] = $user->getLastName();
		$userField['short_description'] = $user->getProfileShortDescription()->getValue();
		$userField['links'] = $user->getLinks();
		$tagResponse = new Karolina\API\v1\TagsResponse($user->getTagsDocument(), 'EN');
		$userField['tags'] = $tagResponse->get();

		$responseArray[] = $userField;

	}

	$response['previews'] = $responseArray;

	$response['status'] = "success";
	return $app->json($response);
	
});


// Get user profile
$agitator->get('/users/{userId}/profile', function ($userId, Request $request) use ($app) {

	$currentUser = $app['currentUser'];

	$getProfile = new Karolina\User\Action\GetProfile(
		$currentUser, 
		new \Karolina\User\UserRepository($app['ci']), 
		$app['platform']->getImgStorageUrl(),
        $app['platform']
		);

	$getProfile = $getProfile->forUserId($userId);

	if ($app['platform']->conf('experiments') == 'yes') {

		$getProfile = $getProfile->withExperiments($app['experimentInteractor']);
	}

	$response = $getProfile->getResponse();

	$response['status'] = "success";
	return $app->json($response);
	
});



// Update current user profile
$agitator->patch('/users/me/profile', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	// Get request
	$profile['shortDescription'] = $request->request->get('short_description');
	$profile['description'] = $request->request->get('long_description');
	$profile['firstName'] = $request->request->get('first_name');
	$profile['lastName'] = $request->request->get('last_name');

	// Process images
	$imageFileRepository = new \Karolina\Image\ImageFileRepository($app['s3']);
	$encode = new Karolina\Language\Base64InlineEncodingToFile($imageFileRepository, $app['platform']->conf('key'), $app['platform']->getImgStorageUrl());
	$profile['description'] = $encode->processImages($profile['description']);

	$userEditor->updateProfile($profile);

	$response['status'] = "success";

	return $app->json($response);
	
})->before($authRequired);


// Update current user profile
$agitator->patch('/users/me/profile/image_collection', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);


	$imagesCollection = $request->request->get('image_collection');
	$profileImageFile = $imagesCollection['profile']['filename'];

	$userEditor->updateProfileImage($profileImageFile);

	$response['status'] = "success";

	return $app->json($response);
	
})->before($authRequired);



// Update password
$agitator->post('/users/me/password', function (Request $request) use ($app) {

	if (!$app['currentUser']) {
		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');
	}

	$userEditor = new \Karolina\User\UserEditor(new \Karolina\User\UserRepository($app['ci']));
	$userEditor->setUser($app['currentUser']);

	$userEditor->updatePassword($request->request->get('new_password'), $request->request->get('old_password'));

	$response['status'] = "success";

	return $app->json($response);
	
})->before($authRequired);


// Request a password token
$agitator->post('/users/password_resets/', function (Request $request) use ($app) {

	$auth = $app['authInteractor'];
	$email = $request->request->get('email');

	$auth->setEmail($email);
	$auth->resetPassword();

	$responseData['status'] = "success";

	return $app->json($responseData);

});

// Update password with password token
$agitator->post('/users/password_changes/', function (Request $request) use ($app) {

	$userEditor = $app['userEditor'];

	$email = $request->request->get('email');
	$token = $request->request->get('password_reset_token');
	$newPassword = $request->request->get('new_password');

	$userEditor->updatePasswordWithToken($email, $token, $newPassword);

	$responseData['status'] = "success";
	return $app->json($responseData);


});



$agitator->get('/users/{userId}/experiments/preview/{langCode}', function ($userId, $langCode, Request $request) use ($app) {

	if ($userId === "me") {

		if (!$app['currentUser']) {

			throw new Karolina\KarolinaException('You are not logged in', 401, null, 'invalid_login');

		} else {

			$userId = $app['currentUser']->getUserId();

		}

	}

	$interactor = $app['experimentInteractor'];
	$previews = $interactor->getAllPreviewDocumentsByTeamMember($userId, $langCode);
	$response['previews'] = $previews;
	$response['status'] = "success";

	return $app->json($response);


});



return $agitator;
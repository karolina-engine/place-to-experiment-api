<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Silex\Provider;
use Symfony\Component\Translation\Loader\YamlFileLoader;


/*********************************************
	LANGUAGE
*********************************************/

if (!defined('LANGUAGE')) {
	define('LANGUAGE', 'EN');
}

/*********************************************
	Current, logged in user
*********************************************/

$app['currentUser'] = false;


/*********************************************
	Platform
*********************************************/

$platform =  new Karolina\Platform(getenv('platform_config'));
$settingRepository = new Karolina\Setting\SettingRepository();
$settings = $settingRepository->getAll();
$platform->setSettingsGroup($settings);

$app['platform'] = $platform;


/*********************************************
	JSON requests
*********************************************/

$app->before(function (Request $request) {

	header('Content-Type: application/json');


    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }

});


/*********************************************
	Logged in user
*********************************************/

$app->before(function (Request $request, $app) {

	$userRepository = new \Karolina\User\UserRepository($app['ci']);

	$app['currentUser'] = false;

	if ($authHeader = $request->headers->get('Authorization')) {

		$app['currentUser'] = $userRepository->getWithToken($authHeader);

	}



});


/*********************************************
	Debug
*********************************************/

if (getenv('environment') == 'development')  {

	$app['debug'] = true;

} else {

	$app['debug'] = false;

}


/*********************************************
	Translation
*********************************************/

$app->register(new Silex\Provider\TranslationServiceProvider());

$app['translator']->setLocale(strtolower(LANGUAGE));


/*********************************************
	Templates
*********************************************/

$app->register(new Silex\Provider\TwigServiceProvider());

$app->extend('twig', function($twig, $app) {

	$loaders = array();
	$default = new Twig_Loader_Filesystem(__DIR__.'/../templates/default/twig');
	$customTemplatesDir = __DIR__.'/../templates/platforms/'.$app['platform']->conf('key').'/twig/';

	if (file_exists($customTemplatesDir)) {
		$custom = new Twig_Loader_Filesystem($customTemplatesDir);
		$loaders = array($custom, $default);
	} else {

		$loaders = array($default);

	}

	$loader = new Twig_Loader_Chain($loaders);
	$twig->setLoader($loader);
	return $twig;

});


/*********************************************
	AWS service
*********************************************/


$app['s3'] = function () {

	$aws = new Aws\Sdk([
	    'region'   => 'eu-west-1',
	    'version'  => 'latest'
	]);
	return $aws->createS3();

};


/*********************************************
	Interactors
*********************************************/


// Notifications
$app['notificationService'] = function ($app) {

	$notification = new Karolina\Notification();
	$notification->setTemplateEngine($app['twig']);

	return $notification;

};



// Experiment
$app['experimentInteractor'] = function ($app) {

	return new Karolina\Experiment\ExperimentInteractor(
		new Karolina\Experiment\ExperimentRepository($app['ci'])
	);

};

$app['experimentEditor'] = function ($app) {

	$editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$app['experimentInteractor'],
		new Karolina\Experiment\ExperimentRepository($app['ci'])
		);

	return $editor;

};


// User
$app['userRepository'] = new \Karolina\User\UserRepository($app['ci']);

$app['userInteractor'] = function ($app) {

    $interactor = new \Karolina\User\UserInteractor($app['userRepository']);
    return $interactor;

};

$app['userEditor'] = function ($app) {

	$editor = new \Karolina\User\UserEditor($app['userRepository']);
	$editor->setPlatform($app['platform']);
	return $editor;

};

$app['authInteractor'] = function ($app) {

	$authInteractor = new Karolina\User\AuthInteractor();
	$authInteractor->setNotificationService($app['notificationService']);
	$authInteractor->setUserRepository(new Karolina\User\UserRepository());
	$authInteractor->setPlatform($app['platform']);


	return $authInteractor;

};



/*********************************************
	EVENTS
*********************************************/

// To be decided: How do we handle subscriptions to events?
// Should we contain them in the interactors and then call their methods here?

$app['platform']->subscribeToEvents( new \Karolina\User\UserEveryMinuteSubscription());





/*********************************************
	AFTER MIDDLEWARE
*********************************************/


$app->after(function (Request $request, Response $response, $app) {

});


/*********************************************
	CORS - TODO: Review security
*********************************************/

$app->after(function (Request $request, Response $response) {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Cache-Control');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS, GET, PATCH, PUT, DELETE');
    });

$app->options("{anything}", function () {
        return new \Symfony\Component\HttpFoundation\Response(null, 200);
    })->assert("anything", ".*");


/*********************************************
	AUTHENTICATION MIDDLEWARE
*********************************************/

$adminOnly = function (Request $request, Silex\Application $app) {
    /** @var \Karolina\User\User $currentUser */
    $currentUser = $app['currentUser'];
	if ($currentUser and $currentUser->isAdmin()) {
        // We are admin, it's ok
	} else {
        $response['status'] = "admin_only";
        return $app->json($response, 401);

    }
};


$authRequired = function (Request $request, Silex\Application $app) {

	if (!$app['currentUser']) {

		$response['status'] = "authorization_required";
		$response['message'] = "You must be logged in to do this.";
		return $app->json($response, 401);

	}
};

/*********************************************
	CATCH ERRORS
*********************************************/

$app->error(function (\Exception $e, $code, $status = "error") use ($app) {


    if ($e instanceof Karolina\KarolinaException) {

		$response['status']= $e->getStatusKey();

		if ($e->getCode()) {

			$code = $e->getCode();

		}
		$response['message'] = $e->getMessage();

    } else if ($e instanceof Karolina\Exception)  {


		$response['status']= $e->getStatusKey();
		$response['exception_type']= "Karolina\Exception";


		$code = $e->getHttpCode();
		$response['message'] = $e->getMessage();

    }


     else {

		$response['status']= "error";
	    if ($app['debug']) {
			$response['message'] = $e->getMessage();

	    } else {
			$response['message'] = "There has been an error. Please contact system admin if this continues.";
	    }

    }


	return $app->json($response, $code);


});



/*********************************************
	HTTP ROUTES
*********************************************/


// Agitator API
$app->mount('/agitator', include __DIR__.'/AgitatorRoutes.php');
$app->mount('/agitator', include __DIR__.'/AgitatorExperimentsRoutes.php');
$app->mount('/agitator', include __DIR__.'/AgitatorUsersRoutes.php');


if ($app['platform']->conf('is_sandbox') === 'yes' or $app['debug'] == true) {

	if (file_exists(__DIR__.'/sandbox/SandboxRoutes.php')) {
		// Allow testing, unsafe and potentially destructive actions
		$app->mount('/sandbox', include __DIR__.'/sandbox/SandboxRoutes.php');

	}


}

//................... let's go:
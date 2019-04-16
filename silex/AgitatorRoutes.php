<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;

$agitator = $app['controllers_factory'];


$agitator->get('/nothing/', function (Request $request) use ($app) {

	$response['nothing'] = "yes";
	return $app->json($response);

});


$agitator->get('/dashboard/', function (Request $request) use ($app) {

    $dashboard = new \Karolina\Stats\Dashboard($app['experimentInteractor'], $app['userInteractor']);

    return $app->json($dashboard->getStats('index'));

});

$agitator->post('/files/images/', function (Request $request) use ($app) {

	$file = $request->files->get('file');

	$imageFileRepository = new Karolina\Image\ImageFileRepository($app['s3']);

	$destinationFolder = $app['platform']->conf('key');

	$filename = $imageFileRepository->storeImageFile($file, $destinationFolder);
	$response['image']['filename'] = $filename;

	$imgStorageUrl = $app['platform']->getImgStorageUrl();
	$response['file']['url'] = $imgStorageUrl.'/'.$filename;

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);

$agitator->post('/contact/', function (Request $request) use ($app) {

	$notificationService = $app['notificationService'];
	$platform = $app['platform'];

	$data = $request->request->get('data');
	$data['platform_name'] = $platform->conf('name');

	$notificationService->setSubjectFromTemplate('contact_notification_subject', $data);
	$notificationService->setBodyFromTemplate('contact_notification_body', $data);	
	$notificationService->addCCEmail($data['email']);
	$notificationService->addBccEmail('monitor@karolina.io');
	$notificationService->send();

	$response['status'] = "success";

	return $app->json($response);


});


return $agitator;

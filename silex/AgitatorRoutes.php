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


return $agitator;
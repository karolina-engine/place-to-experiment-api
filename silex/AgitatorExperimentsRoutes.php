<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Ramsey\Uuid\Uuid;

$agitator = $app['controllers_factory'];



$agitator->get('/experiments/preview/{langCode}', function ($langCode, Request $request) use ($app) {

	/** @var \Karolina\Experiment\ExperimentInteractor $interactor */
	$interactor = $app['experimentInteractor'];

	$filterArguments = $request->query->all();

	$experimentsPreviews = $interactor->getPreviewDocuments(
		$langCode,
		$filterArguments
	);


	if ($app['currentUser'] and $app['currentUser']->isAdmin()) {
		$showTeamEmails = true;
	}else{
		$showTeamEmails = false;
	}
	$response['previews'] = array();
	foreach ($experimentsPreviews as $preview) {

		$response['previews'][]
			= (new Karolina\API\v1\ExperimentResponse($preview, $langCode, $app['platform']))->getPreview($showTeamEmails);

	}

	$response['status'] = "success";

	return $app->json($response);


});




// temp method to inject new tags into the db, only run this once
$agitator->get('/experiments/addmoretags/{langCode}', function ($langCode, Request $request) use ($app) {

	$tagRepository = new \Karolina\Tag\TagRepository();


	$newTags = array('Maahanmuuttajat','Syrjäytyneet','Kiertotalous','Hävikki','Resurssiviisaus','Hyvinvointi','Työhyvinvointi','Kerhotoiminta','Vapaaehtoistoiminta','Hyväntekeväisyys','Blockchain','Kansantalous','Liiketoimintamallit','Toimitilat','Vanhemmuus','Kasvatus','Palvelutalo','Viestintä','Kokeilut','Elämänhallinta','johtaminen','Itsensä johtaminen','Työllisyydenhoito','Aikuiskoulutus','Taide','Kuvataide','Kuvanveisto','Maalaustaide','Grafiikka','Performanssi','Arkkitehtuuri','Muotoilu','Design','Tuotesuunnittelu','Valokuvataide','Tekstiilitaide','Näyttely','Taidegalleria','Taidemuseo','Museo','Yksinäisyys','Leikki','Pelillisyys','Yhteisö','Sisällöntuotanto','Ikäihmiset','Teknologia','Mielenterveys','MyData (omadata)','RCT','Open data','Tarinat','Musiikki','Saavutettavuus','Robotiikka','Tekoäly','Toimintamalli','asuminen ','asunnottomuus','osallisuus','yhteisöllisyys','Yhteistyö','Viranomaisyhteistyö','Lähiruoka','3D-tulostus','IoT (esineiden internet)','RPA (ohjelmistorobotiikka)','Open source softat','Augmented reality','Virtual reality','Drones (nelikopterit)','palvelumuotoilu','digikuntakokeilu','demokratia','Maakunta','Perustulo','Toimeentulo','Kuntoutus','Arjenhallinta','Asukastoiminta','HR','osaaminen','automaatio','API -talous','alustatalous','Tampere');


	foreach ($newTags as $tagLabel) {

		$tag = new Karolina\Tag\Tag();
		$tag->setLabel($tagLabel, $langCode);
		$tagRepository->save($tag);

	}

	return print_r($newTags, true);

})->before($adminOnly);



$agitator->get('/experiments/tags/{langCode}', function ($langCode, Request $request) use ($app) {

	$tagRepository = new \Karolina\Tag\TagRepository();

	$allTags = $tagRepository->getAll();

	$tagsResponse = array();

	foreach ($allTags as $tag) {

		$tagResponse['id'] = $tag->getId();
		$tagResponse['label'] = $tag->getLabel($langCode);
		$tagsResponse[] = $tagResponse;

	}

	$response['tags'] = $tagsResponse;

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->post('/experiments/{experimentId}/publish/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];

	$editor->setExperimentById($experimentId);

    $editor->publishExperiment();

	$response['status'] = "success";
	$response['message'] = "Experiment is published.";
	return $app->json($response);

});

$agitator->delete('/experiments/{experimentId}/publish/', function ($experimentId, Request $request) use ($app) {

    $editor = $app['experimentEditor'];

	$editor->setExperimentById($experimentId);

    $editor->unpublishExperiment();

	$response['status'] = "success";
	$response['message'] = "Experiment is unpublished.";
	return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/likes/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];

	if ($currentUser = $app['currentUser']) {

		$interactor->setExperimentById($experimentId);
		$interactor->setCurrentUser($currentUser);
		$interactor->addLike();

		$response['status'] = "success";
		return $app->json($response);

	} else {

		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
	}


});

$agitator->delete('/experiments/{experimentId}/likes/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];

	if ($currentUser = $app['currentUser']) {

		$interactor->setExperimentById($experimentId);
		$interactor->setCurrentUser($currentUser);
		$interactor->removeLike();

		$response['status'] = "success";
		return $app->json($response);

	} else {

		throw new Karolina\KarolinaException('You are not logged in', 401, null, 'not_authorized');
	}


});



$agitator->get('/experiments/{experimentId}/{langCode}', function ($experimentId, $langCode, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$document = $interactor->getDocument($experimentId, $langCode);

	$currentUser = $app['currentUser'];

	$experimentResponse = new Karolina\API\v1\ExperimentResponse($document, $langCode, $app['platform']);

	$response['experiment'] = $experimentResponse->get();
	$response['acl'] = $experimentResponse->getACL($currentUser);
	$response['my_relationships'] = $experimentResponse->getMyRelationships($currentUser);

	$response['status'] = "success";
	return $app->json($response);

});

$agitator->patch('/experiments/{experimentId}/funding/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

	$funding = $request->request->all();
	$editor->updateFunding($funding);

	$response['status'] = "success";
	return $app->json($response);

});


$agitator->post('/experiments/{experimentId}/tags/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

	$tags = $request->request->get('tags');
	$editor->replaceTags($tags);

	$response['status'] = "success";
	$response['message'] = "Tags have been updated.";
	return $app->json($response);


});



$agitator->post('/experiments/{experimentId}/stagemoves/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

    $direction = $request->request->get('direction');

    switch ($direction) {
        case 'next':
            $editor->moveToNextStage();
        	$response['status'] = "success";
        	$response['message'] = "Stage has been updated.";
            break;
        case 'previous':
            $editor->moveToPreviousStage();
        	$response['status'] = "success";
        	$response['message'] = "Stage has been updated.";
            break;
        default:
            throw new Karolina\KarolinaException('You need to provide direction', 400, null, 'missing_request_parameters');
            break;
    }

	return $app->json($response);

});



$agitator->post('/experiments/{experimentId}/links/', function ($experimentId, Request $request) use ($app) {

	$editor = $app['experimentEditor'];
	$editor->setExperimentById($experimentId);

	$links = $request->request->get('links');
	$editor->replaceLinks($links);

	$response['status'] = "success";
	$response['message'] = "Links have been updated.";
	return $app->json($response);


});

$agitator->get('/experiments/rewritedocuments/', function(Request $request) use ($app) {

	$repo = new Karolina\Experiment\ExperimentRepository($app['ci']);
	$documentsCount = $repo->reWriteAllDocuments();

	$response['count'] = $documentsCount;
	$response['status'] = "success";

	return $app->json($response);

});

$agitator->post('/experiments/', function (Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];

	$editor = $app['experimentEditor'];

	if ($stage = $request->request->get('stage')) {

		$experimentId = $editor->createNew($stage);

	} else {

		$experimentId = $editor->createNew();

	}

	$response['experiment']['experiment_id'] = $experimentId;
	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/settings/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

	$editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$newSettings = $request->request->get('settings');

	$editor->updateSettings($newSettings);

	$response['status'] = "success";
	$response['message'] = "Updated settings";

	return $app->json($response);

})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/image_collection/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$imageCollectionData = $request->request->get('image_collection');
	$editor->updateImages($imageCollectionData);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);

$agitator->patch('/experiments/{experimentId}/custom_language/{langCode}/', function ($experimentId, $langCode, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);


	$imageFileRepository = new \Karolina\Image\ImageFileRepository($app['s3']);

	$encode = new Karolina\Language\Base64InlineEncodingToFile($imageFileRepository, $app['platform']->conf('key'), $app['platform']->getImgStorageUrl());

	$customLanguage = $request->request->get('custom_language');
	foreach ($customLanguage as $key => $field) {
		if ($field['format'] === "html") {

			$customLanguage[$key]['value'] = $encode->processImages($field['value']);

		}
	}
	$editor->updateCustomLanguage($customLanguage, $langCode);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);


$agitator->patch('/experiments/{experimentId}/settings/', function ($experimentId, Request $request) use ($app) {

	$interactor = $app['experimentInteractor'];
	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$newSettings = $request->request->get('settings');

	$editor->updateSettings($newSettings);

	$response['status'] = "success";

	return $app->json($response);

})->before($authRequired);

$agitator->patch('/experiments/{experimentId}/language/{langCode}/', function ($experimentId, $langCode, Request $request) use ($app) {

	$repository = new Karolina\Experiment\ExperimentRepository($app['ci']);
	$interactor = new Karolina\Experiment\ExperimentInteractor($repository);

	$interactor->setExperimentById($experimentId);

    $editor = new Karolina\Experiment\ExperimentEditor(
		$app['currentUser'],
		$interactor,
		new Karolina\Experiment\ExperimentRepository($app['ci']),
        $app['platform']
		);

	$language = $request->request->get('language');

	$editor->updateDefaultLanguage($language, $langCode);

	$response['status'] = "success";

	return $app->json($response);


})->before($authRequired);




return $agitator;

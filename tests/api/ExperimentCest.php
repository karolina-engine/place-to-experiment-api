<?php
use Codeception\Util\Fixtures;

/**
 * Contains tests related to the /experiments/* endpoint.
 * @run Normal: codecept run api ExperimentCest
 * @run Single: codecept run api ExperimentCest:<functionName>$ (note: runs only the test with <functionName>)
 * @run Partial: codecept run api ExperimentCest:<functionName> (note: runs any tests that begin with <functionName>)
 * @run With steps: codecept run api ExperimentCest --steps (can be combined with Single or Partial)
 * @run With debug: codecept run api ExperimentCest --debug (can be combined with Single or Partial)
 *
 * BUG: @test createExperiment: Create a experiment.
 * BUG: @test createExperimentWithoutAuthentication: [NEG] Create a experiment without authentication.
 * @test createExperimentWithWrongAuthentication: [NEG] Create a experiment with wrong authentication.
 * @test getExperiment: Get a experiment.
 * @test getExperimentWithWrongAuthentication: [NEG] Get a experiment with wrong authentication.
 * @test getExperimentsPreview: Gets a list of basic info for experiments that are or have been funding.
 * @test getTagsForExperiments: Get tags that are available for experiments.
 * BUG: @test setTagsForExperiment: Set tags for an experiment.
 */

class ExperimentCest
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

    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }


    public function createExperiment(ApiTester $I)
    {
        $I->wantTo('succeed at creating a new experiment');

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->createExperimentResponse();

        $I->checkResponse($I, 'create_experiment');
        $experimentId = $I->decodeResponse($I, 'experiment_id');
        $I->comment('The ID of the created experiment is: '.$experimentId);

        // Add this experiment to fixtures
        Fixtures::add('experimentId', $experimentId);
    }


    public function createExperimentWithoutAuthentication(ApiTester $I)
    {
        $I->wantTo('fail at creating a new experiment without authentication');

        $I->setRole('creator');
        $I->setStatus('failure');
        $I->setAuthorization(false);

        $I->createExperimentResponse();

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    public function createExperimentWithWrongAuthentication(ApiTester $I)
    {
        $I->wantTo('fail at creating a new experiment with wrong authentication');

        $I->setRole('creator');
        $I->setStatus('failure');
        $I->setAuthorization(true, '12345');

        $I->createExperimentResponse();

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    public function getExperiment(ApiTester $I)
    {
        $I->wantTo('succeed at getting a experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->getExperimentResponse($experimentId);

        $I->checkResponse($I, 'get_experiment');
        $experimentId = $I->decodeResponse($I, 'experiment_id');
        $I->comment('The ID of the retreived experiment is: '.$experimentId);
    }

    public function getExperimentWithWrongAuthentication(ApiTester $I)
    {
        $I->wantTo('fail at getting a experiment with wrong authentication');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('failure');
        $I->setAuthorization(true, '12345');

        $I->getExperimentResponse($experimentId);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);
    }

    public function getExperimentsPreview(ApiTester $I)
    {
        $I->wantTo('succeed at experiments preview');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('visitor');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getExperimentsPreviewResponse();

        $I->checkResponse($I, 'get_experiments_preview');
        $experimentId = $I->decodeResponse($I, 'preview_experiment_id');
        $I->comment('The ID of the first retrieved experiment is: '.$experimentId);
    }

	public function getExperimentsPreviewWithQuery(ApiTester $I)
	{
		$query = '?place=index&tag=1';

		$I->wantTo('succeed at experiments preview with a query parameter');
		$experimentId = $this->getExperimentId($I);

		$I->setRole('visitor');
		$I->setStatus('success');
		$I->setAuthorization(false);

		$I->getExperimentsPreviewResponse(false, $query);

		$I->checkResponse($I, 'get_experiments_preview');
		$experimentPreviews = $I->decodeResponse($I, 'experiment_previews');
		$I->comment('The number of matching experiments is: '.count($experimentPreviews));
	}

    public function getAvailableTagsForExperiments(ApiTester $I)
    {
        $I->wantTo('succeed at getting available tags for experiments');

        $I->setRole('visitor');
        $I->setStatus('success');
        $I->setAuthorization(false);

        $I->getAvailableTagsForExperimentsResponse();

        $I->checkResponse($I, 'get_tags_for_experiments');
        $tagId = $I->decodeResponse($I, 'tag_id');
        $I->comment('The ID of the first retrieved tag is: '.$tagId);
    }


    public function setTagsForExperiment(ApiTester $I)
    {
        $tagsToSet = [1, 2, 5];

        $I->wantTo('succeed at setting tags for experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->setTagsForExperimentResponse($experimentId, ['tags' => $tagsToSet]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);

        // test the set tags
        $I->getExperimentResponse($experimentId);
        $tags = $I->decodeResponse($I, 'experiment_tags');

        // convert array to match POST body
        $tempTags = [];
        for ($i = 0; $i <= count($tags)-1; $i++) {
            array_push($tempTags, $tags[$i]['id']);
        };
        $I->assertSame($tagsToSet, $tempTags);
    }


    public function setLinksForExperiment(ApiTester $I)
    {
        $linksToSet[0]['url'] = "http://www.mbl.is";
        $linksToSet[0]['title'] = "The Morning Newspaper";

        $linksToSet[1]['url'] = "https://www.karolinafund.com";
        $linksToSet[1]['title'] = "Karolina Fund";

        $I->wantTo('succeed at setting links for experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->setLinksForExperimentResponse($experimentId, ['links' => $linksToSet]);

        $I->checkResponse($I, 'message');
        $message = $I->decodeResponse($I, 'message');
        $I->comment('Message: '.$message);

        // test the set links
        $I->getExperimentResponse($experimentId);
        $links = $I->decodeResponse($I, 'experiment_links');

        $I->assertSame($linksToSet[1]['url'], $links[1]['url']);
        $I->assertSame('www.karolinafund.com', $links[1]['site']);
    }


    public function setGeographicLocation(ApiTester $I)
    {
        $I->wantTo('succeed at changing geographic location for experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $I->setSettingForExperimentResponse($experimentId, ['geographic_location' => 'Helsinki']);

        $I->getExperimentResponse($experimentId);
        $geographicLocation = $I->decodeResponse($I, 'experiment_geographic_location');

        $I->assertSame('Helsinki', $geographicLocation);
    }




    public function moveToNextStage(ApiTester $I)
    {
        $I->wantTo('succeed at moving the experiment to the next stage');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);

        $endpoint = '/experiments/'.$experimentId.'/stagemoves/';

        $I->setHeaders($I);
        $I->getExperimentResponse($experimentId);

        $experimentResponse = $I->decodeResponse($I);
        $orgStage = $experimentResponse['experiment']['stage'];

        $I->sendPOST($endpoint, array());

        $I->getExperimentResponse($experimentId);

        $experimentResponse = $I->decodeResponse($I);

        $I->assertSame($orgStage + 1, $experimentResponse['experiment']['stage']);
    }

    public function setLanguage(ApiTester $I)
    {
        $I->wantTo('succeed at setting custom language for experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);
        $I->setHeaders($I);

        $fields['title'] = array('value' => 'My title ...', 'format' => 'plaintext');
        $I->sendPATCH('/experiments/'.$experimentId.'/language/en/', array('language' => $fields));

        $I->getExperimentResponse($experimentId);
        $response = $I->decodeResponse($I);

        $langVal = $response['experiment']['language']['title']['value'];
        $I->assertSame('My title ...', $langVal);
    }
    public function setCustomLanguage(ApiTester $I)
    {
        $I->wantTo('succeed at setting custom language for experiment');
        $experimentId = $this->getExperimentId($I);

        $I->setRole('creator');
        $I->setStatus('success');
        $I->setAuthorization(true);
        $I->setHeaders($I);

        $fields['my_custom_field'] = array('value' => 'Some value!', 'format' => 'plaintext');
        $I->sendPATCH('/experiments/'.$experimentId.'/custom_language/en/', array('custom_language' => $fields));

        $I->getExperimentResponse($experimentId);
        $response = $I->decodeResponse($I);

        $customLangVal = $response['experiment']['custom_language']['my_custom_field']['value'];
        $I->assertSame('Some value!', $customLangVal);
    }

	public function getTeamEmailsAsAdmin (ApiTester $I)
	{
		$I->wantTo('succeed at getting team emails from experiment index as admin');

		$I->setRole('admin');
		$I->setStatus('success');
		$I->setAuthorization(true);

		$I->getExperimentsPreviewResponse();

		$I->checkResponse($I, 'get_experiments_preview');

		$experimentEmails = $I->decodeResponse($I, 'team_emails');
		$I->assertNotSame(NULL, $experimentEmails);
		$I->comment('The first email in the team is: '.$experimentEmails[0]);
	}

	public function getTeamEmailsAsVisitor (ApiTester $I)
	{
		$I->wantTo('succeed at getting NULL team emails from experiment index as visitor');

		$I->setRole('visitor');
		$I->setStatus('success');
		$I->setAuthorization(false);

		$I->getExperimentsPreviewResponse();

		$I->checkResponse($I, 'get_experiments_preview');

		$experimentEmails = $I->decodeResponse($I, 'team_emails');
		$I->assertSame(NULL, $experimentEmails);
	}
}

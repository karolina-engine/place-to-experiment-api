<?php

/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 21/12/16
 * Time: 12:13
 */
use PHPUnit\Framework\TestCase;
use Karolina\Experiment\Experiment;


class ExperimentTest extends TestCase
{


    public function testSetGeographicLocation () {

        $experiment = new Experiment();
        $experiment->setGeographicLocation('Betlehem');
        $this->assertEquals($experiment->getGeographicLocation(), 'Betlehem');
    
    }

    public function testAccessControl () {

        $experiment = new Experiment();
        $user = new \Karolina\User\User(111, "example@example.com", "John", "Smith");

        // Can't edit anything, cause I'm not member or team, nor am I admin
        $this->assertEquals($experiment->canEditSettings($user), false);
        $this->assertEquals($experiment->canEditContent($user), false);
        $this->assertEquals($experiment->canEditWhereToShow($user), false);
        $this->assertEquals($experiment->canEditAbility($user), false);
        $this->assertEquals($experiment->canEditFunding($user), false);

        // Can edit some things, cause I'm not in the team
        $experiment->addToTeam($user);
        $this->assertEquals($experiment->canEditSettings($user), true);
        $this->assertEquals($experiment->canEditContent($user), true);
        $this->assertEquals($experiment->canEditWhereToShow($user), false);
        $this->assertEquals($experiment->canEditAbility($user), false);
        $this->assertEquals($experiment->canEditFunding($user), false);


        // Can edit everything, cause I'm now an admin
        $user->makeAdmin();
        $this->assertEquals($experiment->canEditSettings($user), true);
        $this->assertEquals($experiment->canEditContent($user), true);
        $this->assertEquals($experiment->canEditWhereToShow($user), true);
        $this->assertEquals($experiment->canEditAbility($user), true);
        $this->assertEquals($experiment->canEditFunding($user), true);


    }

    public function testAddLinks () {

        $links[0]['url'] = "www.mbl.is";
        $links[0]['title'] = "Morgunblaðið";
        $links[1]['url'] = "https://www.mbl.is/";
        $links[1]['title'] = "Karolina Fund";

        $experiment = new Experiment();
        $experiment->setLinks($links);

        $retrievedLinks = $experiment->getLinks();

        $this->assertEquals($retrievedLinks[0]['title'], "Morgunblaðið");

    }

    public function testGetUsersWhoLike () {

        $experiment = new Experiment();
        $root = new Karolina\Network\Root($experiment);
        $experiment->setNetwork($root);

        $user = new \Karolina\User\User(111, "example@example.com", "John", "Smith");
        $experiment->addLike($user);

        $fetchedUsers = $experiment->getUsersWhoLike();
        $this->assertEquals($fetchedUsers[0]->getEmail(), $user->getEmail());


    }

    public function testAddLike () {

        $experiment = new Experiment();
        $root = new Karolina\Network\Root($experiment);
        $experiment->setNetwork($root);

        $user = new \Karolina\User\User(111, "example@example.com", "John", "Smith");
        $experiment->addLike($user);

        $user2 = new \Karolina\User\User(222, "example2@example.com", "Karl", "Johnsson");
        $experiment->addLike($user2);

        $user3 = new \Karolina\User\User(333, "example3@example.com", "Smithy", "Johnson");
        $experiment->addLike($user3);

        // Same user again
        $user = new \Karolina\User\User(111, "example@example.com", "John", "Smith");
        $experiment->addLike($user);

        $count = $experiment->countLikes();        
        $this->assertEquals($count, 3);

    }

    public function testGetShortDescription ()
    {

        $shortDescription = "Guðmundur Jónsson frá Akureyri'";
        $experiment = new Experiment();
        $experiment->setShortDescription($shortDescription, 'EN');

        $this->assertEquals($shortDescription, $experiment->getShortDescription());

    }



    public function testMoveToNewStage ()
    {

        $experiment = new Experiment();
        $experiment->setStage(1);
        $experiment->setCustomLanguage('1_long_description', 'EN', 'My original long description', 'plaintext');

        $experiment->moveToNextStage();

        $prevLongDesc = $experiment->getCustomLanguage('1_long_description', 'EN');
        $currentLongDesc = $experiment->getCustomLanguage('2_long_description', 'EN');

        $this->assertEquals($prevLongDesc, $currentLongDesc);
        $this->assertEquals($experiment->getStage(), 2);

    }

    public function testGetShortDescriptionLanguageExists ()
    {

        $shortDescriptionIS = "Guðmundur Jónsson frá Akureyri'";
        $shortDescriptionEN = "Gudmund Johnson from Akureyri'";

        $experiment = new Experiment();
        $experiment->setShortDescription($shortDescriptionEN, 'EN');
        $experiment->setShortDescription($shortDescriptionIS, 'IS');

        $this->assertEquals($shortDescriptionEN, $experiment->getShortDescription('EN'));
        $this->assertEquals($shortDescriptionIS, $experiment->getShortDescription('IS'));

    }

    public function testGetShortDescriptionLanguageFallback ()
    {

        $shortDescriptionEN = "Gudmund Johnson from Akureyri'";

        $experiment = new Experiment();
        $experiment->setShortDescription($shortDescriptionEN, 'EN');

        $this->assertEquals($shortDescriptionEN, $experiment->getShortDescription('IS'));

    }

    public function testCustomLanguage () {

    	$customLanguageField = "my_field_".time();
    	$value = "Trying this out now!";

        $experiment = new Experiment();
        $experiment->setCustomLanguage($customLanguageField, 'ES', $value);

        $this->assertEquals($value, $experiment->getCustomLanguage($customLanguageField));


    }

	public function testGetExperimentTeamEmails () {

		$userA = new \Karolina\User\User(123, 'example@example.com', 'Arnar', 'Sigurdsson');
		$userB = new \Karolina\User\User(321, 'example2@example.com', 'John', 'Smith');

		$experiment = new Experiment();

		$experiment->addToTeam($userA);
		$experiment->addToTeam($userB);

		$teamEmails = $experiment->getTeamEmails();

		$this->assertEquals($userA->getEmail(), $teamEmails[0]);
		$this->assertEquals($userB->getEmail(), $teamEmails[1]);
	}
}

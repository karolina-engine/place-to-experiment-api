<?php

use PHPUnit\Framework\TestCase;


class NetworkTest extends TestCase
{


    public function testAddRelationship () {


        $experiment = new Karolina\Experiment\Experiment();
        $experiment->setId(123);
        $user = new \Karolina\User\User(111, "example@example.com", "Jon", "Smith");

        $root = new Karolina\Network\Root($experiment);

        $root->addFollower($user, 'like');
        $fetchedFollowers = $root->getFollowersByRelationship('like');
        $this->assertEquals($fetchedFollowers[111]->follower->getId(), $user->getId());
        $this->assertEquals($fetchedFollowers[111]->followed->getId(), $experiment->getId());

    }




}

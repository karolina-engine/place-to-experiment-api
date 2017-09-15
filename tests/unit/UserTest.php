<?php
use PHPUnit\Framework\TestCase;
use \Karolina\User\User;

use Mockery as m;

class UserTest extends TestCase
{


    public function tearDown()
    {
        m::close();
    }

    public function testIsAdmin () {

        $user = new User("123", "example@example.com", "Test", "User");
        $this->assertEquals($user->isAdmin(), false);

        $user->makeAdmin();

        $this->assertEquals($user->isAdmin(), true);


    }


    public function testAddSkills () {

        $user = new User("123", "example@example.com", "Test", "User");

        $newSkills = ['computers', 'COMputers', 'English', 'Nerding', 'Flying'];

        $user->setSkills($newSkills);
        $retrievedSkills = $user->getSkills();

        // Removed duplicates and all lowercase
        $expected = ['computers', 'english', 'nerding', 'flying'];

        $this->assertEquals($retrievedSkills, $expected);


    }

    public function testAddLinks () {

        $links[0]['url'] = "www.mbl.is";
        $links[0]['title'] = "Morgunblaðið";
        $links[1]['url'] = "https://www.mbl.is/";
        $links[1]['title'] = "Karolina Fund";

        $user = new User("123", "example@example.com", "Test", "User");
        $user->setLinks($links);

        $retrievedLinks = $user->getLinks();

        $this->assertEquals($retrievedLinks[0]['title'], "Morgunblaðið");

    }

    public function testAuthenticationWithWrongEmail () {

        $user = new User("123", "example@example.com", "Test", "User");

        $this->expectException(Karolina\Exception::class);
        $user->isAuthentic('WRONGexample@example.com', 'mypassword');

    }

    public function testAuthenticationWithourHasher () {

        $user = new User("123", "example@example.com", "Test", "User");

        $this->expectException(Karolina\Exception::class);
        $user->isAuthentic('example@example.com', 'mypassword');

    }

    public function testAuthenticationWithNativeHasher () {

        $user = new User("123", "example@example.com", "Test", "User");

        $hasher = new \Karolina\User\Hashers\NativeHasher();

        $hashString = $hasher->hash('mypassword');

        $user->setHasher($hasher);
        $user->setHashString($hashString);

        $this->assertEquals($user->isAuthentic('example@example.com', 'mypassword'), true);
        $this->assertEquals($user->isAuthentic('example@example.com', 'myWRONGpassword'), false);

    }

    public function testAuthenticationWithNewPassword () {

        $user = new User("123", "example@example.com", "Test", "User");

        $hasher = new \Karolina\User\Hashers\NativeHasher();

        $hashString = $hasher->hash('mypassword');

        $user->setHasher($hasher);
        $user->setHashString($hashString);
        $user->setNewPassword('myNEWPassword');

        $this->assertEquals($user->isAuthentic('example@example.com', 'myNEWPassword'), true);
        $this->assertEquals($user->isAuthentic('example@example.com', 'mypassword'), false);

    }

    public function testAuthenticationWithIonAuthLegacyHasher () {

        $user = new User("123", "example@example.com", "Test", "User");

        $hasher = new \Karolina\User\Hashers\IonAuthLegacyHasher();

        $hashString = "287bad739ca2aeabaf27cecb244d095a8f558137"; // Hard coded, since legacy hashers aren't allowed go generate more.

        $user->setHasher($hasher);
        $user->setHashString($hashString);

        $this->assertEquals($user->isAuthentic('example@example.com', 'mypassword'), true);
        $this->assertEquals($user->isAuthentic('example@example.com', 'myWRONGpassword'), false);

    }


    public function testAuthenticationWithIncorrectHasher () {

        $user = new User("123", "example@example.com", "Test", "User");

        $hasher = new \Karolina\User\Hashers\NativeHasher();

        $hashString = $hasher->hash('mypassword');
        $user->setHashString($hashString);

        $legacyHasher = new \Karolina\User\Hashers\IonAuthLegacyHasher();

        $user->setHasher($legacyHasher);

        $this->assertEquals($user->isAuthentic('example@example.com', 'mypassword'), false);
        $this->assertEquals($user->isAuthentic('example@example.com', 'myWRONGpassword'), false);

    }

    

}

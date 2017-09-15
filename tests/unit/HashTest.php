<?php

use PHPUnit\Framework\TestCase;


class HashTest extends TestCase
{

    public function testLegacyHashCorrectInput ()
    {

        $ionAuthHash = "28680678476f9e1c50da6e711675f179606c4e30"; // password123

        $ionAutHasher = new Karolina\User\Hashers\IonAuthLegacyHasher();

        $check = $ionAutHasher->check("password123", $ionAuthHash);

        $this->assertEquals(true, $check);


    }

    public function testLegacyHashInCorrectInput ()
    {

        $ionAuthHash = "28680678476f9e1c50da6e711675f179606c4e30"; // password123

        $ionAutHasher = new Karolina\User\Hashers\IonAuthLegacyHasher();

        $check = $ionAutHasher->check("password321", $ionAuthHash);

        $this->assertEquals(false, $check);


    }


    public function testNativeHashCorrectInput ()
    {

        $nativeHash = '$2y$10$LUKYAnaw9sSqK6BaBMYhxOyabD9wgX4IJewb6Gm2MsIKr7eeUBfGe'; // password123

        $nativeHasher = new Karolina\User\Hashers\NativeHasher();

        $check = $nativeHasher->check("password123", $nativeHash);

        $this->assertEquals(true, $check);


    }

    public function testNativeHashInCorrectInput ()
    {

        $nativeHash = '$2y$10$LUKYAnaw9sSqK6BaBMYhxOyabD9wgX4IJewb6Gm2MsIKr7eeUBfGe'; // password123

        $nativeHasher = new Karolina\User\Hashers\NativeHasher();

        $check = $nativeHasher->check("password321", $nativeHash);

        $this->assertEquals(false, $check);


    }

    public function testNativeHashHashing ()
    {

        print "hello world";


        $nativeHasher = new Karolina\User\Hashers\NativeHasher();

        $password = "password".time();

        $nativeHash = $nativeHasher->hash($password); 

        $check = $nativeHasher->check($password, $nativeHash);

        $this->assertEquals(true, $check);


    }


}

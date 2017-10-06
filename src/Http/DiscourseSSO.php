<?php

namespace Karolina\Http;

class DiscourseSSO
{
    private $secret;
    private $sso;
    private $payload;

    public function __construct()
    {
        $this->sso = new \Cviebrock\DiscoursePHP\SSOHelper();
    }

    public function validatePayload($payload, $signature)
    {
        if ($this->sso->validatePayload($payload, $signature)) {
            $this->payload = $payload;

            return true;
        }

        return false;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
        $this->sso->setSecret($secret);
    }


    public function getSignedStringForUser(\Karolina\User\User $user)
    {
        $nonce = $this->sso->getNonce($this->payload);

        $userId = $user->getId();
        $userEmail = $user->getEmail();

        $extraParameters = array(
            'name'     => $user->getName()
        );
    
        $string = $this->sso->getSignInString($nonce, $userId, $userEmail, $extraParameters);
        return $string;
    }
}

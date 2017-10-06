<?php

namespace Karolina\Http;

class Cookie
{
    private $maxAge = 60 * 60;
    private $allowInsecure = false;
    private $delight;

    public function setMaxAgeInHrs($hrs)
    {
        $this->maxAge = $hrs * 3600;
    }

    public function allowInsecure()
    {
        $this->allowInsecure = true;
    }

    public function setMaxAgeInDays($days)
    {
        $this->maxAge = $days * 86400;
    }

    public function setMaxAgeInSeconds($seconds)
    {
        $this->maxAge = $seconds;
    }



    public function set($name, $value)
    {
        $cookie = new \Delight\Cookie\Cookie($name);
        $cookie->setValue($value);
        $cookie->setMaxAge($this->maxAge);
        $cookie->setHttpOnly(true);
        $cookie->setDomain('kalli.agitator.dev');
        $cookie->setSameSiteRestriction('Strict');

        if ($this->allowInsecure == false) {
            $cookie->setSecureOnly(true);
        }

        $cookie->save();
    }


    public function get($name)
    {
        return \Delight\Cookie\Session::get($name);
    }
}

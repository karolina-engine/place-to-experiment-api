<?php

namespace Karolina;
use Noodlehaus\Config;

Class Platform {

    public $conf;
    public $Currency;
    private $eventPublisher;
    private $limiter;
    private $ci = NULL;

    private $settings;  use \Karolina\Setting\SettingTrait;


    public function __construct($configFile = false)
    {


        $this->setupConfig($configFile);
        $this->setupCurrency();
        $this->setupEvents();

        $this->limiter = new \Karolina\Event\RateLimiter();

    }

    public function getCurrencyISO () {

        return $this->Currency->getISO();
    }

    public function setCI ($ci) {

        $this->ci = $ci;

    }

    public function limit ($event, $limit, $timeframe) {

        $this->limiter->happen($event, $limit, $timeframe);

    }

    public function getSecretKey () {

        return getenv('platform_secret_key');
    }

    private function setupEvents () {

        $this->eventPublisher = \Karolina\Event\Publisher::instance();

    }

    public function publishEvent ($event) {

        $this->eventPublisher->publish($event);
    }

    public function subscribeToEvents ($subscriber) {

        $this->eventPublisher->subscribe($subscriber);
    }

    private function setupCurrency () {

        $currencyCode = $this->conf->get('currency');
        $this->Currency = new Currency($currencyCode);
    }

    public function getCurrency () {

        return $this->Currency;
    }


    private function setupConfig ($configFile) {

        if (!$configFile) {

            $configFile = getenv('platform_config');

        }

        $conf = Config::load(__DIR__.'/../platform_config/'.$configFile);
        $this->conf = $conf;

    }



    public function getPasswordResetUrl () {

        return getenv('protocol').'://'.$_SERVER['HTTP_HOST']."/auth/reset_password/";

    }

    public function getPledgeConfirmUrl () {

        return getenv('protocol').'://'.$_SERVER['HTTP_HOST']."/auth/confirm_pledge/";

    }


    public function getImgStorageUrl () {

        return "https://s3-eu-west-1.amazonaws.com/agitator-image-host/".$this->conf('key');

    }

    public function conf ($variable) {

        return $this->conf->get($variable);

    }




}

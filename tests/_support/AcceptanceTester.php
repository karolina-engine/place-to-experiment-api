<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /*
    * Some browsers need to wait between certain steps
    */
    public function waitBrowserDependent($waitTime, $onlyForBrowser = false)
    {
        // $this->comment($this->getScenario()->current('browser'));
        if (!$onlyForBrowser) {
            $this->wait($waitTime);
        } else {
            if ($this->getScenario()->current('browser') === $onlyForBrowser) {
                $this->wait($waitTime);
            }
        }
    }

    /*
    * Give the API call time to complete before reloading the page
    */
    public function waitForApiReload()
    {
        $this->waitBrowserDependent(2);
        $this->reloadPage();
    }

    /*
    * Remove the cookie notice
    */
    public function removeCookieNotice()
    {
        try {
            $this->click('#accept_cookie');
        } catch (Exception $e) {
            $this->comment('cookie notice not present');
        }
    }

    /*
    * Set up before the testing starts
    */
    public function setup()
    {
        $this->amOnPage('/');
    }

    /*
    * Tear down after the testing ends
    */
    public function teardown()
    {
    }

    /*
    * Some browsers store values differently
    */
    public function getBrowserDependantString($string, $browser)
    {
        if ($this->getScenario()->current('browser') === $browser) {
            return $string;
        } else {
            return '';
        }
    }

    /*
    * Some browsers need to wait between sending keys
    */
    public function fillFieldBrowserDependant($selector, $text)
    {
        if ($this->getScenario()->current('browser') === 'internet explorer' || $this->getScenario()->current('browser') === 'MicrosoftEdge') {
            $this->pressKey($selector, array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE);
            for ($i = 0; $i <= strlen($text); $i++) {
                $char = substr($text, $i, 1);
                $this->pressKey($selector, $char);
                $this->wait(0.5);
            }
        } else {
            $this->fillField($selector, $text);
        }
    }

    /*
    * General login procedure
    */
    public function login($email, $password)
    {
        $this->fillField(['name' => 'email'], $email);
        $this->fillField(['name' => 'password'], $password);

        if ($this->getScenario()->current('browser') === 'firefox') {
            // this is needed because firefox displays the "insecure passward warning" on non-https pages
            $this->pressKey(['name' => 'password'], \Facebook\WebDriver\WebDriverKeys::TAB);
        }

        $this->click('#login-submit');
        $this->waitBrowserDependent(5, 'safari');
        $this->waitBrowserDependent(3, 'firefox');
        $this->waitBrowserDependent(3, 'internet explorer');
        $this->waitBrowserDependent(3, 'MicrosoftEdge');

        // $this->seeElement('#nav-logout'); // does not work in Safari!
        $this->seeInCurrentUrl('/profile/view/');
    }
}

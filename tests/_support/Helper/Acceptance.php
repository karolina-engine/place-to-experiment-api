<?php
namespace Helper;

class Acceptance extends \Codeception\Module
{
    public function seePageHasElement($element)
    {
        try {
            $this->getModule('WebDriver')->_findElements($element);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {
            return false;
        }
        return true;
    }

    public function clickOnElement($element)
    {
        $this->getModule('WebDriver')->_findElements($element)[0]->click();
    }

    public function getElementText($element)
    {
        return $this->getModule('WebDriver')->_findElements($element)[0]->getText();
    }
    
    public function getTitle()
    {
        return $this->getModule('WebDriver')->webDriver->getTitle();
    }

    public function sendKeys($string)
    {
        $this->getModule('WebDriver')->webDriver->getKeyboard()->sendKeys($string);
    }
}

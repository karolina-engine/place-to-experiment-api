<?php
use PHPUnit\Framework\TestCase;
use Karolina\Currency;

class CurrencyTest extends TestCase
{


    public function testEuroFormatting () {

        $currency = new Currency('EUR');
        $this->assertEquals("€5", $currency->getFormatted('5'));

    }

    public function testNokFormatting () {

        $currency = new Currency('NOK', 'NOK');
        $this->assertEquals("5 NOK", $currency->getFormatted('5'));

    }

    public function testIskFormatting () {    

        $currency = new Currency('ISK');
        $this->assertEquals("ISK 200", $currency->getFormatted('200'));


    }
}

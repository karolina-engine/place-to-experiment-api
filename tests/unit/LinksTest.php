<?php
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Karolina\Content\Link;

class LinkTest extends TestCase
{

    public function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider additionProviderGoodLinks
     */

    public function testCreateLink ($goodLink, $domain) {


    	$link = new Link($goodLink, 'MBL news site');

        $this->assertEquals($goodLink, $link->getUrl());    	
        $this->assertEquals($domain, $link->getSite());    	

    }


    /**
     * @dataProvider additionProviderIncompleteLinks
     */

    public function testCreateIncompleteLink ($incompleteLink) {


    	$link = new Link($incompleteLink, 'MBL news site');

        $this->assertEquals("http://".$incompleteLink, $link->getUrl());    	

    }


    /**
     * @dataProvider additionProviderWhitepsaceLinks
     */

    public function testCreateTrimmedLink ($trimmedLink) {


    	$link = new Link($trimmedLink, 'MBL news site');

        $this->assertEquals(trim($trimmedLink), $link->getUrl());    	

    }
    /**
     * @dataProvider additionProviderBadLinks
     */

    public function testCreateBadLink ($badLink) {

        $this->expectException(Karolina\Exception::class);
    	$link = new Link($badLink, 'MBL news site');

    }




    public function additionProviderWhitepsaceLinks()
    {

        return [

        	[" http://www.mvl.is"],
        	["https://www.mbl.is "]

        ];
    }

    public function additionProviderBadLinks()
    {

        return [

        	["javascript:alert('hello')"],
        	["www. mvl.is"],
        	["https://www.mbl.is:javascript"]

        ];
    }


    public function additionProviderIncompleteLinks()
    {

        return [

        	["www.mvl.is"]

        ];
    }


    public function additionProviderGoodLinks()
    {

        return [

        	["http://www.mbl.is", "www.mbl.is"],
        	["https://www.karolinafund.com", "www.karolinafund.com"],
        	["https://www.karolinafund.com/project/view/1", "www.karolinafund.com"],
        	["https://www.facebook.com/karolinafund/", "facebook.com"],
        	["https://twitter.com/karolinafund/", "twitter.com"],

        ];
    }

}

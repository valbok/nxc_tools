<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS
 * @license GNU GPL v2
 * @package nxc_geo
 */

class nxcAcceptLanguageTest extends PHPUnit_Framework_TestCase
{

    public static function localMap()
    {
        return array(
                    array( 'en-us', array( 'eng-US' ) ),
                    array( 'eN-gb', array( 'eng-GB' ) ),
                    array( 'en-cA', array( 'eng-CA' ) ),
                    array( 'de-De', array( 'ger-DE' ) ),
                    array( 'zh-CN', array( 'chi-CN' ) ),
                    array( 'ua', array( 'ukr-UA' ) ),
                    array( 'ua-ua', array( 'ukr-UA' ) ),
                    array( 'zh', array( 'chi-CN', 'chi-HK', 'chi-TW' ) ),
                    array( 'en', array( 'eng-AU', 'eng-CA', 'eng-GB', 'eng-NZ', 'eng-US' ) ),
                    array( 'ua-unknown', array() ),
                );
    }

    /**
     * @dataProvider localMap
     */
    public function testLookupLocaleList( $v, $e )
    {
        $list = nxcAcceptLanguage::getLocaleListByLanguage( $v );
        $this->assertEquals( $e, $list );
    }

    public function testParse()
    {
        $o = new nxcAcceptLanguage( 'en-ca,en;q=0.1,  en-us ; q = 0.8,de-de;q=0.4,de;q=0.2' );

        $this->assertTrue( is_array( $o->getLanguageList() ) );
        $this->assertEquals( 5, count( $o->getLanguageList() ) );
    }

    public function testRedirectURL_EN()
    {
        $o = new nxcAcceptLanguage( 'ua,fr-fr;q=0.1,  en-us ; q = 0.3,de-de;q=0.4,ru;q=0.8' );
        $r = $o->getRedirectURL();

        $this->assertTrue( is_string( $r ) );
    }

    public function testRedirectURL_ZH()
    {
        $o = new nxcAcceptLanguage( 'ua, ru;q=0.8, zh-zh=0.1' );
        $r = $o->getRedirectURL( '/uri/test' );

        $this->assertFalse( $r );
    }
}
?>

<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS
 * @license GNU GPL v2
 * @package nxc_geo
 */

class nxcSiteAccessTest extends PHPUnit_Framework_TestCase
{
    public function testFetchList()
    {
        $list = nxcSiteAccess::fetchList();

        $this->assertTrue( is_array( $list ) );
        $this->assertTrue( count( $list ) > 0 );
    }

    public function testFetchListByLocale()
    {
        $list = nxcSiteAccess::fetchList();
        $o = current( $list );
        $list = nxcSiteAccess::fetchListByLocale( $o->getLocale() );

        $this->assertTrue( is_array( $list ) );
        $this->assertTrue( count( $list ) > 0 );
        foreach ( $list as $i )
        {
            $this->assertEquals( $o->getLocale(), $i->getLocale() );
        }
    }

    public function testGet()
    {
        $o = nxcSiteAccess::get( 'admin' );
        $this->assertTrue( $o instanceof nxcSiteAccess );
        $this->assertEquals( 'admin', $o->getName() );
    }

    public function testHostList()
    {
        $o = nxcSiteAccess::get();
        $this->assertTrue( is_array( $o->getHostList() ) );
    }

    public static function similarDomainMap()
    {
        return
            array(
                array( 'site.com', array( 'site.com' ), false ),
                array( 'site.com', array( 'notsite.com' ), 'notsite.com' ),
                array( 'en.site.com', array( 'en.site.com' ), false ),
                array( 'en.site.com', array( 'de.site.com' ), 'de.site.com' ),
                array( 'site.com', array( 'en.site.com' ), 'en.site.com' ),
                array( 'en.site.com', array( 'site.com' ), 'site.com' ),
                array( 'site.com', array( 'en.site.com', 'site.com', 'de.site.com', 'example.com' ), 'en.site.com' ),
                array( 'en.site.com', array( 'en.site.com', 'site.com', 'de.site.com' ), 'site.com' ),
                array( 'en.site.com', array( 'en.site.com', 'site.com' ), 'site.com' ),
                array( 'en.dom.site.com', array( 'en.site.com', 'site.com' ), false ),
                array( 'en.dom.site.com', array( 'en.site.com', 'site.com', 'en.dom.site.com' ), false ),
                array( 'en.dom.site.com', array( 'en.site.com', 'site.com', 'fr.dom.site.com' ), 'fr.dom.site.com' ),
                array( 'en.dom.site.com', array( 'en.site.com', 'site.com', 'fr.dom.ex.site.com' ), false ),
                array( 'site.com', array( 'site.com', 'de.en.site.com', 'en.site.com' ), 'en.site.com' ),
                array( 'site.com', array( 'site.com', 'de.en.site.com' ), false ),
            );
    }

    /**
     * @dataProvider similarDomainMap
     */
    public function testFindSimilarLanguageHost( $currentHost, $hostList, $expected )
    {
        $c = nxcSiteAccess::findSimilarHost( $currentHost, $hostList );
        $this->assertEquals( $expected, $c );
    }
}
?>

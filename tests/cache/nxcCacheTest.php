<?php
/**
 * @author VaL <vd@nxc.no>
 * @file nxcCacheTest.php
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Checks caching
 */
class nxcCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks if correct content is stored
     */
    public function testContent()
    {
        $c = new nxcCache( 'test' );
        $content = 'content';
        $c->store( $content );

        $this->assertEquals( $content,  $c->getContent() );
    }

    /**
     * Checks if correct content is stored
     */
    public function testContentWithoutStoring()
    {
        $c = new nxcCache( 'test' );

        $this->assertEquals( 'content', $c->getContent() );
    }

    /**
     * Checks if a cache is cleared
     */
    public function testClearingCacheByIndexList()
    {
        $c = new nxcCache( 'test' );

        $c->setIndexList( array( 'key1' => 'value1',
                                 'key2' => 'value2' ) );
        $c->store( 'content' );

        nxcCache::clearByIndexList( array( 'key1' => 'value1' ) );

        $c = new nxcCache( 'test' );
        $this->assertFalse( $c->getContent() );
    }

    /**
     * Checks if all cache is cleared
     */
    public function testClearingAllCache()
    {
        $c = new nxcCache( 'test1' );
        $c->store( 'content' );

        $c = new nxcCache( 'test2' );
        $c->store( 'content' );

        nxcCache::clearAll();

        $c = new nxcCache( 'test1' );
        $this->assertFalse( $c->getContent() );
        $c = new nxcCache( 'test2' );
        $this->assertFalse( $c->getContent() );
    }

}
?>
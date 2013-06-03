<?php
/**
 * @author VaL <vd@nxc.no>
 * @file nxcExceptionHandlerTest.php
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Checks exception handler
 */
class nxcExceptionHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks if correct title in error list is returned
     */
    public function testTitleInErrorList()
    {
        nxcExceptionHandler::add( new nxcException( 'test1' ), 'Title' );
        $this->assertArrayHasKey( 'Title', nxcExceptionHandler::getErrorList() );
    }

    /**
     * Checks if correct error list is returned
     */
    public function testErrorList()
    {
        nxcExceptionHandler::add( new nxcException( 'test1' ), 'Title' );
        $l = nxcExceptionHandler::getErrorList();
        $this->assertEquals( 'test1', $l['Title'][0] );
    }

    /**
     * Checks if correct error message list is returned
     */
    public function testErrorMessageList()
    {
        nxcExceptionHandler::add( new nxcException( 'test1' ), 'Title' );
        $l = nxcExceptionHandler::getErrorMessageList();
        $this->assertEquals( 'test1', $l[0] );
    }


}
?>
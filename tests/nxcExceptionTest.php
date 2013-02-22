<?php
/**
 * @author VaL <vd@nxc.no>
 * @file nxcExceptionTest.php
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Checks exceptions
 */
class nxcExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks if correct error message is returned
     */
    public function testErrorMessageWithoutDebug()
    {
        $e = new nxcException( 'test' );
        $error = $e->getErrorMessage( false );

        $this->assertEquals( 'test',  $error );
    }

    /**
     * Checks if correct content is stored
     */
    public function testErrorMessageWithDebug()
    {
        $e = new nxcException( 'test' );
        $error = $e->getErrorMessage( true );

        $this->assertRegExp( "/^\[nxcException\]: test\n.*/",  $error );
    }


}
?>
<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Test for suite loader
 */
class nxcTestSuiteLoaderTest extends PHPUnit_Framework_TestCase
{

    public function testFetchNode1()
    {
        $this->assertInstanceOf( 'eZContentObjectTreeNode', eZContentObjectTreeNode::fetch( 2 ) );
    }


    /**
     * After first test we fetch data from the database and check if it is ok
     * If it is not, seems configuration of the loader is not correct
     */
    public function testFetchNodeAgain()
    {
        $this->assertInstanceOf( 'eZContentObjectTreeNode', eZContentObjectTreeNode::fetch( 2 ) );
    }
}
?>
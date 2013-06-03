<?php
/**
 * Suite loader to run the test within ezp environment
 *
 * @author VaL <vd@nxc.no>
 * @file nxcTestSuiteLoader.php
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

require_once 'autoload.php';

class nxcTestSuiteLoader extends PHPUnit_Runner_StandardTestSuiteLoader
{
    /**
     * @var (eZScript)
     */
    static $Script = false;

    /**
     * Exits the script
     *
     * @return (void)
     */
    public static function shutdown()
    {
        self::getScript()->shutdown( 0 );
    }

    /**
     * Returns eZScript
     *
     * @return (eZScript)
     */
    protected static function getScript()
    {
        if ( self::$Script )
        {
            return self::$Script;
        }

        $scriptSettings = array();
        $scriptSettings['description'] = '';
        $scriptSettings['use-session'] = false;
        $scriptSettings['use-modules'] = true;
        $scriptSettings['use-extensions'] = true;

        self::$Script = eZScript::instance( $scriptSettings );
        return self::$Script;
    }

    /**
     * Initializes script to use settings, siteaccesses and other ezp features
     *
     * @return (void)
     */
    public static function initialize()
    {
        $script = self::getScript();
        $script->startup();

        // Workaround to skip include-path and loader to PHPUnit and to use all eZScripts options in tests
        $options = $script->getOptions( '[include-path][loader]' );

        $script->initialize();
    }

    /**
     * @reimp
     */
    function load($suiteClassName, $suiteClassFile = '', $syntaxCheck = FALSE)
    {
        return parent::load( $suiteClassName, $suiteClassFile, $syntaxCheck );
    }

    /**
     * @reimp
     */
    public function reload( ReflectionClass $aClass)
    {
        return parent::reload( $aClass );
    }
}

register_shutdown_function( array( 'nxcTestSuiteLoader', 'shutdown' ) );
nxcTestSuiteLoader::initialize();

?>

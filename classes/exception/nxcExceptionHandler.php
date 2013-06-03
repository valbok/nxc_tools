<?php
/**
 * @author VaL <vd@nxc.no>
 * @file nxcExceptionHandler.php
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Class to handle exceptions
 * @example
 *   nxcExceptionHandler::add( new nxcException( 'test1' ) );
 *   nxcExceptionHandler::add( new nxcException( 'test2' ), 'Runtime' );
 *   var_dump( nxcExceptionHandler::getErrorList() );
 */
class nxcExceptionHandler
{
    /**
     * List of errors:
     *   key   is the description of errors
     *   value is an array of exception messages
     *
     * @var (array)
     */
    protected static $ErrorList = array();

    /**
     * Adds exception message to error list
     *
     * @param (epubException) $e
     * @param (string)        $title Group name of exceptions
     * @param (bool)          $log TRUE means the error should be logged
     */
    public static function add( Exception $e, $title = false, $log = true )
    {
        if ( !$title )
        {
            $title = 'An error has occured';
        }

        $error = $e->getErrorMessage( true );

        if ( $log )
        {
            eZDebug::writeError( $error, $title );
            $ini = eZINI::instance( 'nxc-tools.ini' );
            eZLog::write( $error, $ini->variable( 'ExceptionSettings', 'FilenameLog' ) );
        }

        self::$ErrorList[$title][] = $e->getErrorMessage( false );
    }

    /**
     * Returns error list
     *
     * @return (array)
     */
    public static function getErrorList()
    {
        return self::$ErrorList;
    }

    /**
     * Returns error message list
     *
     * @return (array)
     */
    public static function getErrorMessageList()
    {
        $errorList = self::getErrorList();
        $result = array();

        foreach ( $errorList as $titleList )
        {
            foreach ( $titleList as $error )
            {
                $result[] = $error;
            }
        }

        return $result;
    }

}

?>

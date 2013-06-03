<?php
/**
 * Contains defenitions of exceptions
 *
 * @author vd
 * @file nxcException.php
 * @copyright Copyright (C) 2011 NXC AS.
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Base exception
 */
class nxcException extends Exception
{
    /**
     * Contains debug information
     *
     * @var (string)
     */
    protected $DebugMessage = '';

    /**
     * @param (string) $errorMessage
     * @param (string) $debugMessage
     */
    public function __construct( $errorMessage, $debugMessage = '' )
    {
        parent::__construct( $errorMessage );
        $this->setDebugMessage( $debugMessage );
    }

    /**
     * Gets the Exception's error message
     *
     * @param (bool) $showDebug Defines to include debug messages to result error
     *
     * @return (string)
     *
     * @TODO Check if debug is needed by default
     */
    public function getErrorMessage( $showDebug = true )
    {
        return $showDebug ? '[' . get_class( $this ) . ']: ' . $this->getMessage() . "\n" . $this->getDebugMessage() : $this->getMessage();
    }

    /**
     * Returns debug message
     *
     * @return (string)
     */
    protected function getDebugMessage()
    {
       return $this->DebugMessage . $this->getTraceAsString();
    }

    /**
     * Sets debug message
     */
    protected function setDebugMessage( $message )
    {
        $this->DebugMessage = $message . "\n\n";
    }
}


/**
 * If wrong or missed argument was passed
 */
class nxcInvalidArgumentException extends nxcException
{
}

/**
 * If an object was not found
 */
class nxcObjectNotFoundException extends nxcException
{
}

/**
 * If a user does not have access
 */
class nxcAccessDeniedException extends nxcException
{
}

/**
 * If any other cause not expressible by another exception
 */
class nxcRunTimeException extends nxcException
{
}

?>

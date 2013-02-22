<?php
/**
 * Handler for nxc-cache-block operator
 *
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2011 NXC AS
 * @license GNU GPL v2
 * @package nxc-tools
 */

class nxcToolsAutoload
{
    /**
     * @reimp
     */
    function __construct()
    {
    }

    /**
     * @reimp
     */
    public function operatorList()
    {
        return array( 'nxc_call_user_func_array' );
    }

    /**
     * @reimp
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * @reimp
     */
    function namedParameterList()
    {
        return array( 'nxc_call_user_func_array' => array(
                        'callback' =>
                            array( 'type' => 'string',
                                   'required' => true,
                                   'default' => '' ),
                        'params' =>
                            array( 'type' => 'array',
                                   'required' => false,
                                   'default' => array() ),
                        )
                    );
    }

    /**
     * @reimp
     */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'nxc_call_user_func_array':
            {
                try
                {
                    $operatorValue = false;
                    $name = $namedParameters['callback'];
                    $paramList = $namedParameters['params'];

                    $operatorValue = call_user_func_array( $name, $paramList );
                }
                catch( tbeException $e )
                {
                    tbeExceptionHandler::add( $e, __METHOD__ );
                }
                catch( Exception $e )
                {
                    eZDebug::writeError( $e->getMessage(), __METHOD__ );
                    eZDebug::writeError( $e->getTraceAsString(), __METHOD__ );
                }

            } break;
        }
    }

}

?>

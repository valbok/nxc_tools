#!/usr/bin/env php
<?php
/**
 * @author VaL <vd@nxc.no>
 * @file clear-cache.php
 * @copyright Copyright (C) 2011 NXC AS.
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Script that should be used to clear NXC cache
 */

require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Clears NXC cache" ),
                                     'use-session'    => false,
                                     'use-modules'    => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "",
                                "[key-list:]",
                                array( 'key-list' => 'Cache key list like nameID=1111 nameID=2222. If it is Empty all cache will be purged' )
                                );

$script->initialize();
$keyList = isset( $options['arguments'] ) ? $options['arguments'] : false;

if ( !$keyList )
{
    nxcCache::clearAll();
    $script->shutdown( 0 );
}

$result = array();
foreach( $keyList as $keyString )
{
    list( $key, $value ) = explode( '=', $keyString );
    $result[$key] = $value;
}

nxcCache::clearByIndexList( $result );

$script->shutdown( 0 );
?>

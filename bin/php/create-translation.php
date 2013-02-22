#!/usr/bin/env php
<?php
/**
 * @author  Serhey Dolgushev <serhey.dolgushev@nxc.no>
 * @date    15 Nov 2011
 **/
 
ini_set( 'memory_limit', '512M' );
set_time_limit( 0 );
 
require 'autoload.php';
 
$cli = eZCLI::instance();
$cli->setUseStyles( true );
 
$scriptSettings = array();
$scriptSettings['description']    = 'NXC Create new translations';
$scriptSettings['use-session']    = true;
$scriptSettings['use-modules']    = true;
$scriptSettings['use-extensions'] = true;
#$scriptSettings['site-access']    = 'siteadmin';
 
// Get script options
$script = eZScript::instance( $scriptSettings );
$script->startup();
$options = $script->getOptions(
        '[node_ids:][language_from:][language_to:]',
        '',
        array(
                'node_ids'      => 'Node IDs (separated by comma), childs of which will be translated (example: 11993,15409,15856,11995)',
                'language_from' => 'Base language (example: ger-DE)',
                'language_to'   => 'Translation language (example: ger-AT)'
        )
);
$script->initialize();

if( $options['node_ids'] === null ) {
        $cli->error( 'You should specify Node IDs' );
        $script->shutdown( 1 );
}

// Login by admin user
$ini           = eZINI::instance();
$userCreatorID = $ini->variable( 'UserSettings', 'UserCreatorID' );
$user          = eZUser::fetch( $userCreatorID );
if( ( $user instanceof eZUser ) === false ) {
        $cli->error( 'Cannot get user object by userID = "' . $userCreatorID . '". ( See site.ini [UserSettings].UserCreatorID )' );
        $script->shutdown( 1 );
}
@eZUser::setCurrentlyLoggedInUser( $user, $userCreatorID );
 
 
$languageFrom = trim( $options['language_from'] );
$languageTo   = trim( $options['language_to'] );
$translations = eZContentLanguage::fetchLocaleList();
 
if( in_array( $languageFrom, $translations ) === false ) {
        $cli->error( 'Not correct language_from options. Available options: ' . implode( ', ', $translations ) );
        $script->shutdown( 1 );
}
 
if( in_array( $languageTo, $translations ) === false ) {
        $cli->error( 'Not correct language_to options. Available options: ' . implode( ', ', $translations ) );
        $script->shutdown( 1 );
}

$contentLanguageFrom = eZContentLanguage::fetchByLocale( $languageFrom );
$contentLanguageTo =  eZContentLanguage::fetchByLocale( $languageTo );

$cli->output( 'Starting script...' );
$startTime = microtime( true );
 
// Get objects for which translations will be created
$objects     = array();
$nodeIDs     = explode( ',', $options['node_ids'] );
$fetchParams = array(
        'Depth'            => false,
        'LoadDataMap'      => false,
        'IgnoreVisibility' => true,
        'AsObject'         => true,
        'MainNodeOnly'     => false
);
foreach( $nodeIDs as $nodeID ) {
        $nodeID = trim( $nodeID );
        $node   = eZContentObjectTreeNode::fetch( $nodeID );
        if(
                $node instanceof eZContentObjectTreeNode === false
                || ( $object = $node->attribute( 'object' ) ) instanceof eZContentObject === false
        ) {
                continue;
        }
 
        if( isset( $objects[ $object->attribute( 'id' ) ] ) === false ) {
                $objects[ $object->attribute( 'id' ) ] = $object;
        }
 
        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $fetchParams, $nodeID );
        $cli->output( 'Processing "' . $node->attribute( 'name' ) . '" Node ID: ' . $nodeID . ': ' . count( $nodes ) . ' childs' );
        foreach( $nodes as $node ) {
                $object = $node->attribute( 'object' );
                if(
                        $object instanceof eZContentObject === false
                        || isset( $objects[ $object->attribute( 'id' ) ] )
                ) {
                        continue;
                }
 
                $objects[ $object->attribute( 'id' ) ] = $object;
 
                // Make translations not only for child node`s objects, but for related objects too.
                $relatedObjects = $object->relatedObjects(
                        false,
                        false,
                        0,
                        false,
                        array(
                                'AllRelations'     => true,
                                'IgnoreVisibility' => true
                        )
                );
                foreach( $relatedObjects as $relatedObject ) {
                        if( isset( $objects[ $relatedObject->attribute( 'id' ) ] ) === false ) {
                                $objects[ $relatedObject->attribute( 'id' ) ] = $relatedObject;
                        }
                }
        }
}
 
// Creating translations
$cli->output( 'Creating trnaslations...' );
 
$skipErrors   = array(
        'language_from' => 0,
        'language_to'   => 0
);
 
$c = count( $objects );
$i = 1;
$k = 0;

foreach( $objects as $object ) {
	if ( !$object->attribute( 'main_node' ) )
	{
            $cli->output( 'Skipping due to there is no main node' );
	    continue;
	}
        $cli->output(
                'Creating translation for "'
                . $object->attribute( 'name' ) . '" ('
                . $object->attribute( 'main_node' )->attribute( 'url_alias' ) . ') '
                . $i . '/' . $c
                . ' (' . number_format( memory_get_usage( true ) / 1024 / 1024, 2 ) . ' Mb)'
        );
        $i++;
 
        $skip         = false;
        $translations = $object->attribute( 'current' )->translations( false );
 
        //if( count( $object->fetchDataMap( false, $languageFrom ) ) === 0 ) {
        if( in_array( $languageFrom, $translations ) === false ) {
                $skipErrors['language_from'] += 1;
                $cli->output( 'Skipped. Reason: there is no original translation for current object' );
                $skip = true;
        }
 
        if(
                $skip === false
                && in_array( $languageTo, $translations )
        ) {
                $skipErrors['language_to'] += 1;
                $cli->output( 'Skipped. Reason: destination translation for current object already exist' );
                $skip = true;
        }
 
        if( $skip === false ) {
                $version = $object->createNewVersion( false, true, $languageTo, $languageFrom );
                $object->commitInputRelations( $version->attribute( 'version' ) );
                $object->resetInputRelationList();
                eZOperationHandler::execute(
                        'content',
                        'publish',
                        array(
                                'object_id' => $object->attribute( 'id' ),
                                'version'   => $version->attribute( 'version' )
                        )
                );
		
                $k++;
        }

	eZContentOperationCollection::updateInitialLanguage( $object->attribute( 'id' ), $contentLanguageTo->attribute( 'id' ) );
	eZContentOperationCollection::removeTranslation( $object->attribute( 'id' ), array( $contentLanguageFrom->attribute( 'id' ) ) );
 
        $object->resetDataMap();
        eZContentObject::clearCache( $object->attribute( 'id' ) );
}
 
$cli->output( $k . '/' . $c . ' translations were created.' );
if( $skipErrors['language_from'] > 0 ) {
        $cli->output( $skipErrors['language_from'] . '/' . $c . ' objects were skipped. Reason: there is no original translation.' );
}
if( $skipErrors['language_to'] > 0 ) {
        $cli->output( $skipErrors['language_to'] . '/' . $c . ' objects were skipped. Reason: destination translation already exist.' );
}
 
$executionTime   = microtime( true ) - $startTime;
$memoryPeakUsage = memory_get_peak_usage( true ) / 1024 / 1024;
$cli->output( 'Script took ' . $executionTime . ' seconds. Peak memory usage: ' . number_format( $memoryPeakUsage, 2 ) . ' Mb.' );
 
$script->shutdown( 0 );
?>

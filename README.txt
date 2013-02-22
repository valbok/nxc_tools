NXC Tools
=========

The extension provides a list of useful tools:
- nxcCache
- nxcException
- nxcTestSuiteLoader for phpunit

1. nxcCache
-----------
    It is a lib that is used to cache any data.
    It can store a cache in a file system or in a database.

    Example:
        $cache = new nxcCache( 'key' );
        $cache->store( 'content' );
        $content = $cache->getContent();
        $cache->setIndexList( array( 'test' => 1 ) );
        nxcCache::clearByIndexList( array( 'test' => 1 ) );

2. nxcException
---------------
    Provides a list of exceptions:
    - nxcException - Base exception
    - nxcInvalidArgumentException - If wrong or missed argument was passed
    - nxcObjectNotFoundException - If an object was not found
    - nxcAccessDeniedException - If a user does not have access
    - nxcRunTimeException - If any other cause not expressible by another exception

    And nxcExceptionHandler that is used to handle the exceptions like
    try
    {
        throw new nxcException( 'error' );
    }
    catch ( nxcException $e )
    {
        nxcExceptionHandler::add( $e );
    }

    var_dump( nxcExceptionHandler::getErrorList() );
    var_dump( nxcExceptionHandler::getErrorMessageList() );

3. nxcTestSuiteLoader
---------------------
    Is used to use phpunit to test eZ Publish solutions.
    Some tests have to use ini settings from specified siteaccess or eZP default classes.
    There are few ways to use nxcTestSuiteLoader.
    Use one of the following:
    - Bash script (RECOMMENDED)
           $ cd [PATH TO EZP]; ./extension/nxc_tools/bin/phpunit.sh ./extension/nxc_tools/tests/
    - Include path in php.ini
        1. Add [PATH TO EZP]/extension/nxc_tools/classes/TestSuiteLoader/ to include_path in php.ini
        2. After that you can run phpunit like:
           $ cd [PATH TO EZP]; phpunit --loader TestSuiteLoader ./extension/nxc_tools/tests/
    - Sym link
        1. $ cd [PATH TO EZP]; ln -s extension/nxc_tools/classes/TestSuiteLoader/nxcTestSuiteLoader.php .
        2. $ cd [PATH TO EZP]; phpunit --loader TestSuiteLoader ./extension/nxc_tools/tests/
    - Without sym link
           $ phpunit --include-path=./extension/nxc_tools/classes/TestSuiteLoader/ --loader=nxcTestSuiteLoader ./extension/nxc_tools/tests/

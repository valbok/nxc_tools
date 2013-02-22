<?php
/**
 * @author vd
 * @file nxcCache.php
 * @copyright Copyright (C) 2011 NXC AS.
 * @license GNU GPL v2
 * @pakacge nxc_tools
 */

/**
 * Class to store content into cache.
 * Supports cluster installation.
 *
 * @example
 *     $cache = new nxcCache( 'key' );
 *     $cache->store( 'content' );
 *
 *     $content = $cache->getContent();
 *     $cache->setIndexList( array( 'test' => 1 ) );
 *     nxcCache::clearByIndexList( array( 'test' => 1 ) );
 */
class nxcCache
{
    /**
     * Cache dir name
     */
    const CacheDir = 'nxc-cache';

    /**
     * Dirname where index will be located
     */
    const IndexDir = 'nxc-cache-index';

    /**
     * Cached content
     *
     * @var (bytes)
     */
    protected $Content = false;

    /**
     * Cluster handler
     *
     * @var (eZClusterFileHandler)
     */
    protected $ClusterHandler = false;

    /**
     * @param (string) $dir Where content is placed
     * @param (string) $key a key of content
     */
    public function __construct( $key, $dir = false )
    {
        $this->Path = eZDir::path( array( self::getDir( $dir ), md5( $key ) ) );
        $this->ClusterHandler = eZClusterFileHandler::instance( $this->Path );
    }

    /**
     * Creates index list for current cache.
     * It will create index file named using key=>value with current cache file path.
     * If the file already exist it will append by new value.
     * To clear cache: create index key, find the file, and get the list of cache filepaths which is related to this key.
     * After that purge these files.
     *
     * @param (array) $indexList array( 'nameID' => 1, 'confirmationNumber' = 2 )
     *
     * @return (void)
     */
    public function setIndexList( $indexList )
    {
        if ( !$indexList )
        {
            return;
        }

        foreach ( $indexList as $key => $value )
        {
            $indexKey = self::getIndexKey( $key, $value );
            $index = self::fetchIndex( $indexKey );
            $path = $this->ClusterHandler->name();
            if ( in_array( $path, $index ) )
            {
                continue;
            }

            $index[] = $path;
            self::storeIndex( $indexKey, $index );
        }
    }

    /**
     * Fetches index by key
     *
     * @param (string) $value Index key
     * @return (bytes)
     */
    protected static function fetchIndex( $key )
    {
        $cache = new self( $key, self::IndexDir );
        $list = $cache->exists() ? unserialize( $cache->getContent() ) : array();

        return $list;
    }

    /**
     * Stores index value by
     *
     * @param (string) $key Index key
     * @param (array) $valueList What should be stored
     *
     * @return (void)
     */
    protected static function storeIndex( $key, $valueList )
    {
        $cache = new self( $key, self::IndexDir );
        if ( !$valueList )
        {
            $cache->delete();
            return;
        }

        $cache->store( serialize( $valueList ) );
    }

    /**
     * Returns index key for cache
     *
     * @param (string) $key Field name
     * @param (string) $value Its value
     *
     * @return (string)
     */
    protected static function getIndexKey( $key, $value )
    {
        return $key . '=' . $value;
    }

    /**
     * Returns dir path for cache
     *
     * @return (string)
     */
    protected static function getDir( $dir )
    {
        $dir = $dir ? str_replace( '..', '', $dir ) : '';

        return eZDir::path( array( 'var', 'cache', self::CacheDir, $dir ) );
    }

    /**
     * Checks for existence of cache file
     *
     * @return bool
     */
    public function exists()
    {
        return $this->ClusterHandler->exists();
    }

    /**
     * Returns modification time of cache file
     *
     * @return int
     */
    public function getModificationTime()
    {
        return $this->ClusterHandler->mtime();
    }

    /**
     * Checks if the cache is expired
     *
     * @return bool
     */
    public function isExpired( $ttl )
    {
        return ( $this->getModificationTime() + $ttl ) <= time();
    }

    /**
     * Stores \a $content
     */
    public function store( $content )
    {
        $content = serialize( $content );
        $this->Content = $content;
        $this->ClusterHandler->storeContents( $content, 'binaryfile', self::CacheDir );
    }

    /**
     * Fetches content from cache
     *
     * @return bytes
     */
    public function getContent()
    {
        return $this->Content !== false ? unserialize( $this->Content ) : ( $this->exists() ? unserialize( $this->ClusterHandler->fetchContents() ) : false );
    }

    /**
     * Clears current cache
     *
     * @return (void)
     */
    public function delete()
    {
        $this->ClusterHandler->delete();
    }

    /**
     * Clears cache by dir/filename
     *
     * @param (string) $dir
     */
    public static function clearByPath( $path = '' )
    {
        self::deleteByPath( self::getDir( $path ) );
    }

    /**
     * Deletes file or dir
     *
     * @param (string) Dir or path to filename
     *
     * @return (void)
     */
    protected static function deleteByPath( $path )
    {
        eZClusterFileHandler::instance( $path )->delete();
    }

    /**
     * Clears cache by keys
     *
     * @param (array) $indexList array( 'nameID' => 1, 'nameID' => 2 )
     *
     * @return (void)
     */
    public static function clearByIndexList( $indexList = array() )
    {
        foreach ( $indexList as $key => $value )
        {
            $indexKey = self::getIndexKey( $key, $value );
            $index = self::fetchIndex( $indexKey );
            foreach ( $index as $pathKey => $filePath )
            {
                self::deleteByPath( $filePath );
                unset( $index[$pathKey] );
            }

            self::storeIndex( $indexKey, $index );
        }
    }

    /**
     * Clears all nxc cache
     *
     * @return (void)
     */
    public static function clearAll()
    {
        self::clearByPath();
    }

}

?>

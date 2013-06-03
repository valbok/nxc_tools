<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS
 * @license GNU GPL v2
 * @package nxc_tools
 */

/**
 * Class to handle some functionalities that belong to ez site accesses
 */
class nxcSiteAccess
{
    /**
     * @var (string)
     */
    protected $Name = false;

    /**
     * @reimp
     */
    function __construct( $siteaccess )
    {
        $this->Name = $siteaccess;
    }

    /**
     * @return (string)
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @return (string)
     */
    public function getLocale()
    {
        return eZSiteAccess::getIni( $this->getName() )->variable( 'RegionalSettings', 'Locale' );
    }

    /**
     * @return (array(__CLASS__))
     */
    public static function fetchList()
    {
        $result = array();
        $ini = eZINI::instance();
        $list = $ini->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
        foreach ( $list as $item )
        {
            $result[$item] = new self( $item );
        }

        return $result;
    }

    /**
     * @return (array(__CLASS__))
     */
    public static function fetchListByLocale( $locale )
    {
        $result = array();
        $list = self::fetchList();
        foreach ( $list as $item )
        {
            if ( $item->getLocale() == $locale )
            {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return (__CLASS__)
     */
    public static function get( $siteaccess = false )
    {
        if ( !$siteaccess )
        {
            $siteaccess = $GLOBALS['eZCurrentAccess']['name'];
        }

        return new self( $siteaccess );
    }

    /**
     * Returns hosts of siteaccess
     *
     * @return (array)
     */
    public function getHostList()
    {
        $siteaccess = $this->Name;
        $result = array();
        $ini = eZINI::instance();
        $list = $ini->variable( 'SiteAccessSettings', 'HostMatchMapItems' );
        foreach ( $list as $item )
        {
            $e = explode( ';', $item );
            if ( !isset( $e[1] ) or $e[1] != $siteaccess )
            {
                continue;
            }

            $result[] = $e[0];
        }

        return $result;
    }

    /**
     * Returns domain by siteaccess
     * It will choose proper domain based on current host
     *
     * @return (string)
     */
    public function getDomain( $currentHost = false )
    {
        if ( !$currentHost )
        {
            $currentHost = eZSys::hostname();
        }

        $siteaccess = $this->Name;
        static $cached = array();
        if ( isset( $cached[$siteaccess] ) )
        {
            return $cached[$siteaccess];
        }

        $reversedList = array_reverse( explode( '.', $currentHost ) );

        $max = 0;
        $result = false;
        $list = $this->getHostList();
        foreach ( $list as $sa )
        {
            $i = 0;
            // Try to find the most similar host to current
            $candidateList = array_reverse( explode( '.', $sa ) );
            foreach ( $candidateList as $key => $c )
            {
                if ( isset( $reversedList[$key] ) and $c == $reversedList[$key] )
                {
                    $i++;
                }
            }

            if ( $i > $max )
            {
                $result = $sa;
                $max = $i;
            }
        }

        $cached[$siteaccess] = $result;

        return $result;
    }

    /**
     * Returns actual redirection url which represents a locale of current siteaccess
     * By default if the site is configured to use uri matching, it will return just uri to switch to current siteaccess
     * If matching is host it will find proper host from the list
     *
     * @return (string)
     */
    public function getRedirectURL( $uri = '' )
    {
        $result = "/switchlanguage/to/" . $this->getName();

        $ini = eZINI::instance();
        $matchOrder = $ini->variable( 'SiteAccessSettings', 'MatchOrder' );
        if ( strpos( $matchOrder, 'host' ) !== false )
        {
            $result = false;
            $d = self::findSimilarHost( eZSys::hostname(), $this->getHostList() );
            if ( $d )
            {
                $result = eZSys::serverProtocol() . '://' . $d;
            }
        }

        return $result . $uri;
    }

    /**
     * Is supposed to be called if MatchOrder is host and few different sites installed on the same ez installation
     * Returns first found domain.
     *
     * @return (string)
     */
    public static function findSimilarHost( $currentHost, $siteAccessHostList )
    {
        $result = false;
        $ec = explode( '.', $currentHost );
        unset( $ec[0] );
        $currentHostSubdomainless = implode( '.', $ec );

        foreach ( $siteAccessHostList as $host )
        {
            if ( $host == $currentHost )
            {
                continue;
            }

            $e = explode( '.', $host );
            unset( $e[0] );
            $hostSubdomainless = implode( '.', $e );

            // 1. current host without subdomain is equal to host without subdomain:
            //    en.site.com == de.site.com
            // 2. current host is subdomain of host:
            //    site.com == en.site.com
            // 3. host is subdomain of current host
            //    en.site.com == site.com
            if ( $currentHostSubdomainless == $hostSubdomainless or
                 $currentHost == $hostSubdomainless  or
                 $currentHostSubdomainless == $host
                )
            {
                $result = $host;
                break;
            }
        }

        return $result;
    }

}
?>

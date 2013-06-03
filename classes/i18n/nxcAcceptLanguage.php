<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS
 * @license GNU GPL v2
 * @package nxc_i18n
 */

/**
 * Class to handle determining ez locale and its siteaccess by accept language string
 *
 * @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
 * @see self::redirect()
 */
class nxcAcceptLanguage
{
    /**
     * Original Accept Language value
     *
     * @var (string)
     */
    protected $Value = false;

    /**
     * Parsed languages
     *
     * @var (array)
     * @see self::parse()
     */
    protected $ParsedList = array();

    /**
     * @reimp
     */
    public function __construct( $lang = false )
    {
        if ( !$lang )
        {
            $lang = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
        }

        $this->Value = $lang;
        $this->ParsedList = self::parse( $lang );
    }

    /**
     * @return (string)
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @return (array)
     */
    public function getParsedList()
    {
        return $this->ParsedList;
    }

    /**
     * Returns preferred and accepted languages sorted by quality value
     *
     * @return (array)
     * @see self::parse()
     */
    public function getLanguageList()
    {
        return array_keys( $this->ParsedList );
    }

    /**
     * Returns suggested url to redirect users to.
     * This url represents a locale based on accept language string.
     *
     * @param (string)
     * @return (string)
     */
    public function getRedirectURL( $uri = '' )
    {
        $result = false;
        $currentLocale = nxcSiteAccess::get()->getLocale();
        $ec = explode( '-', $currentLocale );
        $currentBaseLang = $ec[0];

        $langList = $this->getLanguageList();
        foreach ( $langList as $langKey => $lang )
        {
            $localeList = self::getLocaleListByLanguage( $lang );
            foreach ( $localeList as $key => $locale )
            {
                $e1 = explode( '-', $locale );
                $baseLang = $e1[0];

                // If current siteaccess has the same locale as most preffered
                // or it the same base language, like eng-US == eng-GB, no need to redirect
                if ( $langKey == 0 and ( $locale == $currentLocale or $currentBaseLang == $baseLang ) )
                {
                    break( 2 );
                }

                $siteAccessList = nxcSiteAccess::fetchListByLocale( $locale );
                foreach ( $siteAccessList as $sa )
                {
                    $url = $sa->getRedirectURL( $uri );
                    if ( $url )
                    {
                        $result = $url;
                        break( 3 );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Performs actual redirection
     *
     * @return (void)
     */
    public function redirect()
    {
        $url = $this->getRedirectURL( eZSys::requestURI() );
        if ( !$url )
        {
            return;
        }

        header( "Location: $url" );
        eZExecution::cleanExit();
    }

    /**
     * Extracts and converts languages from ACCEPT-LANGUAGE string.
     * Sorts languages by quality value.
     *
     * @param (string)
     * @return (array)
     */
    public static function parse( $value )
    {
        if ( !$value )
        {
            return array();
        }

        $result = array();
        $e = explode( ',', $value );
        foreach ( $e as $v )
        {
            $semi = explode( ';', trim( $v ) );
            $q = isset( $semi[1] ) ? explode( '=', $semi[1] ) : array( 'q', '1' );
            $result[trim( $semi[0] )] = trim( $q[1] );
        }

        uasort( $result, 'self::cmpParsedList' );

        return $result;
    }

    /**
     * @return (int)
     * @see self::parse()
     */
    protected static function cmpParsedList( $a, $b )
    {
        if ( $a == $b )
        {
            return 0;
        }

        return ( $a > $b ) ? -1 : 1;
    }

    /**
     * Returns eZ Publish locales that belong to this accept language
     *
     * @param (string) en-en, en-gb, ua, en etc
     * @return (array)
     */
    public static function getLocaleListByLanguage( $lang )
    {
        $result = array();
        $lang = strtolower( $lang );
        $e = explode( '-', $lang );
        $isRange = !isset( $e[1] );

        $list = eZLocale::localeList( true );
        foreach ( $list as $o )
        {
            $locale = $o->attribute( 'locale_code' );
            $c = strtolower( $o->attribute( 'http_locale_code' ) );
            if ( $isRange )
            {
                if ( strpos( $c, $lang ) === 0 )
                {
                    if ( !in_array( $locale, $result ) )
                    {
                        $result[] = $locale;
                    }
                }

                continue;
            }

            if ( $c == $lang )
            {
                $result[] = $o->attribute( 'locale_code' );
                break;
            }
        }

        return $result;
    }

}
?>

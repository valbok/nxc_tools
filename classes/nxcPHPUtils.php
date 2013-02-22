<?php
/**
 * @author dl
 * @copyright Copyright (C) 2011 NXC AS.
 * @pakacge nxc_tools
 */

/**
 * Class to hanlde php interanals
 */
class nxcPHPUtils {

    /**
     * Convert array to stdClass recursively
     *
     * @param (array)       array to convert
     *
     * @return (object)     converted array
     */
    public static function arrayToObject( $d ) {
		if( is_array( $d ) ) {
			//
			// Return array converted to object
			// Using 'arrayToObject' for recursive call
			//
            //return (object) array_map('self::arrayToObject', $d);
            return new nxcObject( array_map('self::arrayToObject', $d) );
		}
		else {
			// Return object
			return $d;
		}
	}

    /**
     * Convert object to array recursively
     *
     * @param (object)      object to convert
     *
     * @return (array)      converted object
     */
    public static function objectToArray( $d ) {
		if( is_object( $d ) ) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars( $d );
		}

		if( is_array( $d ) ) {
			//
			// Return array converted to object
			// Using 'objectToArray' for recursive call
			//
			return array_map('self::objectToArray', $d);
		}
		else {
			// Return array
			return $d;
		}
	}

}

?>

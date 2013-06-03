<?php
/**
 * @author dl
 * @copyright Copyright (C) 2011 NXC AS.
 * @pakacge nxc_tools
 */

/**
 * Wrapper for stdClass to support 'attribute()', 'setAttribute()' methods
 */
class nxcObject extends stdClass {

    /**
     * Construct nxcObject from $src
     *
     * @param (object or array)
     */
    public function __construct( $src ) {
        if( $src ) {
            $this->copy( $src );
        }
    }

    /**
     * Copies fields to current object from specified list
     *
     * @return (void)
     */
    protected function copy( $src ) {
        foreach ( $src as $key => $value ) {
            $this->$key = $value;
        }
    }

    /**
     * Checks if requested field exists.
     *
     * @return (bool)
     */
    public function __isset( $name ) {
        return isset( $this->$name );
    }

    /**
     * @note Is needed for using in tpls
     */
    public function hasAttribute( $name ) {
        return $this->__isset( $name );
    }


    /**
     * Returns field or custom values.
     *
     * @return (mixed)
     */
    public function attribute( $name ) {
        if ( !$this->hasAttribute( $name ) ) {
            eZDebug::writeError( "Attribute '$name' does not exist", __METHOD__ );
            return false;
        }

        return $this->$name;
    }

    /**
     * @param (string) $attr Field name
     * @param (string) $value Field value
     *
     * @return (mixed) Value of field
     */
    public function setAttribute( $attr, $value ) {
        $this->$attr = $value;

        return $this;
    }

    /**
     * Returns all attributes that exist for this object
     *
     * @return (array)
     */
    public function attributes() {
        $attrs = get_object_vars( $this );
        $attrs = array_keys( $attrs );

        return $attrs;
    }
}

?>

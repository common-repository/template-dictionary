<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin field type class.
 */
abstract class TemplateDictionary_Type {

	/**
	 * Type name.
	 * @var string
	 */
	protected $type;

	/**
	 * Table type – 'int', 'varchar' or 'string'. 
	 * @var string
	 */
	protected $table_type;

	/**
	 * Types, which this type can be changed to (without deleting values).
	 * @var array of strings
	 */
	protected $changeable_to = array();

	/**
	 * Setting options.
	 * @var array
	 */
	protected $options = array();

	/**
	 * Constructor.
	 * @param string|null $options JSON with setting options.
	 */
	public function __construct( $options = null ){
		if( $options !== null ){
			$this->options = json_decode( $options, true );
		}
	}

	/**
	 * Get the name of type.
	 * @return string
	 */
	public final function type(){
		return $this->type;
	}

	/**
	 * Get the table type.
	 * @return string
	 */
	public final function table_type(){
		return $this->table_type;
	}

	/**
	 * Is this type changeable to the given type?
	 * @param string $type 
	 * @return boolean
	 */
	public final function is_changeable_to( $type ){
		if( is_string( $type ) ){
			return in_array( $type, $this->changeable_to );
		}
		else {
			return in_array( $type->type, $this->changeable_to );
		}
	}

	/**
	 * Get setting option.
	 * @param string $name 
	 * @return string|int
	 */
	public final function get_option( $name ){
		return isset( $this->options[ $name ] ) ? $this->options[ $name ] : null;
	}

	/**
	 * Get options from $_POST and return JSON.
	 * @return string
	 */
	public final function get_options_from_post(){
		foreach ( array_keys( $this->get_option_fields() ) as $opt ) {
			$name = $this->type . '_' . $opt;
			if( isset( $_POST[ $name ] ) ){
				if( $this->is_check_option( $opt ) ){
					$this->options[$opt] = true;
				}
				else {
					$this->options[$opt] = $_POST[ $name ];
				}
			}
			else {
				$this->options[$opt] = false;
			}
		}
		return json_encode( $this->options );
	}

	/**
	 * Is the option a checkbox?
	 * @param string $name 
	 * @return boolean
	 */
	public final function is_check_option( $name ){
		return ( strlen( $name ) > 6 && substr( $name, -6) == '_check' );
	}

	/**
	 * Get the option fields for this type.
	 * @return array
	 */
	public function get_option_fields(){
		return array();
	}

	/**
	 * Display the form field for value.
	 * @param string $lang 
	 * @param string|int|null $value 
	 */
	public function display_field( $lang, $value = null ){}

	/**
	 * Show the value.
	 * @param string|int $value 
	 * @return string
	 */
	public function list_value( $value ){
		return $value;
	}

	/**
	 * Get the value from $_POST.
	 * @param string $key 
	 * @return string|int|null
	 */
	public function get_post_value( $key ){
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
	}

	/**
	 * Sanitize the value before using in database.
	 * @param string|int $value 
	 * @return string|int
	 */
	public function sanitize( $value ){
		return $value;
	}

	/**
	 * Validate the value.
	 * @param string|int $value 
	 * @return boolean
	 */
	public function validate( $value ){
		return true;
	}

}

?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Date extends TemplateDictionary_Type {

	protected $type = 'date';

	protected $table_type = 'varchar';

	protected $changeable_to = array( 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function display_field( $lang, $value = null ){
		printf( '<input type="text" name="value_%1$s" id="value_%1$s" value="%2$s" class="datepicker" size="30" />', $lang, isset($value) ? $value : '' );
	}

	public function sanitize( $value ){
		return sanitize_text_field( $value );
	}

	public function validate( $value ){
		if ( !$value ) {
			return true;
		}
		try {
			new DateTime( $value );
			return true;
		}
		catch ( Exception $e ) {
			return false;
		}
	}

}

?>
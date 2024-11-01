<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Email extends TemplateDictionary_Type {

	protected $type = 'email';

	protected $table_type = 'varchar';

	protected $changeable_to = array( 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function display_field( $lang, $value = null ){
		printf( '<input type="email" name="value_%1$s" id="value_%1$s" value="%2$s" size="50" />', $lang, isset($value) ? $value : '' );
	}

	public function validate( $value ){
		return is_email( $value ) || $value === '';
	}

}

?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Wysiwyg extends TemplateDictionary_Type {

	protected $type = 'wysiwyg';

	protected $table_type = 'text';

	protected $changeable_to = array( 'string', 'text' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function display_field( $lang, $value = null ){
		wp_editor( isset($value) ? $value : '', 'value_' . $lang );
	}

	public function sanitize( $value ){
		return wp_kses_post( $value );
	}

}

?>
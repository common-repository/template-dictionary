<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_String extends TemplateDictionary_Type {

	protected $type = 'string';

	protected $table_type = 'varchar';

	protected $changeable_to = array( 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'maxlength' => array(
				'html' => '<input type="number" name="string_maxlength" id="string_maxlength" value="%1$s" />',
				'caption' => __( 'max length', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$opt_maxlength = $this->get_option('maxlength');
		printf( '<input type="text" name="value_%1$s" id="value_%1$s" value="%2$s" maxlength="%3$s" size="50" />', $lang, isset($value) ? $value : '', $opt_maxlength );
		if( $opt_maxlength ){
			printf( '<p class="description">%1$s: %2$s</p>', __( 'max length', 'template-dictionary' ), $opt_maxlength );
		}
	}

	public function sanitize( $value ){
		$opt_maxlength = $this->get_option('maxlength');
		if( $opt_maxlength && mb_strlen($value) > $opt_maxlength ){
			$value = mb_substr( $value, 0, $opt_maxlength );
		}
		return sanitize_text_field( $value );
	}

}

?>
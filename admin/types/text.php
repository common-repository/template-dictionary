<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Text extends TemplateDictionary_Type {

	protected $type = 'text';

	protected $table_type = 'text';

	protected $changeable_to = array( 'string', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'maxlength' => array(
				'html' => '<input type="number" name="text_maxlength" id="text_maxlength" value="%1$s" />',
				'caption' => __( 'max length', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$opt_maxlength = $this->get_option('maxlength');
		printf( '<textarea name="value_%1$s" id="value_%1$s" maxlength="%3$s" rows="5" cols="60">%2$s</textarea>', $lang, isset($value) ? $value : '', $opt_maxlength );
		if( $opt_maxlength ){
			printf( '<p class="description">%1$s: %2$s</p>', __( 'max length', 'template-dictionary' ), $opt_maxlength );
		}
	}

	public function sanitize( $value ){
		$opt_maxlength = $this->get_option('maxlength');
		if( $opt_maxlength && mb_strlen($value) > $opt_maxlength ){
			$value = mb_substr( $value, 0, $opt_maxlength );
		}
		return sanitize_textarea_field( $value );
	}

}

?>
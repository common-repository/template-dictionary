<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Select extends TemplateDictionary_Type {

	protected $type = 'select';

	protected $table_type = 'varchar';

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'options' => array(
				'html' => '<textarea name="select_options" id="select_options" rows="10" cols="40">%1$s</textarea>',
				'caption' => __( 'options', 'template-dictionary' ),
				'desc' => __( 'every option on single line', 'template-dictionary' ),
			),
			'select2_check' => array(
				'html' => '<input type="checkbox" name="select_select2_check" value="1" id="select_select2_check" %1$s/>',
				'caption' => __( 'use select2', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$value = isset($value) ? $value : '';
		printf( '<select name="value_%1$s" id="value_%1$s" %2$s>', $lang, $this->get_option('select2_check') ? ' class="select2"' : '' );
		$opt_options = $this->get_option('options');
		$opt_options = explode( PHP_EOL, $opt_options );
		foreach ($opt_options as $o) {
			printf( '<option value="%1$s" %2$s>%1$s</option>', $o, selected( $o, $value, false ) );
		}
		echo '</select>';
	}

}

?>
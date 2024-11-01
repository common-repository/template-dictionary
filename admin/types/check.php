<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Check extends TemplateDictionary_Type {

	protected $type = 'check';

	protected $table_type = 'int';

	protected $changeable_to = array( 'int', 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function display_field( $lang, $value = null ){
		printf( '<input type="checkbox" name="value_%1$s" id="value_%1$s" value="1" %2$s />', $lang, checked( 1, $value, false ) );
	}

	public function list_value( $value ){
		if( $value === null ){
			return null;
		}
		else if ( (int)$value === 1 ){
			return 'true';
		}
		else {
			return 'false';
		}
	}

	public function get_post_value( $key ){
		return ( isset( $_POST[ $key ] ) && $_POST[ $key ] == 1 ) ? 1 : 0;
	}

}

?>
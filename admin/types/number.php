<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Number extends TemplateDictionary_Type {

	protected $type = 'number';

	protected $table_type = 'int';

	protected $changeable_to = array( 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'min' => array(
				'html' => '<input type="number" name="number_min" id="number_min" value="%1$s" />',
				'caption' => __( 'minimal value', 'template-dictionary' ),
			),
			'max' => array(
				'html' => '<input type="number" name="number_max" id="number_max" value="%1$s" />',
				'caption' => __( 'maximal value', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$opt_min = $this->get_option('min');
		$opt_max = $this->get_option('max');
		printf( '<input type="number" name="value_%1$s" id="value_%1$s" value="%2$s" min="%3$s" max="%4$s" />', $lang, $value, $opt_min, $opt_max );
		if( $opt_min || $opt_max ){
			$desc = array();
			if( $opt_min ){
				$desc[] = __( 'minimal value', 'template-dictionary' ) . ': ' . $opt_min;
			}
			if( $opt_max ){
				$desc[] = __( 'maximal value', 'template-dictionary' ) . ': ' . $opt_max;
			}
			echo '<p class="description">' . implode( ', ' , $desc ) . '</p>';
		}
	}

	public function sanitize( $value ){
		return ( $value || $value === 0 ) ? (int)$value : null;
	}

	public function validate( $value ){
		if( $value === null || $value === '' ){
			return true;
		}
		$value = (int)$value;
		$opt_min = $this->get_option('min');
		if( $opt_min && $value < $opt_min ){
			return false;
		}
		$opt_max = $this->get_option('max');
		if( $opt_max && $value > $opt_max ){
			return false;
		}
		return true;
	}

}

?>
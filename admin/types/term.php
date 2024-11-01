<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Term extends TemplateDictionary_Type {

	protected $type = 'term';

	protected $table_type = 'int';

	protected $changeable_to = array( 'number', 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'taxonomy' => array(
				'html' => '<input type="text" name="term_taxonomy" id="term_taxonomy" value="%1$s" />',
				'caption' => __( 'taxonomy', 'template-dictionary' ),
				'desc' => __( 'separate multiple taxonomies with commas', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$opt_taxonomy = $this->get_option('taxonomy');
		$taxonomies = $opt_taxonomy ? explode( ',', $opt_taxonomy ) : '';
		$args = array(
			'taxonomy'   => $taxonomies,
			'number'     => 0,
			'lang'       => $lang,
			'hide_empty' => false
		);
		$terms = get_terms( $args );
		printf( '<select name="value_%1$s" id="value_%1$s" class="select2">', $lang );
		echo '<option value=""></option>';
		foreach ($terms as $term) {
			$tax_obj = get_taxonomy( $term->taxonomy );
			printf( '<option value="%1$s" %4$s>%2$s (%3$s)</option>', $term->term_id, $term->name, $tax_obj->labels->singular_name, selected( $term->term_id, $value, false ) );
		}
		echo '</select>';
	}

	public function list_value( $value ){
		if( ! $value ){
			return null;
		}
		$term = get_term( (int)$value );
		if( $term ){
			return $term->name . ' (id:' . $value . ')';
		}
		return null;
	}

	public function sanitize( $value ){
		return (int)$value;
	}

}

?>
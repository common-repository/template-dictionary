<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_PostMultiple extends TemplateDictionary_Type {

	protected $type = 'post_multiple';

	protected $table_type = 'varchar';

	protected $changeable_to = array( 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'posttype' => array(
				'html' => '<input type="text" name="post_multiple_posttype" id="post_multiple_posttype" value="%1$s" />',
				'caption' => __( 'post type(s)', 'template-dictionary' ),
				'desc' => __( 'separate multiple post types with commas', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		$opt_posttype = $this->get_option('posttype');
		$types = $opt_posttype ? explode( ',', $opt_posttype ) : '';
		$args = array(
			'post_type'   => $types,
			'numberposts' => -1,
			'lang'        => $lang,
		);
		$posts = get_posts( $args );
		$values = explode( ',', $value );
		printf( '<select name="value_%1$s[]" id="value_%1$s" class="select2" multiple="multiple">', $lang );
		echo '<option value=""></option>';
		foreach ($posts as $post) {
			$posttype_obj = get_post_type_object( $post->post_type );
			printf(
				'<option value="%1$s" %4$s>%2$s (%3$s)</option>',
				$post->ID,
				$post->post_title,
				$posttype_obj->labels->singular_name,
				in_array( $post->ID, $values ) ? 'selected="selected"' : ''
			);
		}
		echo '</select>';
	}

	public function list_value( $value ){
		$values = explode( ',', $value );
		$output = array();
		foreach ( $values as $value ) {
			$post = get_post( (int)$value );
			if( $post ){
				$output[] = $post->post_title . ' (id:' . $value . ')';
			}
		}
		return $output ? implode( '<br />', $output ) : null;
	}

	public function get_post_value( $key ){
		if( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ){
			return implode( ',', $_POST[ $key ] );
		}
		return null;
	}

	public function sanitize( $value ){
		return preg_replace( '/[^0-9,]/', '', $value );
	}

}

?>
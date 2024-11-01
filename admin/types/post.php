<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Post extends TemplateDictionary_Type {

	protected $type = 'post';

	protected $table_type = 'int';

	protected $changeable_to = array( 'number', 'string', 'text', 'wysiwyg', 'post_multiple' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'posttype' => array(
				'html' => '<input type="text" name="post_posttype" id="post_posttype" value="%1$s" />',
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
		printf( '<select name="value_%1$s" id="value_%1$s" class="select2">', $lang );
		echo '<option value=""></option>';
		foreach ($posts as $post) {
			$posttype_obj = get_post_type_object( $post->post_type );
			printf( '<option value="%1$s" %4$s>%2$s (%3$s)</option>', $post->ID, $post->post_title, $posttype_obj->labels->singular_name, selected( $post->ID, $value, false ) );
		}
		echo '</select>';
	}

	public function list_value( $value ){
		$post = get_post( (int)$value );
		if( $post ){
			return $post->post_title . ' (id:' . $value . ')';
		}
		return null;
	}

	public function sanitize( $value ){
		return (int)$value;
	}

}

?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateDictionary_Type_Media extends TemplateDictionary_Type {

	protected $type = 'media';

	protected $table_type = 'int';

	protected $changeable_to = array( 'number', 'string', 'text', 'wysiwyg' );

	public function __construct( $options = null ){
		parent::__construct( $options );
	}

	public function get_option_fields(){
		return array(
			'mimetypes' => array(
				'html' => '<input type="text" name="media_mimetypes" id="media_mimetypes" value="%1$s" />',
				'caption' => __( 'mime types', 'template-dictionary' ),
				'desc' => __( 'separate multiple mime types with commas', 'template-dictionary' ),
			),
		);
	}

	public function display_field( $lang, $value = null ){
		echo '<div class="media-wrapper">';
		echo '<div class="image-preview-wrapper">';
		$src = '';
		if( $value ){
			$mimetype = get_post_mime_type( $value );
			if( in_array( $mimetype, array( 'image/jpeg', 'image/png', 'image/gif' ) ) ){
				$src = wp_get_attachment_image_src( $value, 'thumbnail' );
				if ($src) {
					$src = $src[0];
				}
				else {
					$src = wp_mime_type_icon( $mimetype );
				}
			}
			else {
				$src = wp_mime_type_icon( $mimetype );
			}
			printf( '<img src="%1$s" />', $src );
		}
		echo '</div>';
		echo '<span class="file-name">';
		if( $value ){
			$url = wp_get_attachment_url( $value );
			echo basename( $url );
		}
		echo '</span>';
		$mimetypes = $this->get_option('mimetypes');
		printf( '<input type="hidden" name="value_%1$s" value="%2$s" data-mimetype="%3$s" />', $lang, $value, $mimetypes );
		printf( '<button id="value_%1$s" class="set_media_image button">%2$s</button> ', $lang, __( 'Choose File' ) );
		printf( '<button class="remove_media_image button%2$s">%1$s</button>', __( 'Remove' ), $src ? '' : ' hidden' );
		echo '</div>';
		if( $mimetypes ){
			printf( '<p class="description">%1$s: %2$s</p>', __( 'mime types', 'template-dictionary' ), $mimetypes );
		}
	}

	public function list_value( $value ){
		if( !$value ){
			return null;
		}
		$mimetype = get_post_mime_type( $value );
		$url = wp_get_attachment_url( $value );
		if( in_array( $mimetype, array( 'image/jpeg', 'image/png', 'image/gif' ) ) ){
			$src = wp_get_attachment_image_src( $value, 'thumbnail' );
			if ($src) {
				$src = $src[0];
			}
			else {
				$src = wp_mime_type_icon( $mimetype );
			}
		}
		else {
			$src = wp_mime_type_icon( $mimetype );
		}
		return sprintf( '<a href="%1$s" target="_blank"><img src="%2$s" /><span class="file-name">%3$s</span></a>', $url, $src, basename( $url ) );
	}

	public function sanitize( $value ){
		return (int)$value;
	}

}

?>
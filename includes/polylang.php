<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function template_dictionary_polylang_load_language( $lang ){
	if( !is_admin() ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if( is_plugin_active( 'polylang/polylang.php' ) && function_exists( 'pll_current_language' ) ){
			$lang = pll_current_language();
		}
	}
	return $lang;
}
add_filter( 'template_dictionary_language', 'template_dictionary_polylang_load_language' );

function template_dictionary_polylang_load_languages( $langs ){
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if( is_plugin_active( 'polylang/polylang.php' && function_exists( 'pll_languages_list' ) ) ){
		$langs = pll_languages_list();
	}
	return $langs;
}
add_filter( 'template_dictionary_languages', 'template_dictionary_polylang_load_languages' );

?>
<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TemplateDictionary_Admin_ListTableValues extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'value',
			'plural'   => 'values',
			'ajax'     => false
		) );
	}

	function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	function column_code( $item ) {

		$actions = array(
			'edit' => sprintf( '<a href="?page=template_dictionary_edit_value&id=%1$s">%2$s</a>', $item['id'], __( 'Edit' ) ),
		);

		if( $item['value'] !== null ){
			$actions['delete'] = sprintf( '<a href="?page=%1$s&action=%2$s&id=%3$s&lang=%4$s&_wpnonce=%5$s">%6$s</a>', $_REQUEST['page'], 'delete', $item['id'], $item['lang'], wp_create_nonce('template_dictionary'), __( 'Delete' ) );
		}

		return sprintf('%1$s <span style="color:silver">(%2$s)</span>%3$s',
			$item['code'],
			$item['type'],
			$this->row_actions($actions)
		);
	}

	function column_value( $item ) {
		$admin = TemplateDictionary_Admin::get_instance();
		$type = $item['type'];
		$type = $admin->get_type( $type );
		return $type->list_value( $item['value'] );
	}

	function get_columns() {
		$columns = array(
			'code' => __( 'Code', 'template-dictionary' ),
			'lang' => __( 'Language', 'template-dictionary' ),
			'value' => __( 'Value', 'template-dictionary' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'code' => array( 'code', false ),
			'lang' => array( 'lang', false ),
			'value' => array( 'value', false ),
		);
		return $sortable_columns;
	}

	function prepare_items() {
		global $wpdb;

		$user = get_current_user_id();
		$screen = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page_option = get_user_meta($user, $screen_option, true);
		if ( empty ( $per_page_option) || $per_page_option < 1 ) {
			$per_page_option = $screen->get_option( 'per_page', 'default' );
		}

		$admin = TemplateDictionary_Admin::get_instance();
		$langs_count = count( $admin->get_langs() );
		if( $langs_count > $per_page_option ){
			$per_page = $langs_count;
		}
		else if( $per_page_option % $langs_count == 0 ){
			$per_page = $per_page_option;
		}
		else {
			$per_page = $per_page_option + ( $langs_count - ( $per_page_option % $langs_count ) );
		}

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();

		$total_items = $this->get_settings_count();

		$this->items = $this->get_settings( $per_page, ( $current_page - 1 ) * $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}

	private function get_settings_count(){
		global $wpdb;

		$td = TmplDict();

		$table_main = $td->db_tables( 'table_main' );

		$admin = TemplateDictionary_Admin::get_instance();
		$langs = $admin->get_langs();
		$lang = $langs[0];
		$langs_sql = "SELECT '$lang' AS lang";
		for ($i=1; $i < count( $langs ); $i++) {
			$lang = $langs[$i];
			$langs_sql .= " UNION ALL SELECT '$lang'";
		}

		$sql = "SELECT count(*) FROM $table_main t, ( $langs_sql ) l";

		$count = $wpdb->get_var( $sql );

		return (int)$count;
	}

	public function get_settings( $limit, $offset ){
		global $wpdb;

		$td = TmplDict();

		extract( $td->db_tables() );

		$admin = TemplateDictionary_Admin::get_instance();
		$langs = $admin->get_langs();
		$lang = $langs[0];
		$langs_sql = "SELECT '$lang' AS lang";
		for ($i=1; $i < count( $langs ); $i++) {
			$lang = $langs[$i];
			$langs_sql .= " UNION ALL SELECT '$lang'";
		}

		$sql = "SELECT t.id, code, type, l.lang, value
				FROM ( $table_main t, ( $langs_sql ) l )
				LEFT JOIN (
					SELECT id, value, lang FROM $table_int
					UNION ALL
					SELECT id, value, lang FROM $table_varchar
					UNION ALL
					SELECT id, value, lang FROM $table_text
					) ts
					ON t.id = ts.id AND l.lang = ts.lang";

		$sql .= ' ORDER BY ';
		$sql .= ! empty( $_REQUEST['orderby'] ) ? esc_sql( $_REQUEST['orderby'] ) : 'code';
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';

		$sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results;
	}

	public function no_items() {
		echo __( 'No settings found.', 'template-dictionary' );
	}

}

?>
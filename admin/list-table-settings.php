<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TemplateDictionary_Admin_ListTableSettings extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'setting',
			'plural'   => 'settings',
			'ajax'     => false
		) );
	}

	function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	function column_code( $item ) {

		$actions = array(
			'edit' => sprintf( '<a href="?page=template_dictionary_add_edit_setting&id=%1$s">%2$s</a>', $item['id'], __( 'Edit' ) ),
			'delete' => sprintf( '<a href="?page=%1$s&action=%2$s&id=%3$s&_wpnonce=%4$s">%5$s</a>', $_REQUEST['page'], 'delete', $item['id'], wp_create_nonce('template_dictionary'), __( 'Delete' ) ),
		);

		return $item['code'] . $this->row_actions($actions);
	}

	function column_options( $item ) {
		if( empty( $item['options'] ) ){
			return '';
		}
		$options = json_decode( $item['options'], true );
		$output = '<div class="settings-options-container">';
		foreach ($options as $key => $value) {
			if( $value !== '' ){
				$output .= '<div class="item"><span class="name">' . $key . '</span>' . nl2br( $value ) . '</div>';
			}
		}
		$output .= '</div>';
		return $output;
	}

	function get_columns() {
		$columns = array(
			'code' => __( 'Code', 'template-dictionary' ),
			'type' => __( 'Type', 'template-dictionary' ),
			'options' => __( 'Options', 'template-dictionary' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'code' => array( 'code', false ),
			'type' => array( 'type', false ),
		);
		return $sortable_columns;
	}

	function prepare_items() {
		global $wpdb;

		$user = get_current_user_id();
		$screen = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta($user, $screen_option, true);
		if ( empty ( $per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
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

		$sql = "SELECT count(*) FROM $table_main";

		$count = $wpdb->get_var( $sql );

		return (int)$count;
	}

	private function get_settings( $limit, $offset ){
		global $wpdb;

		$td = TmplDict();

		$table_main = $td->db_tables( 'table_main' );

		$sql = "SELECT id, code, type, options FROM $table_main";

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
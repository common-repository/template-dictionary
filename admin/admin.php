<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The TemplateDictionary_Admin class.
 */
class TemplateDictionary_Admin {

	/**
	 * The instance of admin class.
	 * @var TemplateDictionary_Admin
	 */
	private static $instance = null;

	/**
	 * The list of available languages.
	 * @var array of strings
	 */
	private $langs;

	/**
	 * The list of available field types.
	 * @var array of strings
	 */
	private $types;

	/**
	 * The current admin page of this plugin.
	 * @var string|null
	 */
	private $current_plugin_page = null;

	/**
	 * Notices to print.
	 * @var array of arrays
	 */
	private $notices = array();

	/**
	 * Variables for plugin pages.
	 * @var array of variables
	 */
	private $page_variables;

	/**
	 * Langs whose cache should be deleted.
	 */
	private $transient_langs_to_delete = array();

	/**
	 * Returns the TemplateDictionary_Admin instance.
	 * @static
	 * @return TemplateDictionary
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * The constructor.
	 */
	private function __construct(){
		add_action( 'plugins_loaded', array( $this, 'default_lang_check' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load_languages' ), 6 );
		add_action( 'plugins_loaded', array( $this, 'load_types' ), 7 );
		add_action( 'plugins_loaded', array( $this, 'handle_post_and_get' ), 8 );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		add_action( 'admin_menu', array( $this, 'add_pages' ) );

		add_filter( 'template_dictionary_language', array( $this, 'filter_template_dictionary_language' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3);

		$this->set_current_plugin_page();
	}

	/**
	 * Clone is not allowed.
	 */
	public function __clone()
	{
		wp_die( 'Clone is not allowed.' );
	}

	/**
	 * Unserialization is not allowed.
	 */
	public function __wakeup()
	{
		wp_die( 'Unserialization is not allowed.' );
	}

	/**
	 * Set the current_plugin_page property.
	 */
	public function set_current_plugin_page(){
		if( isset( $_GET['page'] ) ){
			$page = $_GET['page'];
			$pages = array(
				'template_dictionary'                  => 'dictionary_list',
				'template_dictionary_settings'         => 'settings_list',
				'template_dictionary_add_edit_setting' => 'add_edit_setting',
				'template_dictionary_edit_value'       => 'edit_value',
				'template_dictionary_export_import'    => 'export_import',
			);
			if( isset( $pages[ $page ] ) ){
				$this->current_plugin_page = $pages[ $page ];
			}
		}
	}

	/**
	 * Load field types.
	 */
	public function load_types(){
		include_once __DIR__ . '/type.php';

		include_once __DIR__ . '/types/string.php';
		include_once __DIR__ . '/types/number.php';
		include_once __DIR__ . '/types/text.php';
		include_once __DIR__ . '/types/wysiwyg.php';
		include_once __DIR__ . '/types/email.php';
		include_once __DIR__ . '/types/select.php';
		include_once __DIR__ . '/types/date.php';
		include_once __DIR__ . '/types/post.php';
		include_once __DIR__ . '/types/post-multiple.php';
		include_once __DIR__ . '/types/media.php';
		include_once __DIR__ . '/types/check.php';
		include_once __DIR__ . '/types/term.php';

		$types = array(
			'string'        => 'TemplateDictionary_Type_String',
			'number'        => 'TemplateDictionary_Type_Number',
			'text'          => 'TemplateDictionary_Type_Text',
			'wysiwyg'       => 'TemplateDictionary_Type_Wysiwyg',
			'email'         => 'TemplateDictionary_Type_Email',
			'select'        => 'TemplateDictionary_Type_Select',
			'date'          => 'TemplateDictionary_Type_Date',
			'post'          => 'TemplateDictionary_Type_Post',
			'post_multiple' => 'TemplateDictionary_Type_PostMultiple',
			'media'         => 'TemplateDictionary_Type_Media',
			'check'         => 'TemplateDictionary_Type_Check',
			'term'          => 'TemplateDictionary_Type_Term',
		);
		
		$this->types = apply_filters( 'template_dictionary_types', $types );
	}

	/**
	 * Set the TmplDict language in admin.
	 */
	public function filter_template_dictionary_language( $lang ){
		$available_languages = apply_filters( 'template_dictionary_languages', array( $lang ) );

		$user_lang = substr( $this->get_current_user_locale(), 0, 2 );

		if( in_array( $user_lang, $available_languages ) ){
			return $user_lang;
		}

		return $lang;
	}

	private function get_current_user_locale(){
		$user = wp_get_current_user();
		$locale = $user->locale;
    	return $locale ? $locale : get_locale();
	}

	public function default_lang_check(){
		if( ! $this->current_plugin_page ){
			return;
		}

		if( ! defined( 'TMPL_DICT_DEFAULT_LANG' ) ){
			$lang = TmplDict()->get_lang();
			$this->add_notice(
				sprintf(
					__( 'The %1$s constant is not set. Default language %2$s is set from site locale. We recommend you to add this line to wp-config.php:', 'template-dictionary' ),
					'"TMPL_DICT_DEFAULT_LANG"',
					'"' . $lang . '"'
				) . '<br />' . sprintf( '<code>define( "TMPL_DICT_DEFAULT_LANG", "%s" )</code>', $lang ),
				'notice-info'
			);
		}
	}

	/**
	 * Load available languages.
	 */
	public function load_languages(){
		if( isset( $this->langs ) ){
			return;
		}

		$langs = array( TmplDict()->get_lang() );

		$langs = apply_filters( 'template_dictionary_languages', $langs );

		$this->langs = array_map( 'esc_sql', $langs );
	}

	/**
	 * Get available languages.
	 * @return array of strings
	 */
	public function get_langs(){
		return $this->langs;
	}

	/**
	 * Get instance of given type.
	 * @param string $type 
	 * @param string|null $options JSON object of field options.
	 * @return TemplateDictionary_Type|null
	 */
	public function get_type( $type, $options = null ){
		if( isset( $this->types[ $type ] ) ){
			return new $this->types[ $type ]( $options );
		}
		return null;
	}

	/**
	 * Does the field type exist?
	 * @param string $type 
	 * @return boolean
	 */
	private function type_exists( $type ){
		return isset( $this->types[ $type ] ) && $this->types[ $type ];
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function scripts_and_styles(){

		$page = $this->current_plugin_page;
		if ( $page == 'edit_value' ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-style', plugins_url( 'css/jquery-ui.min.css', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui-structure-style', plugins_url( 'css/jquery-ui.structure.min.css', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui-theme-style', plugins_url( 'css/jquery-ui.theme.min.css', __FILE__ ) );

			wp_enqueue_script( 'select2-script', plugins_url( 'plugins/select2/js/select2.min.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'select2-style', plugins_url( 'plugins/select2/css/select2.min.css', __FILE__ ) );

			wp_enqueue_media();
		}
		else if ( $page == 'export_import' ){
			wp_enqueue_script( 'select2-script', plugins_url( 'plugins/select2/js/select2.min.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'select2-style', plugins_url( 'plugins/select2/css/select2.min.css', __FILE__ ) );
		}

		wp_enqueue_style( 'template_dictionary_admin_css', plugins_url( 'css/admin.css', __FILE__ ) );
		wp_enqueue_script( 'template_dictionary_admin_script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ) );

	}

	/**
	 * Add admin pages.
	 */
	public function add_pages(){
		$page_dictionary_list = add_menu_page(
			'Template Dictionary',
			'Template Dictionary',
			'manage_options',
			'template_dictionary',
			array( $this, 'page_dictionary_list' ),
			'dashicons-media-text',
			null
		);
		$page_settings_list = add_submenu_page(
			'template_dictionary',
			__( 'Settings list', 'template-dictionary' ),
			__( 'Settings list', 'template-dictionary' ),
			'manage_options',
			'template_dictionary_settings',
			array( $this, 'page_settings_list' )
		);
		add_submenu_page(
			'template_dictionary',
			__( 'Add settings', 'template-dictionary' ),
			__( 'Add settings', 'template-dictionary' ),
			'manage_options',
			'template_dictionary_add_edit_setting',
			array( $this, 'page_add_edit_setting' )
		);
		add_submenu_page(
			'template_dictionary',
			__( 'Export/Import', 'template-dictionary' ),
			__( 'Export/Import', 'template-dictionary' ),
			'manage_options',
			'template_dictionary_export_import',
			array( $this, 'page_export_import' )
		);
		add_submenu_page(
			'template_dictionary',
			__( 'Add value', 'template-dictionary' ),
			__( 'Add value', 'template-dictionary' ),
			'manage_options',
			'template_dictionary_edit_value',
			array( $this, 'page_edit_value' )
		);

		add_action( 'load-' . $page_dictionary_list, array( $this, 'screen_options_dictionary_list' ) );
		add_action( 'load-' . $page_settings_list, array( $this, 'screen_options_settings_list' ) );
	}

	/**
	 * Set the screen options.
	 */
	public function set_screen_options( $status, $option, $value ){
		if( $option === 'template_dictionary_per_page' ){
			return $value;
		}
		if( $option === 'template_dictionary_settings_per_page' ){
			return $value;
		}
		return $status;
	}

	/**
	 * Add screen options for dictionary list page.
	 */
	public function screen_options_dictionary_list(){
		$args = array(
			'default' => 10,
			'option' => 'template_dictionary_per_page',
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Add screen options for settings list page.
	 */
	public function screen_options_settings_list(){
		$args = array(
			'default' => 10,
			'option' => 'template_dictionary_settings_per_page',
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Handle POST and GET on plugins pages.
	 */
	public function handle_post_and_get(){
		$page = $this->current_plugin_page;
		if( ! $page ){
			return;
		}
		$this->{'handle_' . $page}();

		if( $this->transient_langs_to_delete ){
			foreach ( $this->transient_langs_to_delete as $lang ) {
				delete_transient( TmplDict()->transient_name( $lang ) );
			}
		}
	}

	/**
	 * Handle POST and GET on values_list page.
	 */
	private function handle_dictionary_list(){
		if( isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {
			if( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'template_dictionary' ) ){
				wp_nonce_ays( 'template_dictionary' );
			}
			$id = isset( $_GET['id'] ) ? $_GET['id'] : null;
			$lang = isset( $_GET['lang'] ) ? $_GET['lang'] : null;
			if( $id && $lang ){
				$result = $this->delete_value( $id, $lang );
				if( $result ){
					$this->add_notice( __( 'The value was successfully deleted.', 'template-dictionary' ), 'notice-success' );
				}
				else {
					$this->add_notice( __( 'The value was not deleted.', 'template-dictionary' ), 'notice-error' );
				}
			}
		}
	}

	/**
	 * Handle POST and GET on settings_list page.
	 */
	private function handle_settings_list(){
		if( isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {
			if( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'template_dictionary' ) ){
				wp_nonce_ays( 'template_dictionary' );
			}
			$id = isset( $_GET['id'] ) ? $_GET['id'] : null;
			if( $id ){
				$result = $this->delete_setting( $id );
				if( $result ){
					$this->add_notice( __( 'The setting was successfully deleted.', 'template-dictionary' ), 'notice-success' );
				}
				else {
					$this->add_notice( __( 'The setting was not deleted.', 'template-dictionary' ), 'notice-error' );
				}
			}
		}
	}

	/**
	 * Handle POST and GET on edit_setting page.
	 */
	private function handle_add_edit_setting(){
		$id = 0;
		$code = '';
		$type = '';
		$options = array();

		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			if( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'template_dictionary' ) ){
				wp_nonce_ays( 'template_dictionary' );
			}
			$code = $_POST['code'];
			$type = $this->get_type( $_POST['type'] );
			$options = $type->get_options_from_post();
			if( ! $this->validate_code( $code ) ){
				$this->add_notice( __( 'The code is not valid.', 'template-dictionary' ), 'notice-error' );
			}
			else if( isset( $_POST['id'] ) && is_numeric($_POST['id']) ) {
				$id = $_POST['id'];
				if( $this->is_conflict_setting_code( $code, $id ) > 0 ){
					$this->add_notice( __( 'This code already exists.', 'template-dictionary' ), 'notice-error' );
					$id = 0;
				}
				else {
					$old_type = $this->get_type( $_POST['old_type'] );
					$result = $this->update_setting( $id, $code, $type, $old_type, $options );
					if( $result ){
						$this->add_notice( __( 'The setting was successfully updated.', 'template-dictionary' ), 'notice-success' );
					}
					else {
						$this->add_notice( __( 'The setting was not updated.', 'template-dictionary' ), 'notice-error' );
					}
				}
			}
			else {
				if( $this->is_conflict_setting_code( $code ) > 0 ){
					$this->add_notice( __( 'This code already exists.', 'template-dictionary' ), 'notice-error' );
					$id = 0;
				}
				else {
					$result = $this->save_setting( $code, $type, $options );
					if( is_int( $result ) ){
						$id = $result;
						$this->add_notice( __( 'The setting was successfully saved.', 'template-dictionary' ), 'notice-success' );
					}
					else{
						$this->add_notice( __( 'The setting was not saved.', 'template-dictionary' ), 'notice-error' );
					}
				}
			}
		}

		if( $id == 0 && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ){
			$id = $_GET['id'];
			$setting = $this->get_setting( $id );
			if( $setting ){
				$code = $setting['code'];
				$options = $setting['options'];
				$type = $this->get_type( $setting['type'], $options );
			}
			else {
				$id = 0;
				$this->add_notice( __( 'The setting was not found.', 'template-dictionary' ), 'notice-error' );
			}
		}

		$this->page_variables = array(
			'id'     => $id,
			'code'   => $code,
			'type'   => $type,
			'options' => $options,
		);
	}

	/**
	 * Handle POST and GET on edit_value page.
	 */
	private function handle_edit_value(){
		$id = 0;
		$code = '';
		$type = null;
		$values = array();

		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			if( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'template_dictionary' ) ){
				wp_nonce_ays( 'template_dictionary' );
			}
			if( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
				$id = $_POST['id'];
				$setting = $this->get_setting( $id );
				if( $setting ){
					$code = $setting['code'];
					$options = $setting['options'];
					$type = $this->get_type( $setting['type'], $options );
					foreach ( $this->get_langs() as $lang ) {
						$value = $type->get_post_value( 'value_' . $lang );
						if( $type->validate( $value ) ){
							$values[ $lang ] = $type->sanitize( $value );
						}
						else {
							$this->add_notice( sprintf( __( 'The value "%1$s" for lang "%2$s" is not valid.', 'template-dictionary' ), $value, $lang ), 'notice-error' );
						}
					}
					$result = $this->update_values( $id, $values, $type->table_type() );
					if( $result ){
						$this->add_notice( __( 'The values were successfully saved.', 'template-dictionary' ), 'notice-success' );
					}
					else {
						$this->add_notice( __( 'Some values were not saved.', 'template-dictionary' ), 'notice-error' );
					}
				}
				else {
					$id = 0;
					$this->add_notice( __( 'Setting was not found.', 'template-dictionary' ), 'notice-error' );
				}
			}
			else {
				$this->add_notice( __( 'Setting was not found.', 'template-dictionary' ), 'notice-error' );
			}
		}
		else if( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ){
			$id = $_GET['id'];
			$setting = $this->get_setting( $id );
			if( $setting ){
				$code = $setting['code'];
				$options = $setting['options'];
				$type = $this->get_type( $setting['type'], $options );
				$values = $this->get_values( $id, $type->table_type() );
			}
			else {
				$id = 0;
				$this->add_notice( __( 'Setting was not found.', 'template-dictionary' ), 'notice-error' );
			}
		}
		else {
			$this->add_notice( __( 'Setting was not found.', 'template-dictionary' ), 'notice-error' );
		}

		$this->page_variables = array(
			'id'     => $id,
			'code'   => $code,
			'type'   => $type,
			'values' => $values,
		);
	}

	/**
	 * Handle POST and GET on export_import page.
	 */
	private function handle_export_import(){
		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			if( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'template_dictionary' ) ){
				wp_nonce_ays( 'template_dictionary' );
			}
			$action = $_POST['action'];
			if( $action === 'export' ){
				$export_type = $_POST['export_type'];
				$export_languages = $_POST['export_languages'];
				$result = $this->export_xml( $export_type, $export_languages );
				if( $result === -1 ){
					$this->add_notice( __( 'No records found, XML was not created.', 'template-dictionary' ), 'notice-info' );
				}
				else if( $result ){
					$this->add_notice(
						sprintf(
							'%1$s <a href="%2$s" target="_blank">%3$s</a>',
							__( 'XML was created.', 'template-dictionary' ),
							$result,
							__( 'Download file', 'template-dictionary' )
						),
						'notice-success'
					);
				}
				else {
					$this->add_notice( __( 'XML was not created.', 'template-dictionary' ), 'notice-error' );
				}
			}
			else if( $action === 'import' ){
				$import_type = $_POST['import_type'];
				$import_file = isset( $_FILES['import_file'] ) ? $_FILES['import_file'] : null;
				if( $import_file && $import_file['size'] ){
					$result = $this->import_xml( $import_file, $import_type );
					if( $result === -1 ){
						$this->add_notice( __( 'The XML file could not be loaded.', 'template-dictionary' ), 'notice-error' );
					}
					else if( !$result ){
						$this->add_notice( __( 'Settings and values were not imported.', 'template-dictionary' ), 'notice-error' );
					}
					else {
						$this->add_notice( __( 'Settings and values were imported.', 'template-dictionary' ), 'notice-success' );
					}
				}
				else {
					$this->add_notice( __( 'Please attach an XML file.', 'template-dictionary' ), 'notice-error' );
				}
			}
		}
	}

	/**
	 * Dictionary list page.
	 */
	public function page_dictionary_list(){
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		require __DIR__ . '/list-table-values.php';
		$list_table = new TemplateDictionary_Admin_ListTableValues();

		include( 'views/page-dictionary-list.php' );
	}

	/**
	 * Settings list page.
	 */
	public function page_settings_list(){
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		require __DIR__ . '/list-table-settings.php';
		$list_table = new TemplateDictionary_Admin_ListTableSettings();

		include( 'views/page-settings-list.php' );
	}

	/**
	 * Page for adding and editing settings.
	 */
	public function page_add_edit_setting(){
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		extract( $this->page_variables );

		include( 'views/page-add-edit-setting.php' );
	}

	/**
	 * Page for editing values.
	 */
	public function page_edit_value(){
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		extract( $this->page_variables );

		include( 'views/page-edit-value.php' );
	}

	/**
	 * Page for import and export.
	 */
	public function page_export_import(){
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		include( 'views/page-export-import.php' );
	}

	/**
	 * Get the plugin's database table(s).
	 * @param string|null $key 
	 * @return string
	 */
	private function db_tables( $key = null ){
		$td = TmplDict();
		return $td->db_tables( $key );
	}

	/**
	 * Save setting.
	 * @param string $code Setting code.
	 * @param string|TemplateDictionary_Type $type Field type.
	 * @param string $options JSON object with field options.
	 * @return int ID of inserted setting or 0 if failed.
	 */
	private function save_setting( $code, $type, $options ){
		global $wpdb;

		$table_main = $this->db_tables( 'table_main' );

		if( ! is_string( $type ) ){
			$type = $type->type();
		}

		$result = $wpdb->insert(
			$table_main,
			array(
				'code' => $code,
				'type' => $type,
				'options' => $options,
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);

		if( false === $result ){
			return 0;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update setting.
	 * @param int $id Setting ID.
	 * @param string $code Setting code.
	 * @param TemplateDictionary_Type $new_type New type.
	 * @param TemplateDictionary_Type $old_type Old type.
	 * @param string $options JSON object with field options.
	 * @return boolean
	 */
	private function update_setting( $id, $code, $new_type, $old_type, $options ){
		global $wpdb;

		$table_main = $this->db_tables( 'table_main' );

		$delete_values = false;

		if( $new_type->type() !== $old_type->type() ){
			if( $old_type->is_changeable_to( $new_type ) ){
				if( $new_type->table_type() !== $old_type->table_type() ){
					$values = $this->get_values( $id, $old_type->table_type() );
					$delete_values = true;
					$this->update_values( $id, $values, $new_type->table_type() );
				}
			}
			else {
				$delete_values = true;
			}
		}

		if( $delete_values ){
			$table = $this->db_tables( 'table_' . $old_type->table_type() );
			$wpdb->delete(
				$table,
				array(
					'id' => $id,
				),
				array(
					'%d',
				)
			);

			$this->transient_langs_to_delete = $this->langs;
		}

		$result = $wpdb->update(
			$table_main,
			array(
				'code' => $code,
				'type' => $new_type->type(),
				'options' => $options,
			),
			array(
				'id' => $id,
			),
			array(
				'%s',
				'%s',
				'%s',
			),
			'%d'
		);

		if( false === $result ){
			return false;
		}

		return true;
	}

	/**
	 * Get setting from database.
	 * @param int $id Setting ID.
	 * @return array Database row.
	 */
	private function get_setting( $id ){
		global $wpdb;

		$table_main = $this->db_tables( 'table_main' );

		return $wpdb->get_row( $wpdb->prepare( "SELECT code, type, options FROM $table_main WHERE id = %d", $id ), ARRAY_A );
	}

	/**
	 * Get setting from database.
	 * @param string $code Setting code.
	 * @return array Database row.
	 */
	private function get_setting_by_code( $code ){
		global $wpdb;

		$table_main = $this->db_tables( 'table_main' );

		return $wpdb->get_row( $wpdb->prepare( "SELECT id, type, options FROM $table_main WHERE code = %s", $code ), ARRAY_A );
	}

	/**
	 * Is there another setting with same code in database?
	 * @param string $code 
	 * @param int $id 
	 * @return boolean
	 */
	private function is_conflict_setting_code( $code, $id = null ){
		global $wpdb;

		$table_main = $this->db_tables( 'table_main' );

		if( $id ){
			$sql = "SELECT COUNT(*) FROM $table_main WHERE code = %s AND id != %d";
			$result = $wpdb->get_var( $wpdb->prepare( $sql, $code, $id ) );
		}
		else {
			$sql = "SELECT COUNT(*) FROM $table_main WHERE code = %s";
			$result = $wpdb->get_var( $wpdb->prepare( $sql, $code ) );
		}


		return (int)$result;
	}

	/**
	 * Add a notice.
	 * @param string $notice Notice text.
	 * @param string $class CSS class.
	 */
	private function add_notice( $notice, $class ){
		$this->notices[] = array(
			'notice' => $notice,
			'class'  => $class,
		);
	}

	/**
	 * Print the notice.
	 * @param string $notice Notice text.
	 * @param string $class CSS class.
	 */
	private function print_notice( $notice, $class ){
		echo '<div class="notice ' . $class . '"><p><strong>' . $notice . '</strong></p></div>';
	}

	/**
	 * Print all notices.
	 */
	private function print_all_notices(){
		foreach ( $this->notices as $item ) {
			$this->print_notice( $item['notice'], $item['class'] );
		}
	}

	/**
	 * Validate the code string.
	 * @param string $code 
	 * @return boolean
	 */
	private function validate_code( $code ){
		return preg_match( '/^[a-z_][a-zA-Z0-9_]*$/', $code );
	}

	/**
	 * Get values with given ID from database.
	 * @param int $id Setting ID.
	 * @param string $type Field type.
	 * @return array
	 */
	private function get_values( $id, $type ){
		global $wpdb;

		$table = $this->db_tables( 'table_' . $type );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT lang, value FROM $table WHERE id = %d", $id ) );
		
		$return = array();
		foreach ( $results as $item ) {
			$return[ $item->lang ] = $item->value;
		}

		return $return;
	}

	/**
	 * Update values.
	 * @param int $id Setting ID.
	 * @param array $values
	 * @param string $type 
	 */
	private function update_values( $id, $values, $type ){
		global $wpdb;

		$table = $this->db_tables( 'table_' . $type );

		$value_format = ( $type == 'int' ) ? '%d' : '%s';

		$result = true;

		foreach ( $values as $lang => $value ) {
			$wpdb->delete(
				$table,
				array(
					'id' => $id,
					'lang' => $lang,
				),
				array(
					'%d',
					'%s',
				)
			);

			if( $value !== '' && $value !== null ){
				$r = $wpdb->insert(
					$table,
					array(
						'id' => $id,
						'lang' => $lang,
						'value' => $value,
					),
					array(
						'%d',
						'%s',
						$value_format,
					)
				);
				if( ! $r ){
					$result = false;
				}
			}
		}

		$this->transient_langs_to_delete = $this->langs;

		return $result;
	}

	/**
	 * Delete value with given ID and language.
	 * @param int $id 
	 * @param string $lang 
	 * @return int|boolean Result.
	 */
	private function delete_value( $id, $lang ){
		global $wpdb;

		$setting = $this->get_setting( $id );
		if( ! $setting ){
			return false;
		}
		$type = $setting['type'];
		$type = $this->get_type( $type );
		$type = $type->table_type();

		$table = $this->db_tables( 'table_' . $type );

		$result = $wpdb->delete(
			$table,
			array(
				'id' => $id,
				'lang' => $lang,
			),
			array(
				'%d',
				'%s',
			)
		);

		if( ! in_array( $lang, $this->transient_langs_to_delete ) ){
			$this->transient_langs_to_delete[] = $lang;
		}

		return $result;
	}

	/**
	 * Delete setting (and values) of given ID.
	 * @param int $id 
	 * @return int|boolean Result.
	 */
	private function delete_setting( $id ){
		global $wpdb;

		$setting = $this->get_setting( $id );
		if( ! $setting ){
			return false;
		}
		$type = $setting['type'];
		$type = $this->get_type( $type );
		$type = $type->table_type();

		$table = $this->db_tables( 'table_'.$type );
		$table_main = $this->db_tables( 'table_main' );

		$wpdb->delete(
			$table,
			array(
				'id' => $id,
			),
			array(
				'%d',
			)
		);

		$this->transient_langs_to_delete = $this->langs;

		return $wpdb->delete(
			$table_main,
			array(
				'id' => $id,
			),
			array(
				'%d',
			)
		);
	}

	/**
	 * Export XML file with settings or settings with values.
	 * @param string $export_type Contains 'sv' for settings with value, 's' for settings only.
	 * @return int|string Result or filename.
	 */
	private function export_xml( $export_type, $langs = null ){
		global $wpdb;

		extract( $this->db_tables() );

		$langs_slug = '';

		if( $export_type === 'sv' ){
			$langs = array_map( 'esc_sql', $langs );
			$langs_sql = "'" . implode( "','" , $langs ) . "'";
			$langs_slug = '_' . implode( '-', $langs );

			$sql = "SELECT code, type, options, lang, value
				FROM $table_main t
				LEFT JOIN (
					SELECT id, value, lang FROM $table_int
					UNION ALL
					SELECT id, value, lang FROM $table_varchar
					UNION ALL
					SELECT id, value, lang FROM $table_text
					) ts
					ON t.id = ts.id
				WHERE lang in ($langs_sql) OR lang IS NULL
				ORDER BY code ASC";

			$results = $wpdb->get_results( $sql, ARRAY_A );

			if( ! $results ){
				return -1;
			}

			$xml = new SimpleXMLElement('<template_dictionary />');

			$setting_code = '';
			$setting_el = null;

			foreach ( $results as $item ) {
				if( $item['code'] !== $setting_code ){
					$setting_code = $item['code'];
					$setting_el = $xml->addChild('setting');
					$setting_el->addChild( 'code', $setting_code );
					$setting_el->addChild( 'type', $item['type'] );
					$setting_el->addChild( 'options', $item['options'] );
				}
				$v = $item['value'];
				if( $v != null ){
					$value = $setting_el->addChild('value');
					$value->addChild( 'lang', $item['lang'] );
					$value->addChild( 'value', $v );
				}
			}

		}
		else if ( $export_type === 's' ){
			$sql = "SELECT code, type, options FROM $table_main";

			$results = $wpdb->get_results( $sql, ARRAY_A );

			if( ! $results ){
				return -1;
			}

			$xml = new SimpleXMLElement('<template_dictionary />');

			foreach ( $results as $item ) {
				$setting_el = $xml->addChild('setting');
				$setting_el->addChild( 'code', $item['code'] );
				$setting_el->addChild( 'type', $item['type'] );
				$setting_el->addChild( 'options', $item['options'] );
			}
		}
		else {
			return false;
		}

		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML( $xml->asXML() );

		$uploaddir = wp_upload_dir();
		$filename = 'td_'  . date( 'Y-m-d-H-s' ) . '_' . $export_type . $langs_slug . '.xml';

		$result = $dom->save( $uploaddir['basedir'] . '/template-dictionary/' . $filename );
		if( ! $result ){
			return false;
		}
		else {
			return $uploaddir['baseurl'] . '/template-dictionary/' . $filename;
		}
	}

	/**
	 * Import XML with settings and values.
	 * @param file $import_file 
	 * @param string $import_type 
	 * @return int Result.
	 */
	private function import_xml( $import_file, $import_type ){
		$xml = simplexml_load_file( $import_file['tmp_name'] );

		if( ! $xml ){
			return -1;
		}

		if( $import_type === 'delete' ){
			$this->delete_all();

			foreach ( $xml->setting as $setting ) {
				$code = $setting->code->__toString();
				$type = $setting->type->__toString();
				$options = isset( $setting->options ) ? $setting->options->__toString() : null;

				if( ! $this->type_exists( $type ) ){
					continue;
				}

				$id = $this->save_setting( $code, $type, $options );

				if( isset( $setting->value ) ){
					$type = $this->get_type( $type );
					$type = $type->table_type();
					$values = array();

					foreach ( $setting->value as $value ) {
						$values[ $value->lang->__toString() ] = $value->value->__toString();
					}

					$this->update_values( $id, $values, $type );
				}
			}
		}
		else if( $import_type === 'update' ){
			foreach ( $xml->setting as $setting ) {
				$code = $setting->code->__toString();
				$type = $setting->type->__toString();
				$options = isset( $setting->options ) ? $setting->options->__toString() : null;

				if( ! $this->type_exists( $type ) ){
					continue;
				}

				$old_setting = $this->get_setting_by_code( $code );

				if( $old_setting ){
					$id = $old_setting['id'];
					$old_type = $old_setting['type'];

					$type = $this->get_type( $type );
					$old_type = $this->get_type( $old_type );

					$this->update_setting( $id, $code, $type, $old_type, $options );
				}
				else {
					$id = $this->save_setting( $code, $type, $options );
					$type = $this->get_type( $type );
				}

				if( isset( $setting->value ) ){
					$type = $type->table_type();
					$values = array();

					foreach ( $setting->value as $value ) {
						$values[ $value->lang->__toString() ] = $value->value->__toString();
					}

					$this->update_values( $id, $values, $type );
				}
			}
		}
		else if( $import_type === 'import_new' ){
			foreach ( $xml->setting as $setting ) {
				$code = $setting->code->__toString();
				$type = $setting->type->__toString();
				$options = isset( $setting->options ) ? $setting->options->__toString() : null;

				if( ! $this->type_exists( $type ) ){
					continue;
				}

				if( isset( $setting->value ) ){
					$old_setting = $this->get_setting_by_code( $code );
					if( $old_setting ){
						if( $type !== $old_setting['type'] ){
							continue;
						}
						if( $options != $old_setting['options'] ){
							continue;
						}
						$id = $old_setting['id'];
					}
					else {
						$id = $this->save_setting( $code, $type, $options );
					}

					$type = $this->get_type( $type );
					$type = $type->table_type();
					$values = array();

					foreach ( $setting->value as $value ) {
						$values[ $value->lang->__toString() ] = $value->value->__toString();
					}

					$this->update_values( $id, $values, $type );
				}
			}
		}
		else {
			return false;
		}

		$this->transient_langs_to_delete = $this->langs;

		return true;
	}

	/**
	 * Delete all settings and values.
	 */
	private function delete_all(){
		global $wpdb;

		$tables = $this->db_tables();

		foreach ( $tables as $table ) {
			$wpdb->query( "TRUNCATE TABLE $table" );
		}

		$this->transient_langs_to_delete = $this->langs;
	}

}

?>
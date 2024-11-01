<?php
/**
 * Plugin Name: Template Dictionary
 * Description: A plugin for developers which provides template variables dictionary editable in backend.
 * Version:     1.6.1
 * Author:      Radovan Kneblík
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: template-dictionary
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main TemplateDictionary class.
 */
final class TemplateDictionary {

	/**
	 * The plugin version.
	 * @var string
	 */
	private $version = '1.6.1';

	/**
	 * The instance of TemplateDictionary class.
	 * @var TemplateDictionary
	 */
	private static $instance = null;

	/**
	 * The template dictionary.
	 * @var array of strings and ints
	 */
	private $dictionary = array();

	/**
	 * The current language slug.
	 * @var string
	 */
	private $lang;

	/**
	 * The plugin paths.
	 * @var array of strings
	 */
	private $paths;

	/**
	 * The plugin's database tables.
	 * @var array of strings
	 */
	private $db_tables;

	/**
	 * Returns the TemplateDictionary instance.
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
	 * TemplateDictionary constructor.
	 */
	private function __construct(){
		$this->defines();
		$this->hooks();
		$this->includes();
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
	 * Define plugin contants and variables.
	 */
	private function defines(){
		global $wpdb;
		$this->db_tables = array(
			'table_main'    => $wpdb->prefix . 'tdict',
			'table_int'     => $wpdb->prefix . 'tdict_int',
			'table_varchar' => $wpdb->prefix . 'tdict_varchar',
			'table_text'    => $wpdb->prefix . 'tdict_text',
		);

		$plugindir = __DIR__ . '/';
		$this->paths = array(
			'plugin'   => $plugindir,
			'admin'    => $plugindir . 'admin/',
			'includes' => $plugindir . 'includes/',
		);
	}

	/**
	 * Hook actions and filters.
	 */
	private function hooks(){
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'init', array( $this, 'check_version' ) );

		add_action( 'plugins_loaded', array( $this, 'load_language' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load' ), 9 );
		if( defined( 'TMPL_DICT_JS_VAR_NAME' ) ){
			add_action( 'wp_head', array( $this, 'js_dictionary' ) );
		}

		add_shortcode( 'tmpl_dict', array( $this, 'shortcode' ) );
	}

	/**
	 * Include parts.
	 */
	private function includes(){
		include_once $this->get_path('includes') . 'polylang.php';

		if( is_admin() ){
			include_once $this->get_path('admin') . 'admin.php';
			TemplateDictionary_Admin::get_instance();
		}
	}

	/**
	 * Function to load current language slug.
	 */
	public function load_language(){
		if( isset( $this->lang ) ){
			return;
		}

		$lang = defined( 'TMPL_DICT_DEFAULT_LANG' ) ? TMPL_DICT_DEFAULT_LANG : substr( get_locale() , 0, 2 );

		$lang = apply_filters( 'template_dictionary_language', $lang );

		$this->lang = esc_sql( $lang );
	}

	/**
	 * Function to load the dictionary.
	 */
	public function load(){
		if( ! empty( $this->dictionary ) ){
			return;
		}

		$transient_name = $this->transient_name();

		$dictionary = get_transient( $transient_name );

		if( false === $dictionary ){
			global $wpdb;

			extract( $this->db_tables() );

			$sql = "SELECT id, code, value FROM $table_main
					NATURAL JOIN (
						SELECT id, value, lang FROM $table_int
						UNION ALL
						SELECT id, value, lang FROM $table_varchar
						UNION ALL
						SELECT id, value, lang FROM $table_text
						) t
					WHERE lang = '$this->lang'";

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $item ) {
				$this->set( $item->code, $item->value );
			}

			set_transient( $transient_name, $this->dictionary );
		}
		else {
			$this->dictionary = $dictionary;
		}
	}

	/**
	 * Get the plugin's database table(s).
	 * @param string|null $key 
	 * @return string|array
	 */
	public function db_tables( $key = null ){
		if( ! $key ){
			return $this->db_tables;
		}
		else {
			return $this->db_tables[ $key ];
		}
	}

	/**
	 * Get the plugin's defined path(s).
	 * @param string|null $key 
	 * @return string
	 */
	public function get_path( $key = null ){
		if( ! $key ){
			$key = 'plugin';
		}
		return $this->paths[ $key ];
	}

	/**
	 * Get current language slug.
	 * @return string
	 */
	public function get_lang(){
		return $this->lang;
	}

	/**
	 * Get transient name for a language.
	 * @param string|null $lang
	 * @return string
	 */
	public function transient_name( $lang = null ){
		if( ! $lang ){
			$lang = $this->get_lang();
		}
		return 'template_dictionary_' . $lang;
	}

	/**
	 * Check the plugin's version stored in db.
	 */
	public function check_version(){
		if( get_option( 'template-dictionary-version' ) !== $this->version ){
			delete_option( 'template-dictionary-version' );
			add_option( 'template-dictionary-version', $this->version );
		}
	}

	/**
	 * Install plugin.
	 */
	public function install(){
		global $wpdb;

		extract( $this->db_tables() );
		
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_main (
			id smallint NOT NULL AUTO_INCREMENT,
			type varchar(16) NOT NULL,
			code varchar(32) NOT NULL,
			options text,
			PRIMARY KEY (id)
		) $charset_collate;
		CREATE TABLE $table_int (
			id smallint NOT NULL,
			lang varchar(6) NOT NULL,
			value int,
			PRIMARY KEY (id, lang)
		) $charset_collate;
		CREATE TABLE $table_varchar (
			id smallint NOT NULL,
			lang varchar(6) NOT NULL,
			value varchar(255),
			PRIMARY KEY (id, lang)
		) $charset_collate;
		CREATE TABLE $table_text (
			id smallint NOT NULL,
			lang varchar(6) NOT NULL,
			value text,
			PRIMARY KEY (id, lang)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['basedir'];
		$upload_dir = $upload_dir . '/template-dictionary';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0700 );
		}

		add_option( 'template-dictionary-version', $this->version );
	}

	/**
	 * Get the dictionary value for given code.
	 * @param string $code
	 * @param string|int|null $default
	 * @return string|int
	 */
	public function get( $code, $default = null ){
		return isset( $this->dictionary[ $code ] ) ? $this->dictionary[ $code ] : $default;
	}

	/**
	 * Print the dictionary value for given code.
	 * @param string $code
	 * @param string|int|null $default
	 * @return string|int
	 */
	public function eget( $code, $default = null ){
		echo $this->get( $code, $default );
	}

	/**
	 * Get the dictionary value for given code as property.
	 * @param string $code
	 * @return string|int
	 */
	public function __get( $code ){
		return $this->get( $code );
	}

	/**
	 * Set the dictionary value for given code.
	 * @param string $code 
	 * @param string|int $value 
	 */
	private function set( $code, $value ){
		$this->dictionary[ $code ] = $value;
	}

	/**
	 * Get the whole dictionary array.
	 * @return array
	 */
	public function get_dictionary(){
		return $this->dictionary;
	}

	/**
	 * The shortcode function.
	 */
	public function shortcode( $atts ){
		$atts = shortcode_atts( array(
			'code' => '',
			'default' => null,
			'do_shortcode' => 'no',
		), $atts );

		$code = $atts['code'];
		$default = $atts['default'];
		$do_shortcode = ( $atts['do_shortcode'] === 'yes' ) ? true : false;

		if( ! $code ){
			return '';
		}

		$value = $this->get( $code, $default );
		if( ! $value ){
			return '';
		}

		if( $do_shortcode ){
			return do_shortcode( $value );
		}
		else {
			return $value;
		}
	}

	/**
	 * Create JS object.
	 */
	public function js_dictionary(){ ?>
<script type="text/javascript">
/* <![CDATA[ */
var <?php echo TMPL_DICT_JS_VAR_NAME; ?> = <?php echo json_encode( $this->get_dictionary() ); ?>;
/* ]]> */
</script><?php
	}

}

/**
 * Get the instance of TemplateDictionary.
 * @return type
 */
function TmplDict(){
	return TemplateDictionary::get_instance();
}

TmplDict();

?>
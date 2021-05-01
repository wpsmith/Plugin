<?php

/**
 * WPS Base Plugin Class
 *
 * @package    WPS\WP\Plugin
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\WP\Plugin;

use WPS\Core\Singleton;
use WPS\WP\Templates;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\PluginBase' ) ) {
	/**
	 * Extend Plugin Class
	 *
	 * Assists in properly extending an existing plugin.
	 *
	 * @package WPS\WP\Plugin
	 */
	abstract class PluginBase extends Singleton {

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access protected
		 * @var    string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected string $version = '0.0.0';

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access protected
		 * @var    string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected string $plugin_name = 'wps';

		/**
		 * Template Loaders.
		 *
		 * @access protected
		 * @var    Templates\TemplateLoader[]
		 */
		protected array $loaders;

		/**
		 * Plugin directory.
		 *
		 * @access protected
		 * @var    string
		 */
		protected string $plugin_directory;

		/**
		 * __FILE__ of the root plugin.
		 *
		 * @access protected
		 * @var string
		 */
		protected string $file = __FILE__;

		/**
		 * Plugin constructor.
		 *
		 * @access protected
		 * @formatter:off
		 * @param  array $args {
		 *      Optional args.
		 *
		 *      @type string $name Plugin slug.
		 *      @type string $version Plugin semantic version.
		 *      @type string $file Plugin absolute file path.
		 *      @type string $directory Plugin directory. Defaults to `dirname( $file )`.
		 * }
		 * @formatter:on
		 */
		protected function __construct( $args = array() ) {
			$this->plugin_directory = dirname( $this->file );

			// Do i18n.
			$this->add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			if ( method_exists( $this, 'plugins_loaded' ) ) {
				$this->add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			}

			// Parse the args.
			$args = wp_parse_args( $args, array(
				'name'      => $this->plugin_name,
				'version'   => $this->version,
				'file'      => $this->file,
				'directory' => $this->plugin_directory,
			) );

			// Set parameters.
			$this->version          = $args['version'];
			$this->plugin_name      = $args['name'];
			$this->plugin_directory = '' !== $args['directory'] ? $args['directory'] : ( '' !== $args['file'] ? dirname( $args['file'] ) : dirname( __FILE__ ) );

			// Construct the parent.
			parent::__construct( $args );
		}

		/**
		 * Helper to determine whether AJAX is running.
		 *
		 * @return bool
		 */
		public static function doing_ajax(): bool {
			return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		}

		/**
		 * Load plugin text domain.
		 */
		public function load_plugin_textdomain() {
			// No need to load text domain if doing AJAX.
			if ( self::doing_ajax() && isset( $_GET['lang'] ) ) {
				$locale = $_GET['lang'];
				\add_filter( 'pre_determine_locale', function () use ( $locale ) {
					return $locale;
				} );
			}

			\load_plugin_textdomain( self::get_plugin_name(), false, $this->plugin_directory . '/languages/' );
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @return string The name of the plugin.
		 */
		public static function get_plugin_name(): string {
			return self::get_instance()->plugin_name;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @return string The version number of the plugin.
		 */
		public static function get_version(): string {
			return self::get_instance()->version;
		}

		/**
		 * Gets the config loader.
		 *
		 * @return Templates\ConfigLoader
		 */
		public function get_config_loader(): Templates\ConfigLoader {
			return $this->get_loader( 'config' );
		}

		/**
		 * Gets the block loader.
		 *
		 * @return Templates\TemplateLoader
		 */
		public function get_block_loader(): Templates\TemplateLoader {
			return $this->get_loader( 'blocks' );
		}

		/**
		 * Gets the template loader.
		 *
		 * @return Templates\TemplateLoader
		 */
		public function get_template_loader(): Templates\TemplateLoader {
			return $this->get_loader();
		}

		/**
		 * Gets a specific named loader.
		 *
		 * @param string $loader Name of loader.
		 *
		 * @return Templates\FileLoader|Templates\TemplateLoader|Templates\ConfigLoader
		 */
		protected function get_loader( string $loader = 'templates' ): Templates\TemplateLoader|Templates\FileLoader|Templates\ConfigLoader {
			if ( isset( $this->loaders[ $loader ] ) ) {
				return $this->loaders[ $loader ];
			}

			return $this->get_new_loader( $loader );
		}

		/**
		 * Gets a new named loader.
		 *
		 * @param string $name Loader name.
		 *
		 * @return Templates\ConfigLoader|Templates\TemplateLoader
		 */
		protected function get_new_loader( string $name = 'templates' ) {
			switch ( $name ) {
				case 'config':
					$this->loaders[ $name ] = new Templates\ConfigLoader( $this->get_loader_args( $name ) );
					break;
				default:
					$this->loaders[ $name ] = new Templates\TemplateLoader( $this->get_loader_args( $name ) );
					break;

			}

			return $this->loaders[ $name ];
		}

		/**
		 * Gets the default loader args.
		 *
		 * @param string $templates_directory Templates directory.
		 *
		 * @return array
		 */
		protected function get_loader_args( string $templates_directory = 'templates' ) {
			return [
				'filter_prefix'       => $this->plugin_name,
				'plugin_directory'    => $this->plugin_directory,
				'templates_directory' => $templates_directory,
			];
		}

		/**
		 * Hooks a function on to a specific action.
		 *
		 * Actions are the hooks that the WordPress core launches at specific points
		 * during execution, or when specific events occur. Plugins can specify that
		 * one or more of its PHP functions are executed at these points, using the
		 * Action API.
		 *
		 * @param string $tag The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int $priority Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
		 * @param array $args Args to pass to the function.
		 */
		public function add_action( string $tag, callable $function_to_add, $priority = 10, $accepted_args = 1, $args = array() ) {
			if ( \did_action( $tag ) || \doing_action( $tag ) ) {
				call_user_func_array( $function_to_add, (array) $args );
			} else {
				\add_action( $tag, $function_to_add, $priority, $accepted_args );
			}
		}
	}
}

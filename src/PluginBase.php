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
use WPS\WP\Templates\TemplateLoader;

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
	 * @author Travis Smith <t@wpsmith.net>
	 */
	abstract class PluginBase extends Singleton {

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access protected
		 * @var string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected static $version;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected static $plugin_name = 'wps';

		/**
		 * Templates.
		 *
		 * @var TemplateLoader
		 */
		protected $template_loader;

		/**
		 * Plugin directory.
		 *
		 * @var string
		 */
		protected $plugin_directory;

		/**
		 * Plugin constructor.
		 *
		 * @param array $args {
		 *      Optional args.
		 *
		 *      @type string $name Plugin slug.
		 *      @type string $version Plugin semantic version.
		 *      @type string $file Plugin absolute file path.
		 *      @type string $directory Plugin directory. Defaults to `dirname( $file )`.
		 * }
		 *
		 * @since 0.0.1
		 */
		protected function __construct( $args = array() ) {
			// Do i18n.
			$this->add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Parse the args.
			$args = wp_parse_args( $args, array(
				'name'      => 'wps',
				'version'   => '0.0.0',
				'file'      => __FILE__,
				'directory' => dirname( __FILE__ ),
			) );

			// Set parameters.
			self::$version          = $args['version'];
			self::$plugin_name      = $args['name'];
			$this->plugin_directory = '' !== $args['directory'] ? $args['directory'] : ( '' !== $args['file'] ? dirname( $args['file'] ) : dirname( __FILE__ ) );

			// Construct the parent.
			parent::__construct( $args );
		}

		/**
		 * Helper to determine whether AJAX is running.
		 *
		 * @return bool
		 * @since 0.0.1
		 */
		public static function doing_ajax() {
			return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		}

		/**
		 * Load plugin text domain.
		 */
		public function load_plugin_textdomain() {
			// No need to load text domain if doing AJAX.
			if ( self::doing_ajax() && isset( $_GET['lang'] ) ) {
				$locale = $_GET['lang'];
				add_filter(
					'pre_determine_locale',
					function () use ( $locale ) {
						return $locale;
					}
				);
			}

			load_plugin_textdomain( self::get_plugin_name(), false, $this->plugin_directory . '/languages/' );
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @return string The name of the plugin.
		 * @since 0.0.1
		 */
		public static function get_plugin_name() {
			return self::$plugin_name;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @return string The version number of the plugin.
		 * @since 0.0.1
		 */
		public static function get_version() {
			return self::$version;
		}

		/**
		 * Gets the template loader.
		 *
		 * @return TemplateLoader
		 * @since 0.0.1
		 */
		public function get_template_loader() {
			if ( $this->template_loader ) {
				return $this->template_loader;
			}

			// Create template loader.
			$this->template_loader = new TemplateLoader( [
				'filter_prefix'    => 'wps',
				'plugin_directory' => $this->plugin_directory,
			] );

			return $this->template_loader;
		}

		/**
		 * Hooks a function on to a specific action.
		 *
		 * Actions are the hooks that the WordPress core launches at specific points
		 * during execution, or when specific events occur. Plugins can specify that
		 * one or more of its PHP functions are executed at these points, using the
		 * Action API.
		 *
		 * @param string   $tag The name of the action to which the $function_to_add is hooked.
		 * @param callable $function_to_add The name of the function you wish to be called.
		 * @param int      $priority Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
		 * @param array    $args Args to pass to the function.
		 */
		public function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1, $args = array() ) {
			if ( did_action( $tag ) || doing_action( $tag ) ) {
				call_user_func_array( $function_to_add, (array) $args );
			} else {
				add_action( $tag, $function_to_add, $priority, $accepted_args );
			}
		}
	}
}

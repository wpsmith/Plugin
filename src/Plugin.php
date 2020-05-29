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

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	/**
	 * Extend Plugin Class
	 *
	 * Assists in properly extending an existing plugin.
	 *
	 * @package WPS\WP\Plugin
	 * @author Travis Smith <t@wpsmith.net>
	 */
	abstract class Plugin extends Singleton {

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
		 * @param array $args Optional args.
		 *
		 * @since 0.0.1
		 */
		protected function __construct( $args = array() ) {
			// Do i18n.
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Parse the args.
			$args = wp_parse_args( $args, array(
				'name'      => 'wps',
				'version'   => '',
				'file'      => '',
				'directory' => '',
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

	}
}
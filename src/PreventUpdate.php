<?php

/**
 * WPS Core Hide Plugin Class
 *
 * @package    WPS\Plugins
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Plugin;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Plugin\PreventUpdate' ) ) {
	/**
	 * Prevent Update Plugin Class
	 *
	 * Assists in hiding various plugins from the plugins admin page.
	 *
	 * @package WPS\Plugin
	 * @author  Travis Smith <t@wpsmith.net>
	 */
	class PreventUpdate {

		/**
		 * Plugin basename.
		 *
		 * The output of `plugin_basename( __FILE__ )` at the root of the plugin.
		 *
		 * @private
		 * @var string
		 */
		protected $plugin;

		/**
		 * PreventUpdate constructor.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			$this->plugin = $plugin_basename;
			add_action( 'http_request_args', array( $this, 'http_request_args' ), 5, 2 );
			add_filter( 'site_transient_update_plugins', array( $this, 'disable_plugin_updates' ) );

		}

		/**
		 * Block our plugin from being updated.
		 *
		 * Filters the arguments used in an HTTP request.
		 *
		 * @param array  $r   An array of HTTP request arguments.
		 * @param string $url The request URL.
		 *
		 * @return mixed
		 */
		public function http_request_args( $r, $url ) {
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
				return $r; // Not a plugin update request. Bail immediately.
			}

			$plugins = unserialize( $r['body']['plugins'] );
			unset( $plugins->plugins[ $this->plugin ] );
			unset( $plugins->active[ array_search( $this->plugin, $plugins->active ) ] );
			$r['body']['plugins'] = serialize( $plugins );

			return $r;
		}

		/**
		 * Disables plugin notifications.
		 *
		 * @param \stdClass $value Plugin basename.
		 *
		 * @return mixed
		 */
		public function disable_plugin_updates( $value ) {
			if ( isset( $value ) && is_object( $value ) ) {
				if ( isset( $value->response[ $this->plugin ] ) ) {
					unset( $value->response[ $this->plugin ] );
				}
			}

			return $value;
		}
	}
}
<?php

/**
 * WPS Core Hide Plugin Class
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HidePlugin' ) ) {
	/**
	 * Hide Plugin Class
	 *
	 * Assists in hiding various plugins from the plugins admin page.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	class HidePlugin {

		protected $plugin;

		public function __construct( $plugin ) {
			$this->plugin = $plugin;
			add_action( 'pre_current_active_plugins', array( $this, 'hide_plugin' ) );
		}

		public function hide_plugin() {
			global $wp_list_table;
			$hidearr   = array( $this->plugin );
			$myplugins = $wp_list_table->items;
			foreach ( $myplugins as $key => $val ) {
				if ( in_array( $key, $hidearr ) ) {
					unset( $wp_list_table->items[ $key ] );
				}
			}
		}
	}
}
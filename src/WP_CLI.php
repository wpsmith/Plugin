<?php

namespace WPS\WP\Plugin;

// Make sure we are using WP_CLI.
if ( ! defined( 'WP_CLI' ) || ( defined( 'WP_CLI' ) && ! WP_CLI ) ) {
	return;
}


if ( ! class_exists( __NAMESPACE__ . '\WP_CLI' ) ) {
	class WP_CLI {

		/**
		 * Plugin basename.
		 *
		 * @var string
		 */
		protected $plugin_basename;

		/**
		 * Plugin file path.
		 *
		 * @var string
		 */
		protected $plugin_file;

		/**
		 * Constant or callback to check to see whether a dependency exists.
		 *
		 * @var string|callable
		 */
		protected $const_or_callback;

		/**
		 * Whether to `reactivate` dependency or `deactivate` this plugin.
		 *
		 * @var string
		 */
		protected $reactivate_or_deactivate;

		/**
		 * WP_CLI constructor.
		 *
		 * @param string $plugin_basename Plugin basename.
		 * @param string $plugin_file Plugin file path.
		 * @param string $const_or_callback Constant or callback to check to see if dependency is active.
		 * @param string $reactivate_or_deactivate Whether to `reactivate` dependency or `deactivate` this plugin.
		 */
		public function __construct( $plugin_basename, $plugin_file, $const_or_callback = '', $reactivate_or_deactivate = 'reactivate' ) {

			$this->plugin_basename          = $plugin_basename;
			$this->plugin_file              = $plugin_file;
			$this->const_or_callback        = $plugin_file;
			$this->reactivate_or_deactivate = $reactivate_or_deactivate;

			$this->hook();
		}

		/**
		 * Hooks into WP.
		 */
		private function hook() {
			// Filter get_option( 'active_sitewide_plugins' ).
			$this->add_filter( 'site_option_active_sitewide_plugins', array( $this, 'active_plugins' ) );

			// Filter get_option( 'active_plugins' ).
			$this->add_filter( 'option_active_plugins', array( $this, 'active_plugins' ) );

			// Filter update_option( 'active_sitewide_plugins' ).
			$this->add_filter( 'update_option_active_sitewide_plugins', array( $this, 'active_plugins' ) );

			// Filter update_option( 'active_plugins' ).
			$this->add_filter( 'update_option_active_plugins', array( $this, 'active_plugins' ) );

			// Filter update_option( 'recently_activated' ).
			$this->add_filter( 'update_option_recently_activated', array( $this, 'recently_activated' ) );
		}

		/**
		 * Hooks into both the hook and the pre_hook.
		 *
		 * @param string $hook The hook.
		 * @param callable $fn The callback.
		 */
		private function add_filter( $hook, $fn ) {
			add_filter( "pre_$hook", $fn, PHP_INT_MAX );
			add_filter( $hook, $fn, PHP_INT_MAX );
		}

		/**
		 * Removes both the hook and the pre_hook hook functions.
		 *
		 * @param string $hook The hook.
		 * @param callable $fn The callback.
		 */
		private function remove_filter( $hook, $fn ) {
			remove_filter( "pre_$hook", $fn, PHP_INT_MAX );
			remove_filter( $hook, $fn, PHP_INT_MAX );
		}

		/**
		 * Checks whether the dependency exists.
		 *
		 * @return bool
		 */
		private function dependency_check() {
			return (
				( is_string( $this->const_or_callback ) && defined( $this->const_or_callback ) && constant( $this->const_or_callback ) ) ||
				( is_callable( $this->const_or_callback ) && call_user_func( $this->const_or_callback, $this ) )
			);
		}

		public function deactivate() {
			$this->remove_filter( 'update_option_recently_activated', array( $this, 'recently_activated' ) );
			ExtendPlugin::deactivate_self( $this->plugin_file );
			$this->add_filter( 'update_option_recently_activated', array( $this, 'recently_activated' ) );
		}

		public function reactivate() {
			$this->remove_filter( 'update_option_recently_activated', array( $this, 'recently_activated' ) );
			$network = isset( \WP_CLI::get_runner()->config['network'] );
			activate_plugin( $this->plugin_file, false, $network, false );
			$this->add_filter( 'update_option_recently_activated', array( $this, 'recently_activated' ) );
		}

		/**
		 * Hook into recently active plugins option.
		 *
		 * @param array $plugins Array of recently active plugins.
		 */
		public function recently_activated( $plugins ) {
			if ( isset( $plugins[ $this->plugin_basename ] ) && $this->dependency_check() ) {
//			\WP_CLI::warning( sprintf( __( 'You are deactivating a required plugin for %1$s. Re-activating. Please deactivate the %1$s theme first.', 'wps' ), $dependency ), 'wps' );


			}
		}

		/**
		 * Hook into active plugins option.
		 *
		 * @param array $plugins Array of recently active plugins.
		 *
		 * @return array
		 */
		public function active_plugins( $plugins ) {

			if ( \WP_CLI\Utils\is_plugin_skipped( $this->plugin_basename ) ) {
				if ( ! isset( $plugins[ $this->plugin_basename ] ) ) {
					$plugins[ $this->plugin_basename ] = get_plugin_data( $this->plugin_basename, false, false );
				}
			}

			return $plugins;
		}


		/**
		 * Hook into active plugins option.
		 *
		 * @param array $plugins Array of recently active plugins.
		 *
		 * @return array
		 */
		public function all_plugins( $plugins ) {

			if ( isset( $plugins[ $this->plugin_basename ] ) ) {
				unset( $plugins[ $this->plugin_basename ] );
			}

			return $plugins;
		}
	}
}

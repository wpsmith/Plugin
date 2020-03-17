<?php

namespace WPS;

if ( ! function_exists( __NAMESPACE__ . '\is_plugin_active' ) ) {
	/**
	 * Check whether a plugin is active.
	 *
	 * Only plugins installed in the plugins/ folder can be active.
	 *
	 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
	 * return false for those plugins.
	 *
	 * @param string $plugin Path to the main plugin file from plugins directory.
	 *
	 * @return bool True, if in the active plugins list. False, not in the list.
	 */
	function is_plugin_active( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return \is_plugin_active( $plugin );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\extend_plugins' ) ) {
	/**
	 * Extends multiple plugins.
	 *
	 * <code>
	 * $array = array(
	 *   'plugin_path/plugin_root_file.php'   => '1.0.0',
	 *   'advanced-custom-fields-pro/acf.php' => '5.8.7',
	 * );
	 * </code>
	 *
	 * @param array[string]string $plugins Array of plugin basename => version.
	 * @param string $file
	 * @param string $text_domain
	 */
	function extend_plugins( $plugins, $file, $text_domain = 'wps' ) {
		foreach ( $plugins as $plugin => $version ) {
			new \WPS\WP\Plugin\ExtendPlugin( $plugin, $file, $version, $text_domain );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\hide_plugins' ) ) {
	/**
	 * Hides an array of plugins.
	 *
	 * @param array $plugins Array of plugin basenames.
	 */
	function hide_plugins( $plugins ) {
		foreach ( $plugins as $plugin ) {
			new \WPS\WP\Plugin\HidePlugin( $plugin );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\prevent_plugin_updates' ) ) {
	/**
	 * Prevents an array of plugins from updating.
	 *
	 * @param array $plugins Array of plugin basenames.
	 */
	function prevent_plugin_updates( $plugins ) {
		foreach ( $plugins as $plugin ) {
			new \WPS\WP\Plugin\PreventUpdate( $plugin );
		}
	}
}
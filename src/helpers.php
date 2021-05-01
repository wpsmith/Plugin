<?php

namespace WPS;

use \WPS\WP\Plugin;

if ( ! function_exists( __NAMESPACE__ . '\is_plugin_active' ) ) {
	/**
	 * Check whether a plugin is active.
	 *
	 * Only plugins installed in the plugins folder can be active.
	 *
	 * Plugins in the mu-plugins folder can't be "activated," so this function will
	 * return false for those plugins.
	 *
	 * @param string $plugin Path to the main plugin file from plugins directory.
	 *
	 * @return bool True, if in the active plugins list. False, not in the list.
	 */
	function is_plugin_active( string $plugin ): bool {
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
	 * @param array $plugins Array of plugin basename => version.
	 * @param string $file
	 * @param string $text_domain
	 */
	function extend_plugins( array $plugins, string $file, string $text_domain = 'wps' ) {
		foreach ( $plugins as $plugin => $version ) {
			extend_plugin( $plugin, $file, $version, $text_domain );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\extend_plugin' ) ) {
	/**
	 * Extend a plugin.
	 *
	 * @param string $plugin_basename Plugin basename (e.g., 'plugin_path/plugin_root_file.php')
	 * @param string $version Plugin version.
	 * @param string $file Plugin file.
	 * @param string $text_domain Text domain string.
	 */
	function extend_plugin( string $plugin_basename, string $version, string $file, string $text_domain = 'wps' ) {
		new Plugin\ExtendPlugin( $plugin_basename, $file, $version, $text_domain );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\hide_plugins' ) ) {
	/**
	 * Hides an array of plugins.
	 *
	 * <code>
	 * $array = array(
	 *   'plugin_path/plugin_root_file.php',
	 *   'advanced-custom-fields-pro/acf.php',
	 * );
	 * </code>
	 *
	 * @param string[] $plugins Array of plugin basenames.
	 */
	function hide_plugins( array $plugins ) {
		foreach ( $plugins as $plugin ) {
			hide_plugin( $plugin );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\hide_plugin' ) ) {
	/**
	 * Hides an array of plugins.
	 *
	 * @param string $plugin_basename Plugin basename (e.g., 'plugin_path/plugin_root_file.php').
	 */
	function hide_plugin( string $plugin_basename ) {
		new Plugin\HidePlugin( $plugin_basename );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\prevent_plugin_updates' ) ) {
	/**
	 * Prevents an array of plugins from updating.
	 *
	 * <code>
	 * $array = array(
	 *   'plugin_path/plugin_root_file.php',
	 * );
	 * </code>
	 *
	 * @param string[] $plugins Array of plugin basenames.
	 */
	function prevent_plugin_updates( array $plugins ) {
		foreach ( $plugins as $plugin ) {
			new Plugin\PreventUpdate( $plugin );
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\prevent_plugin_update' ) ) {
	/**
	 * Prevents an array of plugins from updating.
	 *
	 * @param string $plugin_basename Plugin basename (e.g., 'plugin_path/plugin_root_file.php').
	 */
	function prevent_plugin_updates( string $plugin_basename ) {
		new Plugin\PreventUpdate( $plugin_basename );
	}
}
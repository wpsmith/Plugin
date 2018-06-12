<?php

namespace WPS\Plugins;

/**
 * Check whether a plugin is active.
 *
 * Only plugins installed in the plugins/ folder can be active.
 *
 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
 * return false for those plugins.
 *
 * @param string $plugin Path to the main plugin file from plugins directory.
 * @return bool True, if in the active plugins list. False, not in the list.
 */
function is_active( $plugin ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return is_plugin_active( $plugin );
}
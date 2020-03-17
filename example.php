<?php

namespace WPS\WP\MyPlugin;

require_once 'src/HidePlugin.php';

// Hide this plugin from plugins list.
new \WPS\WP\Plugin\HidePlugin( plugin_basename( __FILE__ ) );

require_once 'src/PreventUpdate.php';

// Prevent plugin from being able to be updated.
new \WPS\WP\Plugin\PreventUpdate( plugin_basename( __FILE__ ) );

require_once 'src/ExtendPlugin.php';

// Extend ACF.
new \WPS\WP\Plugin\ExtendPlugin( 'advanced-custom-fields-pro/acf.php', __FILE__, '5.8.7', WPS_CORE_CLASSES_DOMAIN );

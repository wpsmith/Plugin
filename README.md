# Plugin

[![Code Climate](https://codeclimate.com/github/wpsmith/Plugin/badges/gpa.svg)](https://codeclimate.com/github/wpsmith/Plugin)

A class to use in your WordPress plugin to prevent updates to the plugin, ensure dependency plugin(s) is/are active, and to hide specific plugin(s).

## Description

These classes solve three (3) particular problems:
1. _Preventing plugins from updating_

    Plugins and themes are automatically checked by WordPress for updates in the WordPress repository. This poses a special problem for private plugins if the plugin slug happens to match. 

1. _Hiding Plugins_

    It is a best practice to move specific functionality to a core functionality plugin. One could use a mu-plugin but most use a standard plugin. However, the client could easily deactivate or delete the plugin. Therefore, hiding the plugin from the plugins page becomes very important.

1. _Extending Plugins_

    Many core functionality plugins extend/modify other plugins. If the dependency plugin that is being extended is deactivated, then the plugin makes the WordPress instance unstable and breaks the whole site.
    
1. _Uninstalling Plugins_

    It is a best practice to always uninstall your plugin data and files. However, an interface to determine what should be uninstalled needs to be built. This uses WP Pointers to create such an interface giving the user the choice to uninstall just the plugin files or everything.

1. _Building Plugins_

    When building a plugin, this library provides a base class to be extended as the new plugins core class.


## Installation

This isn't a WordPress plugin on its own, so the usual instructions don't apply. Instead you can install manually or using `composer`.

### Manually install class
Copy [`Plugin/src`](src) folder into your plugin for basic usage. Be sure to require the various files accordingly.

or:

### Install class via Composer
1. Tell Composer to install this class as a dependency: `composer require wpsmith/plugin`
2. Recommended: Install the Mozart package: `composer require coenjacobs/mozart --dev` and [configure it](https://github.com/coenjacobs/mozart#configuration).
3. The class is now renamed to use your own prefix to prevent collisions with other plugins bundling this class.

## Implementation & Usage

### Update Prevention

To prevent the current plugin from updating:
```php
new \WPS\WP\Plugin\PreventUpdate( plugin_basename( __FILE__ ) );
```

To prevent another plugin (e.g., ACF) from updating:
```php
new \WPS\WP\Plugin\PreventUpdate( 'advanced-custom-fields-pro/acf.php' );
```

### Hiding Plugin(s)

To hide the current plugin:
```php
new \WPS\WP\Plugin\HidePlugin( plugin_basename( __FILE__ ) );
```

To hide another plugin (e.g., ACF):
```php
new \WPS\WP\Plugin\HidePlugin( 'advanced-custom-fields-pro/acf.php' );
```

### Extending Plugin(s)

To extend ACF, you would do something like this:
```php
new \WPS\WP\Plugin\ExtendPlugin( 'advanced-custom-fields-pro/acf.php', __FILE__, '5.8.7', 'plugin-text-domain' );
```

### Uninstalling Plugin(s)

To use the `UninstallManager`, you would do something like this at the plugin's root:

#### Simple example
Within the plugin's main file:
```php
global $my_plugin_uninstaller;
$my_plugin_uninstaller = new \WPS\WP\Plugin\UninstallManager( __FILE__ );

// Register activation stuffs.
register_activation_hook( __FILE__, function() {
    global $my_plugin_uninstaller;
    UninstallManager::on_activation( $my_plugin_uninstaller );
} );

// Register deactivation stuffs.
register_deactivation_hook( __FILE__, function() {
    global $my_plugin_uninstaller;
    UninstallManager::on_deactivation( $my_plugin_uninstaller );
} );

// If not using an uninstall.php, you need to register uninstall stuffs.
register_uninstall_hook( __FILE__, function() {
    global $my_plugin_uninstaller;
    $wps_codeable_delete_action = $my_plugin_uninstaller->get_uninstall_action();
    
    // Bail if not deleting everything.
    if ( 'everything' !== $wps_codeable_delete_action ) {
    	return;
    }
    
    // Delete Options.
    $my_plugin_uninstaller->uninstall();
} );
```

#### A Better Example
Within the main plugin file:
```php
/**
 * Gets uninstall manager.
 *
 * This function can be placed anywhere. Alternatively, you can use a global variable to hold the uninstaller.
 * 
 * @param string $plugin_file Absolute path to plugin base file.
 *
 * @return \WPS\WP\Plugin\UninstallManager
 */
function get_uninstall_manager( $plugin_file ) {
    static $mgr;
    if ( null === $mgr ) {
        $mgr = new \WPS\WP\Plugin\UninstallManager( $plugin_file );
    }

    return $mgr;
}

// Setup Uninstall Manager at base of the plugin.
get_uninstall_manager( __FILE__ );

register_activation_hook( __FILE__, function() {
    UninstallManager::on_activation( get_uninstall_manager() );
} );
register_deactivation_hook( __FILE__, function() {
    UninstallManager::on_deactivation( get_uninstall_manager() );
} );
```

Then in `uninstall.php`:
```php
// Require the main plugin file.
require_once( 'plugin.php' );

// Get what we are supposed to do on deletion/uninstall.
$uninstall_manager = get_uninstall_manager();
$wps_codeable_delete_action = $uninstall_manager->get_uninstall_action();

// Bail if not deleting everything.
if ( 'everything' !== $wps_codeable_delete_action ) {
	return;
}

// Delete Options.
$uninstall_manager->uninstall();
```
### Building a Plugin

For all my plugins, I build a base class for the main plugin. Out of the box, `PluginBase` does three things:
1. Loads plugin's text domain accordingly.
1. Implements `Singleton` so there can only be one.
1. Provides the following methods:
  * `Plugin::doing_ajax()` or `PluginBase::doing_ajax()` - determines whether WP is processing an AJAX request.
  * `Plugin::get_version()` - gets the plugin's version. Defaults to `0.0.0`.
  * `Plugin::get_plugin_name()` - gets the plugin's name. Defaults to `wps`.~~~~
  * `$this->get_template_loader()` - gets the plugin's template loader. See [TemplateLoader](https://github.com/wpsmith/templates). 
  * `$this->add_action()` - Either adds the action to the correct hook or performs the action if the hook has already been done or is being done. 

Generally speaking, it goes something like this:
```php
namespace WPS\WP\Plugins\MyPlugin;

use WPS\WP\Plugin\PluginBase;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	/**
	 * Class Plugin
	 *
	 * @package \WPS\WP\Plugins\MyPlugin
	 */
	class Plugin extends PluginBase {
	    /**
         * Plugin constructor.
         *
         * @param array $args Optional args.
         *
         * @since 0.0.1
         */
        protected function __construct( $args = array() ) {
            // Construct the parent.
            parent::__construct( $args );
        }
    }
}

// Instantiate.
Plugin::get_instance( array(
    'name'    => 'my-plugin',
    'version' => '0.0.1',
    'file'    => __FILE__,
) );
```


## Change Log

See the [change log](CHANGELOG.md).

## License

[GPL 2.0 or later](LICENSE).

## Contributions

Contributions are welcome - fork, fix and send pull requests against the `master` branch please.

## Credits

Built by [Travis Smith](https://twitter.com/wp_smith)  
Copyright 2013-2020 [Travis Smith](https://wpsmith.net)
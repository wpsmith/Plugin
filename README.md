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
~~~php
new \WPS\WP\Plugin\PreventUpdate( plugin_basename( __FILE__ ) );
~~~

To prevent another plugin (e.g., ACF) from updating:
~~~php
new \WPS\WP\Plugin\PreventUpdate( 'advanced-custom-fields-pro/acf.php' );
~~~

### Hiding Plugin(s)

To hide the current plugin:
~~~php
new \WPS\WP\Plugin\HidePlugin( plugin_basename( __FILE__ ) );
~~~

To hide another plugin (e.g., ACF):
~~~php
new \WPS\WP\Plugin\HidePlugin( 'advanced-custom-fields-pro/acf.php' );
~~~

### Extending Plugin(s)

To extend ACF, you would do something like this:
~~~php
new \WPS\WP\Plugin\ExtendPlugin( 'advanced-custom-fields-pro/acf.php', __FILE__, '5.8.7', 'plugin-text-domain' );
~~~

## Change Log

See the [change log](CHANGELOG.md).

## License

[GPL 2.0 or later](LICENSE).

## Contributions

Contributions are welcome - fork, fix and send pull requests against the `master` branch please.

## Credits

Built by [Travis Smith](https://twitter.com/wp_smith)  
Copyright 2013-2020 [Travis Smith](https://wpsmith.net)
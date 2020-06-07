<?php

/**
 * WPS Plugin Data Manager Class
 *
 * @package    WPS\WP\Plugin
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    0.0.1
 * @since      0.0.1
 */

namespace WPS\WP\Plugins\Codeable;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\UninstallManager' ) ) {
	/**
	 * Plugin Uninstall Manager Class
	 *
	 * @package WPS\WP\Plugin
	 * @author Travis Smith <t@wpsmith.net>
	 */
	class UninstallManager {

		/**
		 * Plugin slug.
		 *
		 * @var string
		 */
		protected string $plugin_slug;

		/**
		 * Plugin basename.
		 *
		 * @var string
		 */
		protected string $plugin_file;

		/**
		 * Default delete action is to remove plugin only.
		 *
		 * @var string
		 */
		public static string $default_delete_action = 'plugin-only';

		/**
		 * DataManager constructor.
		 *
		 * @param string $plugin_filepath Absolute path to plugin base file.
		 */
		public function __construct( $plugin_filepath ) {

			$this->plugin_file = plugin_basename( $plugin_filepath );
			$plugin_data       = get_plugin_data( $plugin_filepath, false, false );
			$this->plugin_slug = sanitize_title_with_dashes( $plugin_data['Name'] );

			add_action( 'admin_footer', array( $this, 'delete_data_or_plugin' ) );

		}

		/**
		 * Default deletion actions.
		 *
		 * @return array {
		 *      Optional. An array of valid/supported delete actions. Default empty array.
		 *
		 * @type array  $id {
		 *          Optional. Valid deletion action.
		 * @type string $button Button class. Default ''. Accepts 'primary', 'secondary', or ''.
		 * @type string $text Text of the button.
		 *      }
		 * }
		 */
		protected static function get_default_delete_actions() {
			return array(
				'plugin-only' => self::create_delete_action( __( 'Delete Plugin Only', 'wps' ), 'primary' ),
				'everything'  => self::create_delete_action( __( 'Delete Everything', 'wps' ) ),
			);
		}

		/**
		 * Creates delete action.
		 *
		 * @param string $button_text Button text.
		 * @param string $button_class Button class.
		 *
		 * @return array $args {
		 * @type string  $button Button class. Default ''. Accepts 'primary', 'secondary', or ''.
		 * @type string  $name Text of the button.
		 * }
		 */
		protected static function create_delete_action( $button_text, $button_class = '' ) {
			$accepted_button_classes = array(
				'primary',
				'secondary',
			);

			if ( in_array( $button_class, $accepted_button_classes, true ) ) {
				return array(
					'button' => $button_class,
					'text'   => $button_text,
				);
			}

			return array(
				'button' => '',
				'text'   => $button_text,
			);
		}

		/**
		 * Gets the valid/supported deletion actions.
		 *
		 * @return string
		 */
		protected function get_delete_actions_content() {
			$default_action = $this->get_uninstall_action();

			$actions = array();
			$content = sprintf(
				'<h3>%s</h3><p>',
				__( 'On delete, delete the plugin or delete everything?', 'wps' )
			);
			foreach ( self::get_default_delete_actions() as $id => $delete_action ) {
				if ( '' !== $default_action ) {
					if ( $id === $default_action ) {
						$delete_action['button'] = 'primary';
					} else {
						$delete_action['button'] = '';
					}
				}
				$actions[] = sprintf(
					'<a id="%1$s" class="button%2$s" href="\' + $(e.currentTarget).attr(\'href\') + \'&delete_action=%1$s">%3$s</a>',
					$id,
					'' !== $delete_action['button'] ? ' button-' . $delete_action['button'] : '',
					$delete_action['text']
				);
			}
			$content .= join( '&nbsp;', $actions );
			$content .= '</p>';

			return $content;
		}

		/**
		 * Adds a pointer and event on plugins page.
		 */
		public function delete_data_or_plugin() {
			if ( ! self::is_plugins_page() ) {
				return;
			}

			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' ); // for user settings
			?>
            <script type="text/javascript">
                (function ($) {
                    var selector = 'tr[data-slug="<?php echo $this->plugin_slug; ?>"]';
                    $(selector + ' .delete a, ' + selector + ' .deactivate a').click(function (e) {
                        $(e.currentTarget).pointer({
                            content: '<?php echo $this->get_delete_actions_content(); ?>',
                            position: {
                                my: 'left top',
                                at: 'center bottom',
                                offset: '-1 0'
                            },
                            close: function () {
                                // Person just closed the pointer, prevent moving forward.
                                e.preventDefault();
                            }
                        }).pointer('open');
                        return false;
                    });
                })(jQuery);
            </script>
			<?php
		}

		/**
		 * Determines whether the current admin page is the plugins page.
		 *
		 * @return bool
		 */
		public static function is_plugins_page() {
			global $pagenow;
			$screen = get_current_screen();

			return is_admin() && ( 'plugins.php' === $pagenow || null !== $screen && 'plugins.php' === $screen->base );
		}

		/**
		 * Deactivation function.
		 *
		 * To be called as part of the deactivation hook.
		 *
		 * @param UninstallManager $instance The uninstall manager.
		 */
		public static function on_deactivation( UninstallManager $instance ) {

			if (
				isset( $_GET['delete_action'] ) &&
				in_array( $_GET['delete_action'], array_keys( self::get_default_delete_actions() ), true )
			) {
				update_option( $instance->plugin_slug . '_delete_action', $_GET['delete_action'], false );
			}

		}

		/**
		 * Activation function.
		 *
		 * To be called as part of the activation hook.
		 *
		 * @param UninstallManager $instance The uninstall manager.
		 */
		public static function on_activation( UninstallManager $instance ) {

			add_option( $instance->plugin_slug . '_delete_action', self::$default_delete_action, null, false );

		}

		/**
		 * Uninstall/Delete function.
		 */
		public function uninstall() {

			delete_option( $this->plugin_slug . '_delete_action' );

		}

		/**
		 * Gets the uninstall/delete action.
		 *
		 * @return string Either: 'plugin-only' or 'everything'.
		 */
		public function get_uninstall_action() {

			return get_option( $this->plugin_slug . '_delete_action', self::$default_delete_action );

		}
	}
}

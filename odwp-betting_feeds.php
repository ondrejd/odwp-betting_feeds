<?php
/**
 * Plugin Name: Napojení na XML feedy sázkových kanceláří
 * Plugin URI: https://github.com/ondrejd/odwp-betting_feeds
 * Description: Jednoduchý plugin pro <a href="https://wordpress.org/" target="blank">WordPress</a>, který zobrazuje XML feedy ze sázkových kanceláří ve formě <a href="https://codex.wordpress.org/Shortcode" target="blank">shortcode</a> a <a href="https://codex.wordpress.org/WordPress_Widgets" target="blank">widgetů</a>.
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.1
 * Tags: sidebar widget,shortcode
 * Donate link: https://www.paypal.me/ondrejd
 *
 * Text Domain: odwp-betting_feeds
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_feeds for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_feeds
 * @since 1.0.0
 */

/**
 * This file is just a bootstrap. It checks if requirements of plugins are met
 * and accordingly either initializes the plugin or halts the process.
 *
 * Requirements can be specified for PHP and the WordPress self - version
 * for both, required extensions for PHP and requireds plugins for WP.
 *
 * If you are using copy of original file in your plugin you shoud change
 * prefix "odwpbf" and name "odwp-betting_feeds" to your own values.
 *
 * To set the requirements go down to line 133 and define array that
 * is used as a parameter for `odwpbf_check_requirements` function.
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Some widely used constants
defined( 'BF_SLUG' ) || define( 'BF_SLUG', 'odwpbf' );
defined( 'BF_NAME' ) || define( 'BF_NAME', 'odwp-betting_feeds' );
defined( 'BF_PATH' ) || define( 'BF_PATH', dirname( __FILE__ ) . '/' );
defined( 'BF_FILE' ) || define( 'BF_FILE', __FILE__ );
defined( 'BF_LOG' )  || define( 'BF_LOG', WP_CONTENT_DIR . '/debug.log' );


if( ! function_exists( 'odwpbf_check_requirements' ) ) :
    /**
     * Checks requirements of our plugin.
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    function odwpbf_check_requirements( array $requirements ) {
        global $wp_version;

        // Initialize locales
        load_plugin_textdomain( BF_SLUG, false, dirname( __FILE__ ) . '/languages' );

        /**
         * @var array Hold requirement errors
         */
        $errors = [];

        // Check PHP version
        if( ! empty( $requirements['php']['version'] ) ) {
            if( version_compare( phpversion(), $requirements['php']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'PHP nesplňuje nároky pluginu na minimální verzi (vyžadována nejméně <b>%s</b>)!', BF_SLUG ),
                        $requirements['php']['version']
                );
            }
        }

        // Check PHP extensions
        if( count( $requirements['php']['extensions'] ) > 0 ) {
            foreach( $requirements['php']['extensions'] as $req_ext ) {
                if( ! extension_loaded( $req_ext ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadováno rozšíření PHP <b>%s</b>, to ale není nainstalováno!', BF_SLUG ),
                            $req_ext
                    );
                }
            }
        }

        // Check WP version
        if( ! empty( $requirements['wp']['version'] ) ) {
            if( version_compare( $wp_version, $requirements['wp']['version'], '<' ) ) {
                $errors[] = sprintf(
                        __( 'Plugin vyžaduje vyšší verzi platformy <b>WordPress</b> (minimálně <b>%s</b>)!', BF_SLUG ),
                        $requirements['wp']['version']
                );
            }
        }

        // Check WP plugins
        if( count( $requirements['wp']['plugins'] ) > 0 ) {
            $active_plugins = (array) get_option( 'active_plugins', [] );
            foreach( $requirements['wp']['plugins'] as $req_plugin ) {
                if( ! in_array( $req_plugin, $active_plugins ) ) {
                    $errors[] = sprintf(
                            __( 'Je vyžadován plugin <b>%s</b>, ten ale není nainstalován!', BF_SLUG ),
                            $req_plugin
                    );
                }
            }
        }

        return $errors;
    }
endif;


if( ! function_exists( 'odwpbf_deactivate_raw' ) ) :
    /**
     * Deactivate plugin by the raw way.
     * @return void
     * @since 1.0.0
     */
    function odwpbf_deactivate_raw() {
        $active_plugins = get_option( 'active_plugins' );
        $out = [];
        foreach( $active_plugins as $key => $val ) {
            if( $val != BF_NAME . '/' . BF_NAME . '.php' ) {
                $out[$key] = $val;
            }
        }
        update_option( 'active_plugins', $out );
    }
endif;


if( ! function_exists( 'odwpbf_error_log' ) ) :
    /**
     * @internal Write message to the `wp-content/debug.log` file.
     * @param string $message
     * @param integer $message_type (Optional.)
     * @param string $destination (Optional.)
     * @param string $extra_headers (Optional.)
     * @return void
     * @since 1.0.0
     */
    function odwpbf_error_log( string $message, int $message_type = 0, string $destination = null, string $extra_headers = '' ) {
        if( ! file_exists( BF_LOG ) || ! is_writable( BF_LOG ) ) {
            return;
        }

        $record = '[' . date( 'd-M-Y H:i:s', time() ) . ' UTC] ' . $message;
        file_put_contents( BF_LOG, PHP_EOL . $record, FILE_APPEND );
    }
endif;


if( ! function_exists( 'odwpbf_write_log' ) ) :
    /**
     * Write record to the `wp-content/debug.log` file.
     * @param mixed $log
     * @return void
     * @since 1.0.0
     */
    function odwpbf_write_log( $log ) {
        if( is_array( $log ) || is_object( $log ) ) {
            odwpbf_error_log( print_r( $log, true ) );
        } else {
            odwpbf_error_log( $log );
        }
    }
endif;


if( ! function_exists( 'readonly' ) ) :
    /**
     * Prints HTML readonly attribute. It's an addition to WP original
     * functions {@see disabled()} and {@see checked()}.
     * @param mixed $value
     * @param mixed $current (Optional.) Defaultly TRUE.
     * @return string
     * @since 1.0.0
     */
    function readonly( $current, $value = true ) {
        if( $current == $value ) {
            echo ' readonly';
        }
    }
endif;


/**
 * Errors from the requirements check
 * @var array
 */
$odwpbf_errs = odwpbf_check_requirements( [
    'php' => [
        // Enter minimum PHP version you needs.
        'version' => '5.6',
        // Enter extensions that your plugin needs
        'extensions' => [
            //'gd',
        ],
    ],
    'wp' => [
        // Enter minimum WP version you need
        'version' => '4.8',
        // Enter WP plugins that your plugin needs
        'plugins' => [
            //'woocommerce/woocommerce.php',
        ],
    ],
] );


// Check if requirements are met or not
if( count( $odwpbf_errs ) > 0 ) {
    // Requirements are not met
    odwpbf_deactivate_raw();

    // In administration print errors
    if( is_admin() ) {
        $err_head = __( '<b>Napojení na XML feedy sázkových kanceláří</b>: ', BF_SLUG );
        foreach( $odwpbf_errs as $err ) {
            printf( '<div class="error"><p>%s</p></div>', $err_head . $err );
        }
    }
} else {
    // Requirements are met so initialize the plugin.
    include( BF_PATH . 'src/BF_Screen_Prototype.php' );
    include( BF_PATH . 'src/BF_Plugin.php' );
    BF_Plugin::initialize();
}

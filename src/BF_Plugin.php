<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_feeds for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_feeds
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'BF_Plugin' ) ) :

/**
 * Main class.
 * @since 1.0.0
 */
class BF_Plugin {
    /**
     * @const string Plugin's version.
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * @const string
     * @since 1.0.0
     */
    const SETTINGS_KEY = BF_SLUG . '_settings';

    /**
     * @const string
     * @since 1.0.0
     */
    const TABLE_NAME = BF_SLUG;

    /**
     * @var array $admin_screens Array with admin screens.
     * @since 1.0.0
     */
    public static $admin_screens = [];

    /**
     * @var string
     * @since 1.0.0
     */
    public static $options_page_hook;

    /**
     * @internal Activates the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function activate() {
        //...
    }

    /**
     * @internal Deactivates the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function deactivate() {
        //...
    }

    /**
     * @return array Default values for settings of the plugin.
     * @since 1.0.0
     */
    public static function get_default_options() {
        return [
            //...
        ];
    }

    /**
     * @return array Settings of the plugin.
     * @since 1.0.0
     */
    public static function get_options() {
        $defaults = self::get_default_options();
        $options = get_option( self::SETTINGS_KEY, [] );
        $update = false;

        // Fill defaults for the options that are not set yet
        foreach( $defaults as $key => $val ) {
            if( ! array_key_exists( $key, $options ) ) {
                $options[$key] = $val;
                $update = true;
            }
        }

        // Updates options if needed
        if( $update === true) {
            update_option( self::SETTINGS_KEY, $options );
        }

        return $options;
    }

    /**
     * Returns value of option with given key.
     * @param string $key Option's key.
     * @param mixed $value Option's value.
     * @return mixed Option's value.
     * @since 1.0.0
     * @throws Exception Whenever option with given key doesn't exist.
     */
    public static function get_option( $key, $default = null ) {
        $options = self::get_options();

        if( array_key_exists( $key, $options ) ) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Initializes the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function initialize() {
        register_activation_hook( BF_FILE, [__CLASS__, 'activate'] );
        register_deactivation_hook( BF_FILE, [__CLASS__, 'deactivate'] );
        register_uninstall_hook( BF_FILE, [__CLASS__, 'uninstall'] );

        add_action( 'init', [__CLASS__, 'init'] );
        add_action( 'admin_init', [__CLASS__, 'admin_init'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'admin_bar_menu', [__CLASS__, 'admin_menu_bar'], 100 );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
    }

    /**
     * Hook for "init" action.
     * @return void
     * @since 1.0.0
     */
    public static function init() {
        // Initialize locales
        $path = BF_PATH . 'languages';
        load_plugin_textdomain( BF_SLUG, false, $path );

        // Initialize options
        $options = self::get_options();

        // Initialize admin screens
        self::init_screens();
        self::screens_call_method( 'init' );
    }

    /**
     * Initialize settings using <b>WordPress Settings API</b>.
     * @link https://developer.wordpress.org/plugins/settings/settings-api/
     * @return void
     * @since 1.0.0
     */
    protected static function init_settings() {
        $section1 = self::SETTINGS_KEY . '_section_1';
        add_settings_section(
                $section1,
                __( 'Název sekce', BF_SLUG ),
                [__CLASS__, 'render_settings_section_1'],
                BF_SLUG
        );

        add_settings_field(
                'setting_field_1',
                __( 'Název nastavení', BF_SLUG ),
                [__CLASS__, 'render_settings_field_1'],
                BF_SLUG,
                $section1
        );
    }

    /**
     * Initialize admin screens.
     * @return void
     * @since 1.0.0
     */
    protected static function init_screens() {
        include( BF_PATH . 'src/BF_Screen_Prototype.php' );
        include( BF_PATH . 'src/BF_Options_Screen.php' );

        /**
         * @var BF_Options_Screen $options_screen
         */
        $options_screen = new BF_Options_Screen();
        self::$admin_screens[$options_screen->get_slug()] = $options_screen;

        /**
         * @var BF_Log_Screen $log_screen
         */
        $log_screen = new BF_Log_Screen();
        self::$admin_screens[$log_screen->get_slug()] = $log_screen;
    }

    /**
     * Hook for "admin_init" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_init() {
        register_setting( BF_SLUG, self::SETTINGS_KEY );

        // Check environment
        self::check_environment();

        // Initialize Settings API
        self::init_settings();

        // Initialize admin screens
        self::screens_call_method( 'admin_init' );

        // Initialize dashboard widgets
        include( BF_PATH . 'src/BF_Log_Dashboard_Widget.php' );
        add_action( 'wp_dashboard_setup', ['BF_Log_Dashboard_Widget', 'init'] );
    }

    /**
     * Hook for "admin_menu" action.
     * @return void
     * @since 1.0.0
     */
    public static function admin_menu() {
        // Call action for `admin_menu` hook on all screens.
        self::screens_call_method( 'admin_menu' );
    }

    /**
     * Hook for "admin_menu_bar" action.
     * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar/add_menu
     * @param \WP_Admin_Bar $bar
     * @return void
     * @since 1.0.0
     */
    public static function admin_menu_bar( \WP_Admin_Bar $bar ) {
        // Get options
        $options = self::get_options();

        // Prepare arguments for new admin bar node
        $args  = [
            'id'     => 'odwpbf-adminbar_item',
            'href'   => admin_url( 'tools.php?page=' . BF_SLUG . '-log' ),
            'parent' => 'top-secondary',
            'meta'   => [],
        ];

        // Get log records count
        $count_prev = self::get_option( 'prev_log_count', 0 );
        $count_current = self::get_log_count();

        $args['meta']['title'] = __( 'Zobrazit ladící zprávy', BF_LOG );
        $icon = sprintf(
            '<img src="%s" alt="%s">',
            plugins_url( '/images/icon-24.png', BF_FILE ),
                __( 'DL', BF_SLUG )
        );

        if( $count_current <= $count_prev ) {
            $display = '<span class="odwpdl-ab-log">' . $icon . '</span>';
        } else {
            $display = sprintf(
                '<span class="odwpdl-ab-log odwpdl-ab-log-active">%s</span> ' .
                '<span class="odwpdl-ab-bubble">%d</span>',
                $icon,
                abs( $count_current - $count_prev )
            );
        }

        $args['title'] = $display;

        // Add our admin bar item
        $bar->add_node( $args );

        // Note: Current log count is saved in file `partials/screen-log.phtml`.
    }

    /**
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     * @since 1.0.0
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( BF_SLUG, plugins_url( 'js/admin.js', BF_FILE ), ['jquery'] );
        wp_localize_script( BF_SLUG, 'odwpdl', [
            //...
        ] );
        wp_enqueue_style( BF_SLUG, plugins_url( 'css/admin.css', BF_FILE ) );
    }

    /**
     * Checks environment we're running and prints admin messages if needed.
     * @return void
     * @since 1.0.0
     */
    public static function check_environment() {
        if( ! file_exists( BF_LOG ) || ! is_writable( BF_LOG ) ) {
            add_action( 'admin_notices', function() {
                $msg = sprintf(
                        __( '<strong>Debug Log Viewer</strong>: Soubor (<code>%s</code>) k zápisu ladících informací není vytvořen nebo není zapisovatelný. Pro více informací přejděte na <a href="%s">nastavení tohoto pluginu</a>.', BF_SLUG ),
                        BF_LOG,
                        admin_url( 'options-general.php?page=' . BF_SLUG . '-plugin_options' )
                );
                BF_Plugin::print_admin_notice( $msg );
            } );
        }

        /**
         * @var string $err_msg Error message about setting WP_DEBUG and WP_DEBUG_LOG constants.
         */
        $err_msg = sprintf(
                __( 'Pro umožnění zápisu ladících informací do logovacího souboru (<code>%s</code>) musí být konstanty <code>%s</code> a <code>%s</code> nastaveny na hodnotu <code>TRUE</code>. Pro více informací přejděte na <a href="%s">nastavení tohoto pluginu</a>.', BF_SLUG ),
                BF_LOG,
                'WP_DEBUG',
                'WP_DEBUG_LOG',
                admin_url( 'options-general.php?page=' . BF_SLUG . '-plugin_options' )
        );

        if( ! defined( 'WP_DEBUG' ) || ! defined( 'WP_DEBUG_LOG' ) ) {
            self::print_admin_notice( $err_msg, 'error' );
        }

        if( ! defined( 'WP_DEBUG' ) || ! defined( 'WP_DEBUG_LOG' ) ) {
            self::print_admin_notice( $err_msg, 'error' );
        }
    }

    /**
     * Hook for "plugins_loaded" action.
     * @return void
     * @since 1.0.0
     */
    public static function plugins_loaded() {
        //...
    }

    /**
     * Hook for "wp_enqueue_scripts" action.
     * @return void
     * @since 1.0.0
     */
    public static function enqueue_scripts() {
        //wp_enqueue_script( BF_SLUG, plugins_url( 'js/public.js', BF_FILE ), ['jquery'] );
        //wp_localize_script( BF_SLUG, 'odwpng', [
        //    //...
        //] );
        //wp_enqueue_style( BF_SLUG, plugins_url( 'css/public.css', BF_FILE ) );
    }

    /**
     * @internal Renders the first settings section.
     * @return void
     * @since 1.0.0
     */
    public static function render_settings_section_1() {
        ob_start( function() {} );
        include( BF_PATH . 'partials/settings-section_1.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Renders setting `setting_field_1`.
     * @return void
     * @since 1.0.0
     */
    public static function render_settings_field_1() {
        ob_start( function() {} );
        include( BF_PATH . '/partials/setting-field_1.phtml' );
        echo ob_get_flush();
    }

    /**
     * @internal Uninstalls the plugin.
     * @return void
     * @since 1.0.0
     */
    public static function uninstall() {
        if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }

        // Nothing to do...
    }

    /**
     * Updates user option `prev_log_count`.
     * @return void
     * @since 1.0.0
     */
    public static function updates_prev_log_count() {
        $options = self::get_options();
        $options['prev_log_count'] = self::get_log_count();
        update_option( self::SETTINGS_KEY, $options );
    }

    /**
     * @internal Prints error message in correct WP amin style.
     * @param string $msg Error message.
     * @param string $type (Optional.) One of ['error','info','success','warning'].
     * @param boolean $dismissible (Optional.) Is notice dismissible?
     * @return void
     * @since 1.0.0
     */
    public static function print_admin_notice( $msg, $type = 'info', $dismissible = true ) {
        $class = 'notice';

        if( in_array( $type, ['error','info','success','warning'] ) ) {
            $class .= ' notice-' . $type;
        } else {
            $class .= ' notice-info';
        }

        if( $dismissible === true) {
            $class .= ' s-dismissible';
        }
        
        printf( '<div class="%s"><p>%s</p></div>', $class, $msg );
    }

    /**
     * On all screens call method with given name.
     *
     * Used for calling hook's actions of the existing screens.
     * See {@see BF_Plugin::admin_menu} for an example how is used.
     *
     * If method doesn't exist in the screen object it means that screen
     * do not provide action for the hook.
     *
     * @access private
     * @param  string  $method
     * @return void
     * @since 1.0.0
     */
    private static function screens_call_method( $method ) {
        foreach ( self::$admin_screens as $slug => $screen ) {
            if( method_exists( $screen, $method ) ) {
                call_user_func( [ $screen, $method ] );
            }
        }
    }
}

endif;

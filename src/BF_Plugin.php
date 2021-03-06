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
     * @since 1.0.0
     * @var array $admin_screens Array with admin screens.
     */
    public static $admin_screens = [];

    /**
     * @since 1.0.0
     * @var string $options_page_hook
     */
    public static $options_page_hook;

    /**
     * @since 1.0.0
     * @var string $upload_dir
     */
    private static $upload_dir;

    /**
     * @since 1.0.0
     * @var string $cache_dir
     */
    private static $cache_dir;

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
     * @param string $path (Optional.) Path to append.
     * @return string Path to plugin's cache/upload directory.
     * @since 1.0.0
     */
    public static function get_cache_dir( $path = null ) {
        if( ! is_array( self::$upload_dir ) ) {
            self::$upload_dir = wp_upload_dir();
        }

        self::$cache_dir = self::$upload_dir['basedir'] . '/' . BF_SLUG;

        if( empty( $path ) ) {
            return self::$cache_dir;
        }

        return self::$cache_dir . '/' . ltrim( $path, '/' );
    }

    /**
     * @return array Default values for settings of the plugin.
     * @since 1.0.0
     */
    public static function get_default_options() {
        return [
            'setting_field_1' => 'test',
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
        add_action( 'admin_init', [__CLASS__, 'init_admin'] );
        add_action( 'admin_menu', [__CLASS__, 'admin_menu'] );
        add_action( 'plugins_loaded', [__CLASS__, 'plugins_loaded'] );
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_action( 'admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts'] );
        add_action( 'widgets_init', [__CLASS__, 'init_widgets'] );
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
     * Hook for "admin_init" action.
     * @return void
     * @since 1.0.0
     */
    public static function init_admin() {
        register_setting( BF_SLUG, self::SETTINGS_KEY );

        // Check environment
        self::check_environment();

        // Initialize Settings API
        self::init_settings();

        // Initialize admin screens
        self::screens_call_method( 'admin_init' );
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

        // Add help tabs
        add_action( 'load-post.php', [__CLASS__, 'add_shortcode_help_tab'], 20 );
        add_action( 'load-post-new.php', [__CLASS__, 'add_shortcode_help_tab'], 20 );
        add_action( 'load-edit.php', [__CLASS__, 'add_shortcode_help_tab'], 20 );
        add_action( 'load-widgets.php', [__CLASS__, 'add_widget_help_tab'], 20 );
    }

    /**
     * Hook for "widgets_init" action.
     * @return void
     * @since 1.0.0
     */
    public static function init_widgets() {
        include( BF_PATH . 'src/BF_PMTable_Widget.php' );
        include( BF_PATH . 'src/BF_PMCurrentRound_Widget.php' );

        register_widget( 'BF_PMTable_Widget' );
        register_widget( 'BF_PMCurrentRound_Widget' );
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
     * Hook for "admin_enqueue_scripts" action.
     * @param string $hook
     * @return void
     * @since 1.0.0
     */
    public static function admin_enqueue_scripts( $hook ) {
        wp_enqueue_script( BF_SLUG, plugins_url( 'js/admin.js', BF_FILE ), ['jquery'] );
        wp_localize_script( BF_SLUG, 'odwpbf', [
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
        // We need to check if cache/uploads directory is available and writable.
        $cache_dir = self::get_cache_dir();

        // check/create wp-content/uploads/odwpbf
        if( ! file_exists( $cache_dir ) ) {
            if( ! mkdir( $cache_dir, 0777, true) ) {
                add_action( 'admin_notices', function() use ( $cache_dir ) {
                    BF_Plugin::print_admin_notice( sprintf(
                        __( 'Plugin <strong>Napojení na XML feedy sázkových kanceláří</strong>: Nelze vytvořit odkládací adresář (<code>%s</code>).', BF_SLUG ),
                        $cache_dir
                    ) );
                } );
                return;
            }
        }

        // File `pmtable.json` is used in BF_PMTable_Widget
        include_once( BF_PATH . 'src/BF_PMTable_DataSource.php' );
        $pmtable_json = self::get_cache_dir( 'pmtable.json' );

        // check/create wp-content/uploads/odwpbf/pmtable.json
        if( ! BF_PMTable_DataSource::create_default_data() ) {
            add_action( 'admin_notices', function() use ( $pmtable_json ) {
                BF_Plugin::print_admin_notice( sprintf(
                    __( 'Plugin <strong>Napojení na XML feedy sázkových kanceláří</strong>: Nelze vytvořit odkládací soubor (<code>%s</code>) pro widge s výsledkovou tabulkou.', BF_SLUG ),
                    $pmtable_json
                ) );
            } );
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
        wp_enqueue_script( BF_SLUG, plugins_url( 'js/public.js', BF_FILE ), ['jquery'] );
        wp_localize_script( BF_SLUG, 'odwpbf', [
            //...
        ] );
        wp_enqueue_style( BF_SLUG, plugins_url( 'css/public.css', BF_FILE ) );
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

    /**
     * Hook for "load-[post|post-new|edit].php" actions.
     * @return void
     * @since 1.0.0
     */
    public static function add_shortcode_help_tab() {
        /** @var \WP_Screen $screen */
        $screen = get_current_screen();

        if( ! in_array( $screen->id, ['post', /*'edit-post',*/ 'page'] ) ) {
            return;
        }

        ob_start( function() {} );
        include( BF_PATH . 'partials/help-shortcode_help.phtml' );
        $help_tab = ob_get_flush();

        $screen->add_help_tab( [
            'id'       => BF_SLUG . '-help',
            'title'    => __( 'Sportovní feedy', BF_SLUG ),
            'content'  => $help_tab,
        ] );
    }

    /**
     * Hook for "load-widgets.php" action.
     * @return void
     * @since 1.0.0
     */
    public static function add_widget_help_tab() {
        /** @var \WP_Screen $screen */
        $screen = get_current_screen();

        if( ! strstr( $screen->id, 'widgets' ) ) {
            return;
        }

        ob_start( function() {} );
        include( BF_PATH . 'partials/help-widget_help.phtml' );
        $help_tab = ob_get_flush();

        $screen->add_help_tab( [
            'id'       => BF_SLUG . '-widget_help',
            'title'    => __( 'Sportovní feedy', BF_SLUG ),
            'content'  => $help_tab,
        ] );
    }
}

endif;

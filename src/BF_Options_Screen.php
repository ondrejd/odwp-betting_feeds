<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_fields for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_fields
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'BF_Options_Screen' ) ):

/**
 * Administration screen for plugin's options.
 * @since 1.0.0
 */
class BF_Options_Screen extends BF_Screen_Prototype {
    /**
     * Constructor.
     * @param WP_Screen $screen Optional.
     * @return void
     * @since 1.0.0
     */
    public function __construct( \WP_Screen $screen = null ) {
        // Main properties
        $this->_slug = 'plugin_options';
        $this->slug = BF_SLUG . '-' . $this->_slug;
        $this->menu_title = __( 'Sportovní XML feedy', BF_SLUG );
        $this->page_title = __( 'Nastavení pluginu <i>Napojení na XML feedy sázkových kanceláří</i>', BF_SLUG );

        // Specify help tabs
        $this->help_tabs = [];

        // Specify help sidebars
        $this->help_sidebars = [];

        // Specify screen options
        $this->options = [];
        $this->enable_screen_options = false;

        // Finish screen constuction
        parent::__construct( $screen );
    }

    /**
     * Action for `admin_menu` hook.
     * @return void
     * @since 1.0.0
     */
    public function admin_menu() {
        $this->hookname = add_options_page(
                $this->page_title,
                $this->menu_title,
                'manage_options',
                $this->slug,
                [$this, 'render']
        );

        add_action( 'load-' . $this->hookname, [$this, 'screen_load'] );
    }
}

endif;

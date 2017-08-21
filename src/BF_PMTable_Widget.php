<?php
/**
 * Widget, který zobrazuje výsledkovou tabulku Premier League ({@link https://premierleague.cz/tabulka/}).
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_feeds for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_feeds
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}


if( ! class_exists( 'BF_PMTable_Widget' ) ) :

/**
 * Widget, který zobrazuje výsledkovou tabulku Premier League ({@link https://premierleague.cz/tabulka/}).
 * @link https://developer.wordpress.org/reference/classes/wp_widget/
 * @since 1.0.0
 */
class BF_PMTable_Widget extends WP_Widget {
    /**
     * Constructor.
     * @param string $id_base
     * @param string $name
     * @param array $widget_options (Optional.) Widget options.
     * @param array $control_options (Optional.) Widget control options.
     * @return void
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            BF_SLUG . '-pmtable_widget',
            __( 'Výsledková tabulka PM', BF_SLUG ), [
                'description' => __( 'Premier League 2017/18', BF_SLUG ),
                'league'      => 'pm2018',
                'show_col1'   => true,
                'show_col2'   => true,
                'show_col3'   => true,
                'show_col4'   => true,
                'show_col5'   => true,
                'show_col6'   => true,
                'show_col7'   => true,
                'show_col8'   => true,
                'show_col9'   => true,
            ], []
        );
    }

    /**
     * Prints the widget content.
     * @param array $args Display arguments.
     * @param array $instance Settings for the widget's instance.
     * @return void
     * @since 1.0.0
     */
    public function widget( $args, $instance ) {
        ob_start( function() {} );
        include( BF_PATH . 'partials/widget-pmtable.phtml' );
        echo ob_get_flush();
    }

    /**
     * Outputs the settings update form.
     * @param array $instance Current settings.
     * @return string Default return is 'noform'.
     * @since 1.0.0
     */
    public function form( $instance ) {
        $self = $this;
        ob_start( function() {} );
        include( BF_PATH . 'partials/widget_form-pmtable.phtml' );
        echo ob_get_flush();
        //return 'noform';
    }

    /**
     * Updates a particular instance of a widget.
     * @param array $new_instance Settings for the new widget's instance.
     * @param array $old_instance Settings for the old widget's instance.
     * @return mixed Array with settings to save or FALSE to cancel saving.
     * @return void
     * @since 1.0.0
     */
    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }
}

endif;











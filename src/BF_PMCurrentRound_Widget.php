<?php
/**
 * Widget s aktuálním kolem Premier League ({@link https://premierleague.cz/zapasy/?season=2017}).
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_feeds for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_feeds
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}


if( ! class_exists( 'BF_PMCurrentRound_Widget' ) ) :

/**
 * Widget s aktuálním kolem Premier League ({@link https://premierleague.cz/zapasy/?season=2017}).
 * @link https://developer.wordpress.org/reference/classes/wp_widget/
 * @since 1.0.0
 */
class BF_PMCurrentRound_Widget extends WP_Widget {
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
            BF_SLUG . '-pmcurround_widget',
            __( 'Aktuální kolo PM', BF_SLUG ), [
                'description' => __( 'PM - Aktuální kolo', BF_SLUG ),
                'show_season_filter' => false,
                'show_round_filter' => false,
                'show_title' => true,
                'show_icon' => true,
                'show_date' => true,
                'show_result' => false,
            ],
            []
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
        include( BF_PATH . 'partials/widget-pmcurrent_round.phtml' );
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
        include( BF_PATH . 'partials/widget_form-pmcurrent_round.phtml' );
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

<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-betting_feeds for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-betting_feeds
 * @since 1.0.0
 */

if( ! class_exists( 'BF_PMTable_DataItem' ) ) :

/**
 * Jednoduchá třída reprezentující jeden řádek výsledkové tabulky.
 * @see BF_PMTable_DataSource
 * @since 1.0.0
 */
class BF_PMTable_DataItem {
    public $poradi;
    public $tym;
    public $zapasy;
    public $vyhry;
    public $remizy;
    public $prohry;
    public $goly_o;
    public $goly_v;
    public $body;
}

endif;

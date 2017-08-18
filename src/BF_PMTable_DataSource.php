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

if( ! class_exists( 'BF_PMTable_DataItem' ) ) :
    include_once( BF_PATH . 'src/BF_PMTable_DataItem.php' );
endif;


if( ! class_exists( 'BF_PMTable_DataSource' ) ) :

/**
 * Datový zdroj pro {@see BF_PMTable_Widget}.
 * @since 1.0.0
 */
class BF_PMTable_DataSource {
    /**
     * @const string URL aktuální tabulky PM.
     * @since 1.0.0
     */
    const DATA_URL = 'https://premierleague.cz/tabulka/';

    /**
     * @since 1.0.0
     * @var array $data
     */
    protected static $data = null;

    /**
     * Parsuje data ze stránky {@link https://premierleague.cz/tabulka/}
     * do JSONu, který se pak používá při zobrazení {@see BF_PMTable_Widget}.
     * Volá se přes WP_Cron.
     * @return void
     * @since 1.0.0
     */
    public static function cron_job() {
        // Získáme HTML dokument
        $html = file_get_contents( self::DATA_URL );

        // Vytvoříme z něho DOM objekt
        $dom = new \DOMDocument();
        $dom->loadHTML( $html );

        // Najdeme tabulku s výsledky
        $cont = $dom->getElementById( 'no-more-tables' );
        if( ! ( $cont instanceof \DOMElement ) ) {
            return;
        }

        $tables = $cont->getElementsByTagName( 'table' );
        $table = null;

        if( $tables->length > 0 ) {
            $table = $tables->item( 0 );
        }

        if( ! ( $table instanceof \DOMElement ) ) {
            return;
        }

        // Převedeme z ní data do objektu/pole pro JSON
        $tbodies = $table->getElementsByTagName( 'tbody' );

        if( $tbodies->length <= 0 ) {
            return;
        }

        $rows = $tbodies->item( 0 )->getElementsByTagName( 'tr' );
        $data = '';

        for( $i = 0; $i < $rows->length; $i++ ) {
            $row = $rows->item( $i );
            $tds = $row->getElementsByTagName( 'td' );
            $itm = new BF_PMTable_DataItem();

            for( $y = 0; $y < $tds->length; $y++ ) {
                switch( $y ) {
                    case 0: $itm->poradi = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 1: $itm->tym    = trim( $tds->item( $y )->textContent ); break;
                    case 2: $itm->zapasy = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 3: $itm->vyhry  = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 4: $itm->remizy = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 5: $itm->prohry = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 6: $itm->goly_v = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 7: $itm->goly_o = ( int ) trim( $tds->item( $y )->textContent ); break;
                    case 8: $itm->body   = ( int ) trim( $tds->item( $y )->textContent ); break;
                }

                $data[] = $itm;
            }
        }

        // Uložíme pmtable.json
        $json = json_encode( $data );
        file_put_contents( BF_Plugin::get_cache_dir( 'pmtable.json' ), $json );
    }

    /**
     * Vrátí data pro {@see BF_PMTable_Widget}.
     * @return array
     * @see BF_PMTable_Widget::widget()
     * @since 1.0.0
     */
    public static function get_data() {
        $pmtable_json = BF_Plugin::get_cache_dir( 'pmtable.json' );

        $json_str = file_get_contents( $pmtable_json );
        if( $json_str === false ) {
            return [];
        }

        $json_arr = json_decode( $json_str, true );
        if( $json_arr === false ) {
            return [];
        }

        return $json_arr;
    }

    /**
     * Vytvoří soubor defaultní soubor pmtable.json.
     * @return boolean
     * @see BF_Plugin::check_environment()
     * @since 1.0.0
     */
    public static function create_default_data() {
        // Vytvoříme prázdný soubor `pmtable.json`.
        $pmtable_json = BF_Plugin::get_cache_dir( 'pmtable.json' );
        $res = file_put_contents( $pmtable_json, '{}' );

        // Zkusíme získat data rovnou z webu.
        self::cron_job();

        // Pokud je soubor `pmtable.json` vytvořen vrátíme TRUE.
        return ( $res !== false );
    }
}

endif;

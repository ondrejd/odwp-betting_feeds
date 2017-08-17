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
    protected static $data;

    /**
     * Parsuje data ze stránky {@link https://premierleague.cz/tabulka/} do JSONu, který se pak používá při zobrazení {@see BF_PMTable_Widget}. Volá se přes WP_Cron.
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
        //...
        // Převedeme z ní data do objektu/pole pro JSON
        //...
        // Uložíme pmtable.json
        //...

echo '<pre>';
var_dump( $dom );
exit();
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

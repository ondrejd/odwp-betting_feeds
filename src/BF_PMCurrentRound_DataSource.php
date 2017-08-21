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

//if( ! class_exists( 'BF_PMCurrentRound_DataItem' ) ) {
//    include_once( BF_PATH . 'src/BF_PMCurrentRound_DataItem.php' );
//}

if( ! class_exists( 'BF_PMCurrentRound_DataItem' ) ) :
    /**
     * Jeden radek z parsovane tabulky.
     * @since 1.0.0
     */
    class BF_PMCurrentRound_DataItem {
        public $kolo;
        public $tym1;
        public $stav;
        public $tym2;
    }
endif;


if( ! class_exists( 'BF_PMCurrentRound_DataSource' ) ) :

/**
 * Datový zdroj pro {@see BF_PMCurrentRound_Widget}.
 * @since 1.0.0
 */
class BF_PMCurrentRound_DataSource {
    /**
     * @const string URL aktuálního kola PM.
     * @since 1.0.0
     */
    const DATA_URL = 'https://premierleague.cz/zapasy/';

    /**
     * @since 1.0.0
     * @var string Zvolené kolo.
     */
    protected static $round = null;

    /**
     * @since 1.0.0
     * @var string Zvolená sezóna.
     */
    protected static $season = null;
    
    /**
     * @since 1.0.0
     * @var array $data
     */
    protected static $data = null;

    /**
     * @internal
     * @param string $season (Optional.) Season [2018,2017,2016].
     * @param string $round (Optional.) Round.
     * @return string URL of the source HTML page.
     */
    protected static function get_html_url( $season = null, $round = null ) {
        $url = self::DATA_URL;

        if( ! is_null( $season ) ) {
            $url .= '?season=' . $season;
        }

        if( ! is_null( $round ) ) {
            $url .= ( is_null( $season ) ? '?' : '&' ) .'matchday=' . $round;
        }

        return $url;
    }

    /**
     * Parsuje data ze stránky {@link https://premierleague.cz/tabulka/}
     * do JSONu, který se pak používá při zobrazení {@see BF_PMTable_Widget}.
     * Volá se přes WP_Cron.
     * @return void
     * @since 1.0.0
     */
    public static function cron_job() {
        // Získáme HTML dokument
        $html = file_get_contents( self::get_html_url() );

        // Vytvoříme z něho DOM objekt
        $dom = new \DOMDocument();
        $dom->loadHTML( $html );

echo '<pre>';
echo '================================'.PHP_EOL;
        // Najdeme tabulku s výsledky
        $content = $dom->getElementById( 'content' );
        if( ! ( $content instanceof \DOMElement ) ) {
            return;
        }

        // Nejprve potřebujeme <div class="container">
        $divs = $content->getElementsByTagName( 'div' );
        $cont = null;
        for( $i = 0; $i < $divs->length; $i++ ) {
            $div = $divs->item( $i );

            if( ! $div->hasAttribute( 'class' ) ) {
                continue;
            }

            if( strstr( $div->getAttribute( 'class' ), 'container' ) ) {
                $cont = $div;
                break;
            }
        }

        // Pak vsechny <div class="row">
        $divs = $cont->getElementsByTagName( 'div' );
        $rows = [];
        for( $i = 0; $i < $divs->length; $i++ ) {
            $div = $divs->item( $i );

            if( ! $div->hasAttribute( 'class' ) ) {
                continue;
            }

            if( strstr( $div->getAttribute( 'class' ), 'match-row' ) ) {
                $rows[] = $div;
            }
        }

        // Teď projdeme všechny řádky a získáme samotná data
        /* Struktura jednoho řádku je:
        <div class="row match-row">
            <div>... kolo ...</div>
            <div>... domaci tym ...</div>
            <h3>... stav/vysledek ...</h3>
            <div>... hostujici tym ...</div>
        </div>
        */
        $data = [];

        for( $i = 0; $i < count( $rows ); $i++ ) {
            $row = $rows[$i];
            $itm = new BF_PMCurrentRound_DataItem();

            for( $y = 0; $y < $row->childNodes->length; $y++ ) {
                $node = $row->childNodes->item( $y );
            
                if( strtolower( $node->nodeName ) == 'div' ) {
                    switch( $y ) {
                        case 1: $itm->kolo = self::trim_html_text_content( $node->textContent ); break;
                        case 3: $itm->tym1 = self::trim_html_text_content( $node->textContent ); break;
                        case 7: $itm->tym2 = self::trim_html_text_content( $node->textContent ); break;
                    }
                }
                elseif( strtolower( $node->nodeName ) == 'h3' && $y == 5) {
                    $itm->stav = self::trim_html_text_content( $node->textContent );
                }
            }

            $data[] = $itm;
        }

        // Uložíme pmcurrent_round.json
        $json = json_encode( $data );
        file_put_contents( BF_Plugin::get_cache_dir( 'pmcurrent_round.json' ), $json );
    }

    /**
     * 
     * @param string $content
     * @return string
     */
    private static function trim_html_text_content( $content ) {
        $parts = explode( '\r\n', $content );

        if( count( $parts ) <= 1) {
            return trim( $content );
        }

        $ret = '';

        foreach( $parts as $part ) {
            $ret .= trim(  $part );
        }

        return $ret;
    }

    /**
     * Vrátí data pro {@see BF_PMTable_Widget}.
     * @return array
     * @see BF_PMTable_Widget::widget()
     * @since 1.0.0
     */
    public static function get_data() {
        $pmcr_json = BF_Plugin::get_cache_dir( 'pmcurrent_round.json' );
        if( ! file_exists( $pmcr_json ) || ! is_readable( $pmcr_json ) ) {
            if( ! self::create_default_data() ) {
                return [];
            }
        }

        $json_str = file_get_contents( $pmcr_json );
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
     * Vytvoří soubor defaultní soubor pmcurrent_round.json.
     * @return boolean
     * @see BF_Plugin::check_environment()
     * @since 1.0.0
     */
    public static function create_default_data() {
        // Vytvoříme prázdný soubor `pmcurrent_round.json`.
        $pmcr_json = BF_Plugin::get_cache_dir( 'pmcurrent_round.json' );
        $res = file_put_contents( $pmcr_json, '{}' );

        // Zkusíme získat data rovnou z webu.
        self::cron_job();

        // Pokud je soubor `pmcurrent_round.json` vytvořen vrátíme TRUE.
        return ( $res !== false );
    }
}

endif;

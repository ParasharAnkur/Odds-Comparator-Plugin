<?php
/**
 * Plugin Name: Odds Comparator
 * Description: Fetch and display live odds from multiple bookmakers with Gutenberg support.
 * Version: 1.1
 * Author: Ankur Parashar
 * Author URI: https://github.com/ParasharAnkur
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Load dependencies
require_once plugin_dir_path(__FILE__) . 'admin/class-oc-admin-interface.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-oc-scraper.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-oc-odds-converter.php';

// Initialize admin interface
new OC_Admin_Interface();

/**
 * Register the Gutenberg block for odds comparator
 */
function oc_register_block() {
    wp_register_script(
        'oc-odds-block',
        plugins_url('blocks/odds-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/odds-block.js')
    );

    register_block_type('oc/odds-comparator', array(
        'editor_script'   => 'oc-odds-block',
        'render_callback' => 'oc_render_odds_block'
    ));
}
add_action('init', 'oc_register_block');

/**
 * Enqueue block editor assets
 */
function oc_editor_assets() {
    wp_enqueue_script(
        'oc-odds-block',
        plugins_url('blocks/odds-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/odds-block.js')
    );
}
add_action('enqueue_block_editor_assets', 'oc_editor_assets');

/**
 * Enqueue frontend DataTables assets
 */
function oc_enqueue_frontend_assets() {
    // DataTables CSS/JS
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css');
    wp_enqueue_style('datatables-buttons-css', 'https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css');

    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js', ['jquery'], null, true);
    wp_enqueue_script('datatables-buttons-js', 'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js', ['jquery'], null, true);
    wp_enqueue_script('datatables-jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', [], null, true);
    wp_enqueue_script('datatables-pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', [], null, true);
    wp_enqueue_script('datatables-vfs', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', [], null, true);
    wp_enqueue_script('datatables-buttons-html5', 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js', ['jquery'], null, true);
    wp_enqueue_script('datatables-buttons-print', 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js', ['jquery'], null, true);

    // DataTables Init
    wp_add_inline_script('datatables-js', "
        jQuery(document).ready(function($) {
            if ($('.oc-odds-table').length) {
                $('.oc-odds-table').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'csv', 'excel'
                    ]
                });
            }
        });
    ");
}
add_action('wp_enqueue_scripts', 'oc_enqueue_frontend_assets');

/**
 * Render the odds comparator table on the frontend
 */
function oc_render_odds_block($attributes) {
    $odds = OC_Scraper::fetch_odds(); // Get odds from API calling static method of aocscrappeclas
    ob_start(); // Start storing HTML output

    // Some simple styles for the table
    echo '<style>
        .aoc-odds-table {
            width: 1024px !important;
            max-width: 1024px !important;
        }
        .aoc-odds-table table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .aoc-odds-table th, .aoc-odds-table td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        .aoc-odds-table th {
            background: #f4f4f4;
        }
    </style>';

    echo '<div class="aoc-odds-table">';
    echo '<table class="oc-odds-table display stripe" ><thead><tr><th>Bookmaker</th><th>Market</th><th>Team</th><th>Odds</th><th>Link</th></tr></thead><tbody>';

    // Show each row if we have odds
    if (!empty($odds)) {
        foreach ($odds as $entry) {
            echo '<tr>';
            echo '<td>' . esc_html($entry['bookmaker']) . '</td>'; // Bookmaker name
            echo '<td>' . esc_html($entry['market']) . '</td>'; // Market name
            echo '<td>' . esc_html($entry['team']) . '</td>'; // Team name
            // Get user-selected format from settings (default = decimal)
                $format = get_option('aoc_odds_format', 'decimal');

                // Convert odds based on selected format
                $odds_value = $entry['odds']; // assume incoming odds are in decimal

                if ($format === 'fractional') {
                    $odds_value = OC_Odds_Converter::to_fractional((float) $odds_value);
                } elseif ($format === 'american') {
                    $odds_value = OC_Odds_Converter::to_american((float) $odds_value);
                } else {
                    $odds_value = round((float) $odds_value, 2);
                }

                echo '<td>' . esc_html($odds_value) . '</td>';


            // Show link if it's there, otherwise show a dash
            if (!empty($entry['link']) && $entry['link'] !== '#') {
                echo '<td><a href="' . esc_url($entry['link']) . '" target="_blank">Visit</a></td>';
            } else {
                echo '<td>–</td>';
            }

            echo '</tr>';
        }
    } else {
        // If no odds found, show this message
        echo '<tr><td colspan="5" style="text-align:center;color:red;">No matching odds found for selected filters.</td></tr>';
    }

    echo '</tbody></table></div>';

    return ob_get_clean(); // Send the HTML to show on the site
}

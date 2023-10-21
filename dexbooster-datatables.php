<?php

/**
 * Dex Booster Datatables
 *
 * @package     DexBoosterDatatables
 * @author      Henri Susanto
 * @copyright   2023 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Dex Booster Datatables
 * Plugin URI:  https://github.com/susantohenri/dexbooster-datatables
 * Description: Datatables plugin for dexbooster.io to speed up page load by serving large JSON through PHP backend <strong>sample usage:</strong> [dexbooster-datatables json-url="https://ffxkccymzr.a.pinggy.online/data_arbitrum"]
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri/
 * Text Domain: DexBoosterDatatables
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

add_shortcode('dexbooster-datatables', function ($attributes) {
    if (!isset($attributes['json-url'])) return '<strong>invalid short-code usage, use:</strong> [dexbooster-datatables json-url="https://ffxkccymzr.a.pinggy.online/data_arbitrum"]';

    wp_register_style('datatables', 'https://dexbooster.io/wp-content/plugins/ht-mega-for-elementor/assets/css/datatables.min.css?ver=2.3.3');
    wp_enqueue_style('datatables');

    wp_register_script('datatables', 'https://dexbooster.io/wp-content/plugins/ht-mega-for-elementor/assets/js/datatables.min.js?ver=2.3.3', ['jquery']);
    wp_enqueue_script('datatables');

    wp_enqueue_script('dexbooster-datatables', plugin_dir_url(__FILE__) . 'dexbooster-datatables.js?token=' . time(), null, null, true);
    wp_localize_script(
        'dexbooster-datatables',
        'dexbooster_datatables',
        array(
            'json_parser_url' => site_url("wp-json/dexbooster-datatables/v1/parse-json?&cache-breaker=" . time()),
        )
    );

    return "
        <table class='dexbooster-datatables' json-url='{$attributes['json-url']}'>
            <thead>
                <tr>
                    <th>PAIR</th>
                    <th>TIER</th>
                    <th>APR</th>
                    <th>PRICE</th>
                    <th>TVL</th>
                    <th>DEX</th>
                    <th>INFO</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    ";
});

add_action('rest_api_init', function () {
    register_rest_route('dexbooster-datatables/v1', '/parse-json', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $_POST['source']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $contents = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($contents, true);
            $json = array_map(function ($obj) {
                $obj = array_filter($obj, function ($value, $attr) {
                    return in_array($attr, ['Pair', 'Tier', 'apr_mensual', 'Price_USD', 'TVL 2', 'Dex_image', 'FDV']);
                }, ARRAY_FILTER_USE_BOTH);
                return array_values($obj);
            }, $json);

            $data = ['data' => $json];

            $columns = $_POST['columns'];
            $dir = $_POST['order'][0]['dir'];
            $col = $columns[$_POST['order'][0]['column']]['name'];

            $search = urldecode($_POST['search']['value']);
            $rows = [];
            foreach ($data['data'] as $row) {
                if (
                    (empty($search) || (!empty($search) && (stripos($row['key'], $search) !== false || stripos($row['id'], $search) !== false)))
                ) {
                    $rows[] = $row;
                }
            }

            uasort($rows, fn ($a, $b) => ($dir === 'asc') ? $a[$col] <=> $b[$col] : $b[$col] <=> $a[$col]);
            $data_slice = array_slice($rows, $_POST['start'], $_POST['length']);

            return [
                'draw' => intval($_POST['draw']),
                'recordsTotal' => count($data['data']),
                'recordsFiltered' => count($rows),
                'data' => $data_slice
            ];
        }
    ));
});

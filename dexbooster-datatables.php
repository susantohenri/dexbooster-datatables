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

// add_action('wp_ajax_get_wdtable', 'dexbooster_datatables_ajax', 1);
// add_action('wp_ajax_nopriv_get_wdtable', 'dexbooster_datatables_ajax', 1);
function dexbooster_datatables_ajax()
{
    $result = [
        'draw' => intval($_POST['draw']),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ];
    $columns = $_POST['columns'];
    $dir = $_POST['order'][0]['dir'];
    $col = $columns[$_POST['order'][0]['column']]['name'];
    $search = urldecode($_POST['search']['value']);
    $source = 'https://ffxkccymzr.a.pinggy.online/data_arbitrum';

	$wpdt_id = 49;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $contents = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($contents, true);
    $data = ['data' => $json];

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

    $data_slice = array_map(function ($obj) use ($wpdt_id) {
        $filtered = array_filter($obj, function ($value, $attr) {
            return in_array($attr, ['Pair', 'Tier', 'APY_24h', 'Price_USD', 'TVL 2', 'Dex_image']);
        }, ARRAY_FILTER_USE_BOTH);
        $obj['wdt_md_id_table'] = $wpdt_id;
        $s_obj = htmlentities(json_encode($obj));
        $detail_page = site_url('pool');

        return [
            $filtered['Pair'],
            $filtered['Tier'] . '%',
            $filtered['APY_24h'] . '%',
            '$' . $filtered['Price_USD'],
            '$' . $filtered['TVL 2'],
            "<img src='{$filtered['Dex_image']}'>",
            "
                <form class='wdt_md_form' method='post' target='_blank' action='https://dexbooster.io/pool/'>
                    <input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=\"{$s_obj}\">
                    <input class='master_detail_column_btn my-button' type='submit' value='🚀'>
                </form>
            "
        ];
    }, $data_slice);

    $result = [
        'draw' => intval($_POST['draw']),
        'recordsTotal' => count($data['data']),
        'recordsFiltered' => count($rows),
        'data' => $data_slice
    ];
	echo $result;
// 	wp_send_json($result);
    wp_die();
}
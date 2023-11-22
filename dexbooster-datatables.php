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
 * Description: Update current wpdatatable: set input data source type to "SQL query", check "Enable server-side processing", and SQL Query to "SELECT * FROM `arbitrum` WHERE 'https://henri.xsanisty.com/data_arbitrum.json' = 'https://henri.xsanisty.com/data_arbitrum.json'" (change URL with json source)
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri/
 * Text Domain: DexBoosterDatatables
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('DEX_BOOSTER_DATATABLES_CRON_INTERVAL_MINUTES', 10);

add_action('wp_ajax_get_wdtable', 'dexbooster_datatables_ajax', 1);
add_action('wp_ajax_nopriv_get_wdtable', 'dexbooster_datatables_ajax', 1);
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
    $wpdt_id = $_GET['table_id'];

    $json = dexbooster_datatables_get_uptodate_json_data($wpdt_id);
    $data = ['data' => $json['data']];

    $rows = [];
    foreach ($data['data'] as $row) {
        if (
            (empty($search) || (!empty($search) && (stripos($row['Pair'], $search) !== false)))
        ) {
            $rows[] = $row;
        }
    }

    uasort($rows, fn ($a, $b) => ($dir === 'asc') ? $a[$col] <=> $b[$col] : $b[$col] <=> $a[$col]);
    $data_slice = array_slice($rows, $_POST['start'], $_POST['length']);

    $header_positions = [];
    global $wpdb;
    foreach ($wpdb->get_results("SELECT orig_header, pos FROM {$wpdb->prefix}wpdatatables_columns WHERE table_id={$wpdt_id}") as $header) {
        $header_positions[$header->pos] = $header->orig_header;
    }

    foreach ($data_slice as $index => $value) {
        $reordereds = [];
        $address = '';
        foreach ($value as $key => $obj) {
            if ('Dex_image' == $key) $value[$key] = "<img src='{$obj}'>";
            else if ('Address' === $key) $address = $obj;
            else $value[$key] = strval($obj);
            foreach ($header_positions as $pos => $orig_header) {
                $reordereds[(int)$pos] = $value[$orig_header];
            }
        }

        $data_slice[$index] = $reordereds;
        $pool_url = site_url("pool?blockchain={$json['crypto']}&wpdtid={$wpdt_id}&address={$address}");
        $data_slice[$index][count($reordereds) - 1] = "<form class='wdt_md_form' method='post' target='_blank' action='{$pool_url}'><input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=''><input class='master_detail_column_btn my-button' type='submit' value='🚀'></form>";
    }

    $result = [
        'draw' => intval($_POST['draw']),
        'recordsTotal' => strval(count($data['data'])),
        'recordsFiltered' => strval(count($rows)),
        'data' => $data_slice
    ];

    echo json_encode($result);
    wp_die();
}

add_action('init', function () {
    if (isset($_GET['wpdtid']) && isset($_GET['address'])) {
        $params = [
            'crypto' => $_GET['crypto'],
            'wpdtid' => $_GET['wpdtid'],
            'address' => $_GET['address'],
        ];
        $json = dexbooster_datatables_get_uptodate_json_data($params['wpdtid']);
        $rows = array_values(array_filter($json['data'], function ($record) use ($params) {
            return $record['Address'] == $params['address'];
        }));
        $rows[0]['wdt_md_id_table'] = $params['wpdtid'];
        $rows[0]['Dex_image'] = "<img src='{$rows[0]['Dex_image']}'>";
        $rows = json_encode($rows[0]);
        $rows = addslashes($rows);
        $_POST['wdt_details_data'] = $rows;
    }
});

function dexbooster_datatables_get_uptodate_json_data($wpdt_id)
{
    global $wpdb;
    $source = $wpdb->get_var("SELECT SUBSTR(content, LOCATE('=', content)) FROM `{$wpdb->prefix}wpdatatables` WHERE id = {$wpdt_id}");
    $source = str_replace('=', '', $source);
    $source = str_replace("'", '', $source);
    $source = trim($source);

    $crypto_data = explode('/', $source);
    $crypto_data = end($crypto_data);

    $downloaded_json_file = plugin_dir_path(__FILE__) . "{$crypto_data}.json";
    $last_downloaded = round(abs(time() - filemtime($downloaded_json_file)) / 60, 2);
    if ($last_downloaded >= DEX_BOOSTER_DATATABLES_CRON_INTERVAL_MINUTES) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $contents = curl_exec($ch);
        curl_close($ch);
        file_put_contents($downloaded_json_file, $contents);
    }
    return [
        'crypto' => str_replace('data_', '', $crypto_data),
        'data' => json_decode(file_get_contents($downloaded_json_file), true)
    ];
}
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

	global $wpdb;
	
	$columns = $_POST['columns'];
    $dir = $_POST['order'][0]['dir'];
    $col = $columns[$_POST['order'][0]['column']]['name'];
    $search = urldecode($_POST['search']['value']);

	$wpdt_id = $_GET['table_id'];
	$source = $wpdb->get_var("SELECT SUBSTR(content, LOCATE('=', content)) FROM `{$wpdb->prefix}wpdatatables` WHERE id = {$wpdt_id}");
	$source = str_replace('=','', $source);
	$source = str_replace("'",'', $source);
	$source = trim($source);

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

	$header_positions = [];
	foreach ($wpdb->get_results("SELECT orig_header, pos FROM {$wpdb->prefix}wpdatatables_columns WHERE table_id={$wpdt_id}") as $header) {
		$header_positions[$header->pos] = $header->orig_header;
	}
	
	foreach ($data_slice as $index => $value) {
		$reordereds = [];
		foreach ($value as $key => $obj) {
			if ('Dex_image' == $key) $value[$key] = "<img src='{$obj}'>";
			else $value[$key] = strval($obj);
			foreach ($header_positions as $pos => $orig_header) {
	 			$reordereds[(int)$pos] = $value[$orig_header];				
			}
		}

		$data_slice[$index] = $reordereds;
		$data_slice[$index][count($reordereds)-1] = "<form class='wdt_md_form' method='post' target='_blank' action='https://dexbooster.io/pool/'><input class='wdt_md_hidden_data' type='hidden' name='wdt_details_data' value=''><input class='master_detail_column_btn my-button' type='submit' value='🚀'></form>";
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
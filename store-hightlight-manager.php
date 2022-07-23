<?php
/***
 *
 * Plugin Name: Store Hight Light Manager
 * Plugin URI:
 * Description: Chức năng quản lí sản phẩm thống kê số liệu, vv và vv ...
 * Author: Hiếu Small
 * Version: 0.0.1
 * Author URI: https://hieusmall.github.io
 *
 * @package  StoreHightLight
 * @version  1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Root Folder Path.
if (!defined('STORE_HIGHT_LIGHT_PLUGIN_DIR_URL')) {
    define('STORE_HIGHT_LIGHT_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
}

// Plugin Root Folder Path.
if (!defined('STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH')) {
    define('STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

if (!defined('STORE_HIGHT_LIGHT_GOOGLE_CREDENTIALS')) {
    define('STORE_HIGHT_LIGHT_GOOGLE_CREDENTIALS', STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'credentials.json');
}

if (!defined('STORE_HIGHT_LIGHT_PUBLIC_GOOGLE_CREDENTIALS')) {
    define('STORE_HIGHT_LIGHT_PUBLIC_GOOGLE_CREDENTIALS', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'credentials.json');
}

if (!defined('STORE_HIGHT_LIGHT_GOOGLE_ANALYSTIC_PROPERTIES')) {
    define('STORE_HIGHT_LIGHT_GOOGLE_ANALYSTIC_PROPERTIES', '321878431');
}

// Run this function when WPGraphQL is de-activated
register_deactivation_hook(__FILE__, 'store_hightlight_deactivation_callback');
register_activation_hook(__FILE__, 'store_hightlight_activation_callback');

// Bootstrap the plugin
if (!class_exists('StoreHightLight\StoreHL')) {
    require_once __DIR__ . '/src/StoreHL.php';
}


if (!function_exists('store_hightlight_init')) {
    /**
     * Function that instantiates the plugins main class
     *
     * @return object
     */
    function store_hightlight_init()
    {
        /**
         * Return an instance of the action
         */
        return StoreHightLight\StoreHL::instance();
    }
}
store_hightlight_init();

if (defined('WP_CLI') && WP_CLI) {
    require_once 'cli/wp-cli.php';
}

//use HightLightStore\StoreHLGA4;

if ($_GET['debug'] == 'vip') {

    $StoreHLGa4 = new StoreHightLight\StoreHLGa4();

    $dimension_filters = array(
        "andGroup" => array(
            [
                "filter" => array(
                    "field_name" => "eventName",
                    "in_list_filter" => ["page_view", "click_buy_product", "click_view_shop"]
                )
            ]
        )
    );

    $request_report_domain = $StoreHLGa4::instance()->RequestReportDataWithDomain();

    $response_domain_report = $StoreHLGa4::instance()->makeRunReport($request_report_domain);

    $json_report = json_decode($response_domain_report->serializeToJsonString());

    $data = $StoreHLGa4::instance()->makeReportPretty($response_domain_report);

    /*$dimensionHeaders = $json_report->dimensionHeaders;
    $metricHeaders = $json_report->metricHeaders;

    $rows = $json_report->rows;

    $data = array();

    foreach ($rows as $row) {
        $item = array();
        $dimensionValues = $row->dimensionValues;
        foreach ($dimensionValues as $dimensionValueIndex => $dimensionValue) {
            $itemName = $dimensionHeaders[$dimensionValueIndex]->name;
            $item[$itemName] = $dimensionValue->value;
        }

        array_push($data, (object) $item);
    }*/

    /*foreach ($response_domain_report->getRows() as $row) {
        $obj = array();
        $dimensionValues = $row->getDimensionValues();
        $metricValues = $row->metricValues();

        foreach ($dimensionValues as $dimensionValueIndex => $dimensionValue) {

        }
    }*/

    echo "<pre>";
    var_dump($data);
    echo "</pre>";

//    echo "done";
    die();

}
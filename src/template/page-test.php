<?php
/**
 * Template Name: Page Test Class Hight Light Store
 *
 * @package willgroup
 */

$store_hl_ga4 = new HightLightStore\StoreHLGA4();

$result = $store_hl_ga4::instance()->reportByProductSlug(
    array("slug" => 'ban-nha-pho-go-vap-duong-pham-van-chieu-phuong-9')
);
echo "<pre>";

//$data = array_map(function($obj) {
//    return $obj->rows;
//}, $result['data']);

var_dump($result);
echo "<pre/>";

die();
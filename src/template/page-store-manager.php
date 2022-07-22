<?php
/**
 * Template Name: Quản Lý Dữ Liệu Hệ thống
 *
 * @package willgroup
 */

$storeHL = new StoreHightLight\StoreHL();
$storeHLGA4 = new StoreHightLight\StoreHLGA4();

if( ! is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();

//
$dimension_hostName_key = "hostName";
$dimension_pagePath_key = "pagePath";
$dimension_eventName_key = "eventName";

//
$metric_eventCount_key = "eventCount";
$metric_activeUsers_key = "activeUsers";
$metric_screenPageViews_key = "screenPageViews";
$metric_averageSessionDuration_key = "averageSessionDuration";

//
$dimension_query_args = array(
    $dimension_hostName_key,
    $dimension_pagePath_key,
    $dimension_eventName_key
);
$metric_query_args = array(
    $metric_eventCount_key,
    $metric_activeUsers_key,
    $metric_screenPageViews_key,
    $metric_averageSessionDuration_key
);

//
$dimension_hostName_key_index = array_search($dimension_hostName_key, $dimension_query_args);
$dimension_pagePath_key_index = array_search($dimension_pagePath_key, $dimension_query_args);
$dimension_eventName_key_index = array_search($dimension_eventName_key, $dimension_query_args);

//
$metric_eventCount_key_index = array_search($metric_eventCount_key, $metric_query_args);
$metric_activeUsers_key_index = array_search($metric_activeUsers_key, $metric_query_args);
$metric_screenPageViews_key_index = array_search($metric_screenPageViews_key, $metric_query_args);
$metric_averageSessionDuration_key_index = array_search($metric_averageSessionDuration_key, $metric_query_args);

//
$baoCaoGA4 = $storeHLGA4::instance()->ThongKeSoLieuHeThong(
    array(
        "dimensions" => $dimension_query_args,
        "metrics" => $metric_query_args,
        "dimension_filters" => array(
            "and_group" => array()
        ),
    )
);
$baoCaoGA4_json_str = $baoCaoGA4->serializeToJsonString();
$baoCaoGA4_json = json_decode($baoCaoGA4_json_str);
$queryDanhSachSanPham = $storeHL::instance()->queryStoreProducts(array(
    "post_status" => "any",
    "posts_per_page" => -1
));

/**
 * @description Tìm số liệu thống kê theo đường dẫn của sản phẩm
 * @param $productSlug
 * @return null
 */

$reportByProductSlug = function(
    $productSlug
) use (
    &$baoCaoGA4,
    &$dimension_hostName_key_index,
    &$dimension_pagePath_key_index,
    &$dimension_eventName_key_index,
    &$metric_eventCount_key_index,
    &$metric_activeUsers_key_index,
    &$metric_screenPageViews_key_index,
    &$metric_averageSessionDuration_key_index
) {
    if (!$productSlug) {
        return null;
    }

    // Tìm trong báo cáo những số liệu của sản phẩm này
    $baocao_str = $baoCaoGA4->serializeToJsonString();
    $baocao_json = json_decode($baocao_str);
    $callback = function ($var) use (&$productSlug, &$dimension_pagePath_key_index) {
        $flag = str_contains($var->dimensionValues[$dimension_pagePath_key_index]->value, $productSlug);
        return $flag;
    };
    $data = array_filter($baocao_json->rows, $callback);

    // Chuyển đổi về json
    $data_args = array_values($data);
    if (count($data_args) == 0) {
        return null;
    }
    $result = array(
        "screenPageViews" => 0,
        "click_view_shop" => 0,
        "click_buy_product" => 0,
        "averageSessionDuration" => 0
    );

    foreach ($data_args as $key => $item) {
        $new_item = array();
        $dimensionValues = $item->dimensionValues;
        $metricValues = $item->metricValues;

        $hostName = $dimensionValues[$dimension_hostName_key_index]->value;
        $pagePath = $dimensionValues[$dimension_pagePath_key_index]->value;
        $eventName = $dimensionValues[$dimension_eventName_key_index]->value;

        $eventCount = $metricValues[$metric_eventCount_key_index]->value;
        $screenPageViews = $metricValues[$metric_screenPageViews_key_index]->value;
        $averageSessionDuration = $metricValues[$metric_averageSessionDuration_key_index]->value;

        // Tính tổng lượt xem cửa hàng
        if ($eventName == "click_view_shop") {
            $result["click_view_shop"] += $eventCount;
        }

        // Tính tổng lượt click mua hàng
        if ($eventName == "click_buy_product") {
            $result["click_buy_product"] += $eventCount;
        }

        $result['screenPageViews'] += $screenPageViews;
        $result["averageSessionDuration"] += $averageSessionDuration;
    }

    if ($result["averageSessionDuration"] > 0) {
        $result["averageSessionDuration"] = $result["averageSessionDuration"] / count($data_args);
    }

    return $result;
};

//echo "<pre>";
//print_r($baoCaoGA4_json_str);
//var_dump($reportByProductSlug("physiodermie-deep-cleansing-milk-sua-tay-trang-rua-mat-3-trong-1-2"));
//echo "</pre>";
//die();

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">

    <?php if(false) : ?>

    <div class="container-fluid">
        <h3 class="text-center">Report Total Google Analytic</h3>

        <pre>
            <?php print_r($baoCaoGA4_json_str) ?>
        </pre>

        <table id="users-table-analytics" class="store-hightlight-dataTable table table-striped display" style="width: 100%">
            <thead>
            <tr>
                <?php foreach ($baoCaoGA4_json->dimensionHeaders as $dimensionHeader) { ?>
                    <th>
                        <?php echo $dimensionHeader->name ?>
                    </th>
                <?php } ?>

                <?php foreach ($baoCaoGA4_json->metricHeaders as $metricHeader) { ?>
                    <th>
                        <?php echo $metricHeader->name ?>
                    </th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($baoCaoGA4_json->rows as $keyRP => $rowReport) {
                $stt = $keyRP + 1; ?>

                <tr>
                    <?php foreach ($rowReport->dimensionValues as $dimensionValue) { ?>
                        <td>
                            <?php echo $dimensionValue->value ?>
                        </td>
                    <?php } ?>
                    <?php foreach ($rowReport->metricValues as $metricValues) { ?>
                        <td>
                            <?php echo $metricValues->value ?>
                        </td>
                    <?php } ?>
                </tr>


                <!--<tr>
                    <td class="text-center">
                        <?php /*echo $stt */?>
                    </td>
                    <td>
                        <?php /*echo $product->post_title */?>
                    </td>
                    <td class="text-center">
                        <?php /*if( get_post_status( get_the_ID() ) == 'publish' ) : */?>
                            <i class="fas fa-check-circle h5 text-success mb-0" title="<?php /*_e( 'Đã duyệt', 'willgroup' ); */?>"></i>
                        <?php /*else : */?>
                            <i class="fas fa-ellipsis-h text-info border border-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 1.25rem; height: 1.25rem;" title="<?php /*_e( 'Chờ xét duyệt', 'willgroup' ); */?>"></i>
                        <?php /*endif; */?>
                    </td>
                    <td class="text-center">
                        <?php /*echo 100 */?>
                    </td>
                    <td class="text-center">
                        <?php /*echo 100 */?>
                    </td>
                    <td class="text-center">
                        <?php /*echo 100 */?>
                    </td>
                    <td class="text-center">
                        <?php /*echo 100 */?>
                    </td>
                </tr>-->
            <?php } ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>

    <div style="margin-top: 40px;margin-bottom: 40px;"></div>

    <?php if(true) : ?>

    <div class="container-fluid">
        <h3 class="text-center">Report Theo mã sản phẩm cho users</h3>

        <!--<div>
            <h5>Tổng trafic/lượt hiển thị</h5>
        </div>
        <div>
            <h5>Tổng mua hàng</h5>
        </div>
        <div>
            <h5>Tổng cửa hàng</h5>
        </div>
        <div>
            <h5>Thời gian xem trung bình</h5>
        </div>-->

        <table id="users-table-analytics" class="store-hightlight-dataTable table table-striped display" style="width: 100%">
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th style="width: 200px;">Tiêu đề</th>
                    <th class="text-center">Lượt hiển thị</th>
                    <th class="text-center">Click mua hàng</th>
                    <th class="text-center">Click cửa hàng</th>
                    <th class="text-center">Thời gian xem trung bình</th>
<!--                    <th>Dữ liệu analystic</th>-->
                    <th class="text-center">Tình trạng</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($queryDanhSachSanPham->posts as $keySP => $product) {
                $stt = $keySP + 1;
                $baoCaoGA4_theo_san_pham = $reportByProductSlug($product->post_name); ?>
                <tr>
                    <td class="text-center">
                        <?php echo $stt ?>
                    </td>
                    <td>
                        <?php echo $product->post_title ?>
                    </td>
                    <td class="text-center">
                        <?php echo $baoCaoGA4_theo_san_pham['screenPageViews'] . " lượt"  ?>
                    </td>
                    <td class="text-center">
                        <?php echo $baoCaoGA4_theo_san_pham['click_buy_product']. " lượt" ?>
                    </td>
                    <td class="text-center">
                        <?php echo $baoCaoGA4_theo_san_pham['click_view_shop']. " lượt" ?>
                    </td>
                    <td class="text-center">
                        <?php echo $baoCaoGA4_theo_san_pham['averageSessionDuration'] . " giây" ?>
                    </td>
                    <!--<td class="">
                        <?php /*//print_r($baoCaoGA4_theo_san_pham) */?>
                    </td>-->
                    <td class="text-center">
                        <?php if( get_post_status( $product->ID ) == 'publish' ) : ?>
                            <i class="fas fa-check-circle h5 text-success mb-0" title="<?php _e( 'Đã duyệt', 'willgroup' ); ?>"></i>
                        <?php else : ?>
                            <i class="fas fa-ellipsis-h text-info border border-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 1.25rem; height: 1.25rem;" title="<?php _e( 'Chờ xét duyệt', 'willgroup' ); ?>"></i>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 40px;margin-bottom: 40px;"></div>

    <?php endif; ?>

    <?php if(false) : ?>

    <div class="container-fluid">
        <h3>Report theo website</h3>
        <p>Liệt kê số liệu chi tiết mỗi web</p>
        <table id="websites-table-analytics" class="store-hightlight-dataTable table table-striped display" style="width: 100%">
            <thead>
            <tr>
                <th>STT</th>
                <th>Web</th>
                <th>Tổng traffic/lượt hiển thị</th>
                <th>Tổng mua hàng</th>
                <th>Tổng cửa hàng</th>
                <th>Thời gian xem trung bình</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <?php endif; ?>

    <?php if(false) : ?>

    <div style="margin-top: 40px;margin-bottom: 40px;"></div>

    <div class="container-fluid">
        <h3>Report theo toàn hệ thống</h3>
        <table id="users-table-analytics" class="store-hightlight-dataTable table table-striped display" style="width: 100%">
            <thead>
            <tr>
                <th>STT</th>
                <th>Tiêu đề</th>
                <th>Tổng traffic/lượt hiển thị</th>
                <th>Tổng mua hàng</th>
                <th>Tổng cửa hàng</th>
                <th>Thời gian xem trung bình</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <?php endif; ?>
</main>

<?php get_footer(); ?>

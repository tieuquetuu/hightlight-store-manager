<?php
/**
 * Template Name: Page Report
 *
 * @package willgroup
 */

$storeHL = new StoreHightLight\StoreHL();
$storeHLGA4 = new StoreHightLight\StoreHLGA4();

$request = $storeHLGA4::instance()->RequestReportSummaryData();
$report = $storeHLGA4::instance()->makeRunReport($request);
$pretty_report = $storeHLGA4::instance()->makeReportPretty($report);


$totalScreenPageViews = $storeHLGA4::instance()->totalScreenPageViewsFromReport($report);


if( ! is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();
$is_admin = in_array("administrator", $current_user->roles);

$ajaxArrayParams = array();

if (!$is_admin) {
    $ajaxArrayParams["author"] = $current_user->ID;
}

$str_params = http_build_query($ajaxArrayParams);

$ajax_source_url = get_rest_url() . "hightlight/v1/pageReportDataTable?" . $str_params;

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">
    <div class="container-fluid">
        <div class="row">
            <div class="col col-md-3">
                <span>Tổng lượt xem</span>
                <span><?php echo $totalScreenPageViews ?></span>
            </div>
            <div class="col col-md-3">
                <span>Lượt click cửa hàng</span>
                <span>1000</span>
            </div>
            <div class="col col-md-3">
                <span>Lượt click mua hàng</span>
                <span>1000</span>
            </div>
            <div class="col col-md-3">
                <span>Thời gian xem trung bình</span>
                <span>1000</span>
            </div>
        </div>

        <table
                id="products-table-analytics"
                class="<?php if($is_admin) : echo 'admin-view'; endif; ?> table table-striped display"
                data-ajax-source="<?php echo $ajax_source_url ?>"
                style="width: 100%">
            <thead>
            <tr>
                <th></th>
                <th>ID sản phẩm</th>
                <th style="width: 15%;">Tiêu đề</th>
                <th>Danh mục</th>
                <th class="text-center">Lượt hiển thị</th>
                <th class="text-center">Lượt click cửa hàng</th>
                <th class="text-center">Lượt click mua hàng</th>
                <th class="text-center">Thời gian xem trung bình</th>
                <th style="width: 5%;" class="text-center" style="width: 10%;">Trạng thái</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</main>



<?php get_footer() ?>
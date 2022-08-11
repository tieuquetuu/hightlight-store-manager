<?php
/**
 * Template Name: Page Report
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
$is_admin = in_array("administrator", $current_user->roles);

$ajaxArrayParams = array();
$queryArgs = array(
    "posts_per_page" => -1,
    "post_status" => array(
        "publish", "pending", "trash"
    )
);

if (!$is_admin) {
    $ajaxArrayParams["author"] = $current_user->ID;
    $queryArgs["author"] = $current_user->ID;
}

$queryProducts = $storeHL::instance()->queryStoreProducts($queryArgs);

$productSlugs = array();

foreach ($queryProducts->posts as $item) {
    if (strlen($item->post_name) <= 0) {
        continue;
    }
    array_push($productSlugs, $item->post_name);
}

$request = $storeHLGA4::instance()->RequestReportSummaryData(array(
    "productSlugs" => $productSlugs
));
$report = $storeHLGA4::instance()->makeRunReport($request);
$pretty_report = $storeHLGA4::instance()->makeReportPretty($report);

// Đếm tổng lượt xem & sự kiện các thứ
$totalScreenPageViews = $storeHLGA4::instance()->totalScreenPageViewsFromReport($report);
$totalClickBuyProduct = $storeHLGA4::instance()->totalClickBuyProductFromReport($report);
$totalClickViewShop = $storeHLGA4::instance()->totalClickViewShopFromReport($report);
$totalAverageSessionDuration = $storeHLGA4::instance()->totalAverageSessionDurationFromReport($report);

$str_params = http_build_query($ajaxArrayParams);
$ajax_source_url = get_rest_url() . "hightlight/v1/pageReportDataTable?" . $str_params;

get_header(); ?>
<style>
    .total-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .total-card-count {
        font-weight: bold;
        font-size: 24px;
    }

    .total-card-text {
        text-transform: capitalize;

    }
</style>
<main id="main" class="col-12 site-main" role="main">

    <div class="container-fluid">
        <div class="row mb-4">
            <div id="total-screen-page-views" class="col col-md-3 alert alert-success total-card">
                <span class="total-card-text">Tổng lượt xem</span>
                <span class="total-card-count"><?php echo $totalScreenPageViews ?></span>
            </div>
            <div id="total-click-view-shop" class="col col-md-3 alert alert-info total-card">
                <span class="total-card-text">Lượt click cửa hàng</span>
                <span class="total-card-count"><?php echo $totalClickViewShop ?></span>
            </div>
            <div id="total-click-buy-product" class="col col-md-3 alert alert-warning total-card">
                <span class="total-card-text">Lượt click mua hàng</span>
                <span class="total-card-count"><?php echo $totalClickBuyProduct ?></span>
            </div>
            <div id="total-average-session-duration" class="col col-md-3 alert alert-danger total-card">
                <span class="total-card-text">Thời gian xem trung bình</span>
                <span class="total-card-count"><?php echo $totalAverageSessionDuration ?> Giây</span>
            </div>
        </div>

        <div class="row">
            <div class="col col-md-3 mb-4">
                <label for="filter-category">Lọc theo thời gian</label>
                <input type="text" id="report-filter-daterange" name="daterange" />
            </div>

            <?php /*
            <div class="col col-md-12">
                <table class="table table-striped store-hightlight-dataTable display">
                    <thead>
                        <th></th>
                        <th>ID Tin</th>
                        <th>Tiêu đề</th>
                        <th>Đường dẫn</th>
                        <th>Danh mục</th>
                        <th>Lượt xem</th>
                        <th>Lượt click cửa hàng</th>
                        <th>Lượt click mua hàng</th>
                        <th>Thời gian xem trung bình</th>
                        <th>Tình trạng</th>
                    </thead>
                    <tbody>
                        <?php foreach ($queryProducts->posts as $prod) {
                            $prodId = $prod->ID;
                            $prodTitle = $prod->post_title;
                            $prodSlug = $prod->post_name;
                            $productCategory = array_map(function($cat) {
                                return $cat->name;
                            },get_the_terms($prodId, "re_cat"));
                            $prodStatusText = $prod->post_status; ?>
                            <tr>
                                <td></td>
                                <td><?php echo $prodId ?></td>
                                <td><?php echo $prodTitle ?></td>
                                <td><?php echo $prodSlug ?></td>
                                <td><?php echo implode(",", $productCategory) ?></td>
                                <td>1000</td>
                                <td>500</td>
                                <td>400</td>
                                <td>60 giây</td>
                                <td><?php echo $prodStatusText ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            */ ?>

            <div class="col col-md-12">
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

        </div>
    </div>

</main>



<?php get_footer() ?>
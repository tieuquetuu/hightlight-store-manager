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

/*$queryProducts = $storeHL::instance()->queryStoreProducts($queryArgs);*/

$report_by_product_slugs = array();
$productSlugs = array();

/*foreach ($queryProducts->get_posts() as $item) {
    if (strlen($item->post_name) <= 0) {
        continue;
    }
    array_push($productSlugs, $item->post_name);
}*/

$totalScreenPageViews = 0;
$totalClickBuyProduct = 0;
$totalClickViewShop = 0;
$totalAverageSessionDuration = 0;

if (count($productSlugs) > 0) {
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
}

$str_params = http_build_query($ajaxArrayParams);
$detail_product_by_domain_report_ajax_source_url = get_rest_url() . "hightlight/v1/reportDetailProductByDomainDataTable?" . $str_params;
$product_table_manager_ajax_source_url = get_rest_url() . "hightlight/v1/reportManageProductDataTable?" . $str_params;

get_header(); ?>
<style>
    .total-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .total-card-detail {
        padding: 10px;
    }

    .total-card-count {
        font-weight: bold;
        font-size: 24px;
    }

    .total-card-text {
        text-transform: capitalize;

    }

    .total-card-link {
        text-align: center;
        width: 100%;
        margin-top: auto;
        text-transform: capitalize;
        background: rgba(11, 11, 11, 0.20);
        color: white;
        padding-top: 5px;
        padding-bottom: 5px;
    }
</style>
<main id="main" class="col-12 site-main" role="main">

    <div class="container-fluid">
        <div class="row my-4">
            <div id="total-products" class="col col-md-3 alert alert-danger total-card">
                <div class="total-card-detail row w-100">
                    <div class="col-md-6">
                        <div class="total-card-count"><?php echo 0 ?></div>
                        <div class="total-card-text">Tổng số sản phẩm</div>
                    </div>
                </div>
                <a class="total-card-link" href="#">Xem chi tiết</a>
            </div>
            <div id="total-screen-page-views" class="col col-md-3 alert alert-success total-card">
                <div class="total-card-detail row w-100">
                    <div class="col-md-6">
                        <div class="total-card-count"><?php echo 0 ?> Lượt</div>
                        <div class="total-card-text">Lượng người xem</div>
                    </div>
                </div>
                <a class="total-card-link" href="#">Xem chi tiết</a>
            </div>
            <div id="total-click-view-shop" class="col col-md-3 alert alert-info total-card">
                <div class="total-card-detail row w-100">
                    <div class="col-md-6">
                        <div class="total-card-count"><?php echo 0 ?> Lượt</div>
                        <div class="total-card-text">Lượng liên hệ</div>
                    </div>
                </div>
                <a class="total-card-link" href="#">Xem chi tiết</a>
            </div>
            <div id="total-click-buy-product" class="col col-md-3 alert alert-warning total-card">
                <div class="total-card-detail row w-100">
                    <div class="col-md-6">
                        <div class="total-card-count"><?php echo 0 ?> Lượt</div>
                        <div class="total-card-text">Lượng xem cửa hàng</div>
                    </div>
                </div>
                <a class="total-card-link" href="#">Xem chi tiết</a>
            </div>
            <!--<div id="total-average-session-duration" class="col col-md-3 alert alert-danger total-card">
                <span class="total-card-text">Thời gian xem trung bình</span>
                <span class="total-card-count"><?php /*echo $totalAverageSessionDuration */?> Giây</span>
            </div>-->
        </div>

        <div class="row">
            <!--<div class="col col-md-3 mb-4">
                <label for="filter-category">Lọc theo thời gian</label>
                <input type="text" id="report-filter-daterange" name="daterange" />
            </div>-->

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

        </div>

        <div class="row">
            <div class="col col-md-7">
                <h4>Quản lý sản phẩm</h4>
                <table id="product-table-manager"
                       data-ajax-source="<?php echo $product_table_manager_ajax_source_url ?>"
                       class="table table-striped display"
                       style="width: 100%">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tiêu đề</th>
                            <th>Ngày hết hạn</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Lượt xem</th>
                            <th class="text-center">Xem liên hệ</th>
                            <th class="text-center">Xem cửa hàng</th>
                            <th class="text-center">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="col col-md-5">
                <h4>Số liệu chi tiết</h4>
                <table
                        id="detail-product-analytics"
                        class="<?php if($is_admin) : echo 'admin-view'; endif; ?> table table-striped display"
                        data-ajax-source="<?php echo $detail_product_by_domain_report_ajax_source_url ?>"
                        style="width: 100%">
                    <thead>
                        <!--<tr>
                            <th></th>
                            <th>ID sản phẩm</th>
                            <th style="width: 15%;">Tiêu đề</th>
                            <th>Danh mục</th>
                            <th class="text-center">Lượt hiển thị</th>
                            <th class="text-center">Lượt click cửa hàng</th>
                            <th class="text-center">Lượt click mua hàng</th>
                            <th class="text-center">Thời gian xem trung bình</th>
                            <th style="width: 5%;" class="text-center" style="width: 10%;">Trạng thái</th>
                        </tr>-->
                        <tr>
                            <th>Website</th>
                            <th>Lượt xem</th>
                            <th>Liên hệ</th>
                            <th>Cửa hàng</th>
                            <th>Thời gian</th>
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
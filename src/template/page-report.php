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

const HOSTNAME_DIMENSION_INDEX = 0;
const PAGE_TITLE_DIMENSION_INDEX = 1;
const PAGE_PATH_DIMENSION_INDEX = 2;
const EVENT_NAME_DIMENSION_INDEX = 3;

const ACTIVE_USERS_METRIC_INDEX = 0;
const EVENT_COUNT_METRIC_INDEX = 1;
const SCREEN_PAGE_VIEWS_METRIC_INDEX = 2;
const AVERAGE_SESSION_DURATION_METRIC_INDEX = 3;

// SET UP HEADER TO QUERY
$dimension_header_args = array(
    "hostName",
//        "pageTitle",
//        "pageLocation",
    "pagePath",
    "eventName",
);
$metric_header_args = array(
    "activeUsers", // Số lượng người dùng riêng biệt đã truy cập vào trang web hoặc ứng dụng của bạn.
    "eventCount", // Đếm số sự kiện
//        "eventValue", // Tổng của tham số sự kiện có tên 'giá trị'.
    'screenPageViews', // Số lượng màn hình ứng dụng hoặc trang web mà người dùng của bạn đã xem. Lượt xem lặp lại của một trang hoặc màn hình được tính. (sự kiện screen_view + page_view).
//        'userEngagementDuration', // Tổng lượng thời gian (tính bằng giây) trang web hoặc ứng dụng của bạn ở nền trước thiết bị của người dùng.
    "averageSessionDuration", // Thời lượng trung bình (tính bằng giây) trong các phiên của người dùng.
//        "engagedSessions", // Số phiên kéo dài hơn 10 giây hoặc có sự kiện chuyển đổi hoặc có 2 lượt xem màn hình trở lên.
//        "engagementRate", // Phần trăm phiên tương tác (Phiên tương tác chia cho Số phiên). Số liệu này được trả về dưới dạng phân số; ví dụ: 0,7239 có nghĩa là 72,39% phiên là phiên tương tác.
);

// GET SOME PRIMARY HEADER KEY INDEX vnb


$options = array(
    'dimensions' => $dimension_header_args,
    'metrics' => $metric_header_args,
);
$report = $storeHLGA4::instance()->ThongKeSoLieuHeThong($options);
$report_str = $report->serializeToJsonString();
$report_json = json_decode($report_str);
$rowsCount = $report_json->rowCount;
$rowsData = $report_json->rows;
$dimensionHeader = $report_json->dimensionHeaders;
$metricHeader = $report_json->metricHeaders;
$hostNames = array();

$store_products_args = array(
    'post_status'	 => 'publish',
    'posts_per_page' => -1
);
if (!in_array("administrator", $current_user->roles)) :
    $store_products_args['author'] = $current_user->ID;
endif;

$fetch_store_products = $storeHL::instance()->queryStoreProducts($store_products_args);
$system_store_products = $fetch_store_products->posts;

$product_analytics_by_slug = function(
    $product_slug
) use (
    &$rowsData,
    &$dimension_header_args,
    &$metric_header_args
) {
    $data = array(
        "luot_xem" => 0, // screenPageViews eventCount
        "luot_click_mua_hang" => 0, // click_buy_product eventCount
        "luot_click_cua_hang" => 0, // clivk_view_shop eventCount
        "thoi_gian_xem_trung_binh" => 0 // averageSessionDuration eventCount
    );

    if (!$product_slug) {
        return $data;
    }

    $hostName_dimension_keyword = "hostName";
    $eventName_dimension_keyword = "eventName";
    $pagePath_dimension_keyword = "pagePath";

    $eventCount_metric_keyword = "eventCount";
    $screenPageViews_metric_keyword = "screenPageViews";
    $averageSessionDuration_metric_keyword = "averageSessionDuration";

    // Get Index Of dimension
    $dimension_hostName_index = array_search($hostName_dimension_keyword, $dimension_header_args);
    $dimension_eventName_index = array_search($eventName_dimension_keyword,$dimension_header_args);
    $dimension_pagePath_index = array_search($pagePath_dimension_keyword,$dimension_header_args);

    // Get Index Of metric
    $metric_eventCount_index = array_search($eventCount_metric_keyword,$metric_header_args);
    $metric_screenPageViews_index = array_search($screenPageViews_metric_keyword,$metric_header_args);
    $metric_averageSessionDuration_index = array_search($averageSessionDuration_metric_keyword,$metric_header_args);

    $product_analytics_rows = array_filter($rowsData, function($row) use (&$product_slug, &$dimension_pagePath_index) {
        $flag = str_contains($row->dimensionValues[$dimension_pagePath_index]->value, $product_slug);
        return $flag;
    });

    $tong_luot_xem = 0;
    $tong_luot_click_mua_hang = 0;
    $tong_luot_click_cua_hang = 0;
    $tong_thoi_gian_trung_binh = 0;

    if(count($product_analytics_rows) > 0) {
        foreach ($product_analytics_rows as $product_analytics_row) {
            // Cộng tổng tất cả lượt xem
            $tong_luot_xem += (int) $product_analytics_row->metricValues[$metric_screenPageViews_index]->value;

            // cộng tổng lượt click button mua hàng
            if ($product_analytics_row->dimensionValues[$dimension_eventName_index]->value == "click_buy_product") {
                $tong_luot_click_cua_hang += (int) $product_analytics_row->metricValues[$metric_eventCount_index]->value;
            }

            // cộng tổng lượt click button cửa hàng
            if ($product_analytics_row->dimensionValues[$dimension_eventName_index]->value == "click_view_shop") {
                $tong_luot_click_mua_hang += (int) $product_analytics_row->metricValues[$metric_eventCount_index]->value;
            }

            // cộng tổng thời gian xem trang trung bình

            $tong_thoi_gian_trung_binh += floatval($product_analytics_row->metricValues[$metric_averageSessionDuration_index]->value);
        }

        $tong_thoi_gian_trung_binh = $tong_thoi_gian_trung_binh / count(array_keys($product_analytics_rows));
    }

    $data['luot_xem'] = $tong_luot_xem;
    $data['luot_click_cua_hang'] = $tong_luot_click_cua_hang;
    $data['luot_click_mua_hang'] = $tong_luot_click_mua_hang;
    $data['thoi_gian_xem_trung_binh'] = $tong_thoi_gian_trung_binh;

    return (object) $data;
};

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">

    <?php if($fetch_store_products->have_posts()) : ?>
        <div class="container-fluid">
            <table id="products-table" class="store-hightlight-dataTable table table-striped display" style="width: 100%">
                <thead>
                <tr>
                    <th>STT</th>
                    <!--                        <th>Hình ảnh</th>-->
                    <th style="width: 15%;">Tiêu đề</th>
                    <th style="width: 5%;" class="text-center" style="width: 10%;">Trạng thái</th>
                    <th>Đường dẫn</th>
                    <th>Lượt click mua hàng</th>
                    <th>Lượt click cửa hàng</th>
                    <th>Lượt hiển thị</th>
                    <th>Thời gian xem trung bình</th>
                    <!--                        <th>Dữ liệu</th>-->
                </tr>
                </thead>
                <tbody>
                <?php while( $fetch_store_products->have_posts() ) : $fetch_store_products->the_post();
                    $post = get_post();
                    $stt = (int) $fetch_store_products->current_post + 1;
                    $postTitle = get_the_title();
                    $postPermaLink = get_permalink();
                    $postSlug = $post->post_name;
                    $postStatus = get_post_status();
                    $product_analytics_data = $product_analytics_by_slug($postSlug); ?>
                    <tr>
                        <td>
                            <?php echo $stt ?>
                        </td>
                        <td>
                            <div style="display: flex;flex-direction: row;align-items: center;justify-content: center">
                                <a href="<?php the_permalink(); ?>" style="display: flex;margin-right: 10px">
                                    <img class="rounded" style="width: 3.125rem;" src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>"/>
                                </a>
                                <a href="<?php the_permalink(); ?>" style="display: flex;"><?php echo $postTitle ?></a>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if( get_post_status( get_the_ID() ) == 'publish' ) : ?>
                                <i class="fas fa-check-circle h5 text-success mb-0" title="<?php _e( 'Đã duyệt', 'willgroup' ); ?>"></i>
                            <?php else : ?>
                                <i class="fas fa-ellipsis-h text-info border border-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 1.25rem; height: 1.25rem;" title="<?php _e( 'Chờ xét duyệt', 'willgroup' ); ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $postSlug ?>
                        </td>
                        <td class="text-center">
                            <?php echo $product_analytics_data->luot_click_mua_hang ?> click
                        </td>
                        <td class="text-center">
                            <?php echo $product_analytics_data->luot_click_cua_hang ?> click
                        </td>
                        <td class="text-center">
                            <?php echo $product_analytics_data->luot_xem ?> lượt
                        </td>
                        <td class="text-center">
                            <?php echo $product_analytics_data->thoi_gian_xem_trung_binh ?> giây
                        </td>
                    </tr>
                <?php endwhile;wp_reset_postdata(); ?>

                <?php /* foreach ($system_store_products as $system_product_key => $system_product) {
                    $stt = (int) $system_product_key + 1;
                    $postTitle = $system_product->post_title;
                    $postSlug = $system_product->post_name;
                    $product_analytics_data = $product_analytics_by_slug($postSlug); ?>
                    <tr>
                        <td>
                            <?php echo $stt ?>
                        </td>
                        <td>
                            <?php echo $postTitle ?>
                        </td>
                        <td>
                            <?php echo "Đã duyệt" ?>
                        </td>
                        <td>
                            <?php echo $postSlug ?>
                        </td>
                        <td>
                            <?php echo $product_analytics_data->luot_click_mua_hang ?> click
                        </td>
                        <td>
                            <?php echo $product_analytics_data->luot_click_cua_hang ?> click
                        </td>
                        <td>
                            <?php echo $product_analytics_data->luot_xem ?> lượt
                        </td>
                        <td>
                            <?php echo $product_analytics_data->thoi_gian_xem_trung_binh ?> giây
                        </td>
                    </tr>
                <?php } */ ?>
                </tbody>
            </table>
            <!--<pre>
            <?php /*print_r($report_str) */?>
        </pre>-->
        </div>
    <?php else: ?>
        <div class="alert alert-danger"><?php _e( 'Bạn chưa có tin đăng nào.', 'willgroup' ); ?></div>
    <?php endif; ?>


<?php if(false) : ?>
    <div class="container-fluid">
        <table id="system-analystic-table" class="store-hightlight-dataTable display" style="width: 100%">
            <thead>
            <tr>
                <th> STT </th>
                <?php
                if (is_array($report_json->dimensionHeaders) && count($report_json->dimensionHeaders) > 0) :
                    foreach ($report_json->dimensionHeaders as $dimensionHeader) { ?>
                        <th>
                            <?php echo $storeHLGA4::instance()->DimensionExplain($dimensionHeader->name) ?>
                        </th>
                    <?php }
                endif; ?>

                <?php
                if (is_array($report_json->metricHeaders) && count($report_json->metricHeaders) > 0) :
                    foreach ($report_json->metricHeaders as $metricHeader) { ?>
                        <th>
                            <?php echo $storeHLGA4::instance()->MetricExplain($metricHeader->name) ?>
                        </th>
                    <?php }
                endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array($report_json->rows) && count($report_json->rows) > 0) :
                foreach ($report_json->rows as $rowKey => $row) {
                    $dimensionValues = $row->dimensionValues; // Array
                    $metricValues = $row->metricValues; // Array ?>
                    <tr>
                        <td><?php echo (int)$rowKey + 1 ?></td>

                        <?php if (is_array($dimensionValues) && count($dimensionValues) > 0) :
                            // Map The Dimension value
                            foreach ($dimensionValues as $dimensionValue) { ?>
                                <td>
                                    <?php echo $dimensionValue->value ?>
                                </td>
                            <?php }
                        endif; ?>

                        <?php // Map The Metric value
                        if (is_array($metricValues) && count($metricValues) > 0) :
                            foreach ($metricValues as $metricValue) { ?>
                                <td>
                                    <?php echo $metricValue->value ?>
                                </td>
                            <?php }
                        endif; ?>
                    </tr>
                <?php }
            endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</main>



<?php get_footer() ?>
<?php
/**
 * Template Name: Page Report Version 2
 *
 * @package willgroup
 */

$storeHL = new StoreHightLight\StoreHL();
$storeHLGA4 = new StoreHightLight\StoreHLGA4();

const HOSTNAME_DIMENSION_INDEX = 0;
const PAGE_TITLE_DIMENSION_INDEX = 1;
const PAGE_PATH_DIMENSION_INDEX = 2;
const EVENT_NAME_DIMENSION_INDEX = 3;

const ACTIVE_USERS_METRIC_INDEX = 0;
const EVENT_COUNT_METRIC_INDEX = 1;
const SCREEN_PAGE_VIEWS_METRIC_INDEX = 2;
const AVERAGE_SESSION_DURATION_METRIC_INDEX = 3;

$options = array(
    'dimensions' => array(
        "hostName",
//        "pageTitle",
//        "pageLocation",
        "pagePath",
        "eventName",
    ),
    'metrics' => array(
        "activeUsers", // Số lượng người dùng riêng biệt đã truy cập vào trang web hoặc ứng dụng của bạn.
        "eventCount", // Đếm số sự kiện
//        "eventValue", // Tổng của tham số sự kiện có tên 'giá trị'.
        'screenPageViews', // Số lượng màn hình ứng dụng hoặc trang web mà người dùng của bạn đã xem. Lượt xem lặp lại của một trang hoặc màn hình được tính. (sự kiện screen_view + page_view).
//        'userEngagementDuration', // Tổng lượng thời gian (tính bằng giây) trang web hoặc ứng dụng của bạn ở nền trước thiết bị của người dùng.
        "averageSessionDuration", // Thời lượng trung bình (tính bằng giây) trong các phiên của người dùng.
//        "engagedSessions", // Số phiên kéo dài hơn 10 giây hoặc có sự kiện chuyển đổi hoặc có 2 lượt xem màn hình trở lên.
//        "engagementRate", // Phần trăm phiên tương tác (Phiên tương tác chia cho Số phiên). Số liệu này được trả về dưới dạng phân số; ví dụ: 0,7239 có nghĩa là 72,39% phiên là phiên tương tác.
    ),
);
$report = $storeHLGA4::instance()->ThongKeSoLieuHeThong($options);
$report_str = $report->serializeToJsonString();
$report_json = json_decode($report_str);
$rowsCount = $report_json->rowCount;
$hostNames = array();

$fetch_store_products = $storeHL::instance()->queryStoreProducts(array(
    'post_status'	 => 'publish',
    'posts_per_page' => 100
));
$system_store_products = $fetch_store_products->posts;

//$user_store_products = $storeHL::instance()->queryStoreProducts(array(
//
//));

//echo "<pre>";
//print_r($report_str);
//echo "</pre>";
//die();
//
//echo "<pre>";
//print_r($report_json->rows[0]->dimensionValues[PAGE_PATH_DIMENSION_INDEX]->value);
//echo "</pre>";
//die();

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">

<?php if(FALSE) : ?>
    <div class="container-fluid">
        <table id="products-table" class="store-hightlight-dataTable display" style="width: 100%">
            <thead>
                <tr>
                    <th>STT</th>
<!--                    <th>Hình ảnh</th>-->
                    <th>Tiêu đề</th>
                    <th>Đường dẫn</th>
                    <th>Lượt click mua hàng</th>
                    <th>Lượt click cửa hàng</th>
                    <th>Thời gian xem trung bình</th>
                    <th>Lượt hiển thị</th>
<!--                    <th>Dữ liệu</th>-->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($system_store_products as $system_product_key => $system_product) {
                    $stt = (int) $system_product_key + 1;
                    $postTitle = $system_product->post_title;
                    $postSlug = $system_product->post_name;

                    $luotXem = $luotClickCuaHang = $luotClickMuaHang = $thoiGianTrungBinh = null;

                    $analysticProductDataObj = array_filter($report_json->rows, function($var) use (&$postSlug) {
//                        $flag = in_array($postSlug, $var->dimensionValues[PAGE_PATH_DIMENSION_INDEX]->value);
                        $flag = $var->dimensionValues[PAGE_PATH_DIMENSION_INDEX]->value == '/product/' . $postSlug . '/';
                        return $flag;
                    });

                    $analysticProductData = is_array($analysticProductDataObj) || is_object($analysticProductDataObj) ? array_values($analysticProductDataObj) : null;

                    ?>
                    <tr data-analystic="<?php echo json_encode($analysticProductData) ?>">
                        <td>
                            <?php echo $stt ?>
                        </td>
                        <td>
                            <?php echo $postTitle ?>
                        </td>
                        <td>
                            <?php echo $postSlug ?>
                        </td>
                        <td>
                            <?php echo 0 ?>
                        </td>
                        <td>
                            <?php echo 0 ?>
                        </td>
                        <td>
                            <?php echo 0 ?>
                        </td>
                        <td>
                            <?php echo 0 ?>
                        </td>
                        <!--<td>
                            <pre>
                                <?php /*print_r() */?>
                            </pre>
                        </td>-->
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if(TRUE) : ?>

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
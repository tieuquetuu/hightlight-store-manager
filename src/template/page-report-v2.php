<?php
/**
 * Template Name: Page Report Version 2
 *
 * @package willgroup
 */

use Google\Analytics\Data\V1beta\Filter\StringFilter;

$storeHLGA4 = new HightLightStore\StoreHLGA4();

$options = array(
    'dimensions' => array(
        "hostName",
        "pageTitle",
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

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">
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
                    $metricValues = $row->metricValues; // Array
                    ?>
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
</main>

<?php get_footer() ?>
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



get_header(); ?>

<main id="main" class="col-12 site-main" role="main">
    <div class="container-fluid">
        <table
            id="products-table-analytics"
            class="<?php if($is_admin) : echo 'admin-view'; endif; ?> table table-striped display"
            data-ajax-source="<?php echo get_rest_url() . "hightlight/v1/pageReportDataTable?" ?>"
            style="width: 100%">
            <thead>
            <tr>
                <th></th>
                <th>ID sản phẩm</th>
                <th style="width: 15%;">Tiêu đề</th>
                <th class="text-center">Lượt click mua hàng</th>
                <th class="text-center">Lượt click cửa hàng</th>
                <th class="text-center">Lượt hiển thị</th>
                <th class="text-center">Thời gian xem trung bình</th>
                <th style="width: 5%;" class="text-center" style="width: 10%;">Trạng thái</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- The Modal -->
    <div id="detail-analytics-modal" class="detail-analytics-modal">

        <!-- Modal content -->
        <div class="detail-analytics-modal-content">
            <span class="close">&times;</span>
            <p>Some text in the Modal..</p>
            <table class="detail-analyti cs-product-table display store-hightlight-dataTable" style="100%">
                <thead>
                    <tr>
                        <th>Tên miền</th>
                        <th>Đường dẫn</th>
                        <th>Lượt click mua hàng</th>
                        <th>Lượt click cửa hàng</th>
                        <th>Thời gian xem trung bình</th>
                        <th>Lượt hiển thị</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>

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
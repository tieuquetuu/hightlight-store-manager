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
    .table-heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .total-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-around;
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

    #product-preview-wrap {
        padding: 10px;
        display: none;
    }

    #product-preview-wrap.shown {
        display: block;
    }

    .product-preview__gallery {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
    }

    .product-preview__gallery-item {
        margin-right: 8px;
    }

    .product-preview__gallery-item:last-child {
        margin-right: 0;
    }

    .product-preview__gallery-item__image {
        max-width: 140px;
        min-width: 140px;
        max-height: 100px;
        min-height: 100px;
    }

    .product-preview__gallery-item__image img {
        display: block;
        width: 100%;
        height: auto;
    }

    .product-preview__gallery-item__text {
        align-items: center;
        text-align: center;
    }

    .product-preview__gallery-item__file-name {
        font-weight: bold;
        font-style: italic;
    }

    .product-preview__meta {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .product-preview__price {
        font-weight: bold;
        font-size: 16px;
    }

    .product-preview__footer {

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
            <div class="col col-md-7">
                <div id="product-table-manager-wrap" class="card p-3">
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

                <div id="product-preview-wrap" class="card p-3 my-4">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col col-md-12">
                                <h4 class="product-preview-id">Mã sản phẩm : <span class="" style="font-weight: bold">OR9842</span></h4>
                                <hr class="divider">
                            </div>

                            <div class="col col-md-12">
                                <h4 class="product-preview__title">Thuốc bổ mắt Anphalina</h4>
                                <div class="product-preview__meta my-2">
                                    <div class="product-preview__price">Giá <span>1.380.000 vnd</span></div>
                                    <div class="product-preview__updated">Cập nhật gần nhất: <span>15 tháng 2, 2022 11:30 sáng</span></div>
                                </div>
                                <hr class="divider">
                                <div class="product-preview__gallery">

                                    <!--<div class="product-preview__gallery-item">
                                        <div class="product-preview__gallery-item__image">
                                            <img src="https://via.placeholder.com/140x100" alt="">
                                        </div>
                                        <div class="product-preview__gallery-item__text">
                                            <span class="product-preview__gallery-item__file-name">Tên file.png</span>
                                        </div>
                                    </div>
                                    <div class="product-preview__gallery-item">
                                        <div class="product-preview__gallery-item__image">
                                            <img src="https://via.placeholder.com/140x100" alt="">
                                        </div>
                                        <div class="product-preview__gallery-item__text">
                                            <span class="product-preview__gallery-item__file-name">Tên file.png</span>
                                        </div>
                                    </div>
                                    <div class="product-preview__gallery-item">
                                        <div class="product-preview__gallery-item__image">
                                            <img src="https://via.placeholder.com/140x100" alt="">
                                        </div>
                                        <div class="product-preview__gallery-item__text">
                                            <span class="product-preview__gallery-item__file-name">Tên file.png</span>
                                        </div>
                                    </div>-->

                                </div>
                                <hr class="divider">
                                <div class="product-preview__content">
                                    <span class="mb-4">Mô tả sản phẩm :</span>
                                    <div class="product-preview__content-inner">
                                        <p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">Đây là loại yến được làm từ các tổ yến bị gãy, vỡ trong quá trình thu hoạch, phần sơ dừa còn lại khi làm loại rút lông xuất khẩu và các phần yến rút lông khác bị gãy trong quá trình vận chuyển. Để tiện cho khách hàng sử dụng, chúng tôi đã gom và ép lại thành từng tổ.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">Bạch yến tinh chế loại 2 cũng có công dụng như tổ yến. Tuy nhiên do bị vỡ nên không được đẹp mắt như tổ yến. Nếu bạn là người ăn yến thường xuyên thì đây thực sự là lựa chọn tốt cho kinh tế. Tuy nhiên không phù hợp cho biếu tặng.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;"><span style="font-size: 20px; color: #ff0000;" data-mce-style="font-size: 20px; color: #ff0000;"><span style="font-weight: bolder;" data-mce-style="font-weight: bolder;">Bộ Sản Phẩm bao gồm</span>:</span></p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;1 hộp tổ yến tinh chế;</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;1 Sách hướng dẫn sử dụng;</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;1 hộp đường phèn;</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧ 1 túi xách Yến Sào</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;"><span style="font-size: 20px; color: #ff0000;" data-mce-style="font-size: 20px; color: #ff0000;"><span style="font-weight: bolder;" data-mce-style="font-weight: bolder;">Cách chế biến yến sào Khánh Hòa</span></span></p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">Có thể chế biến theo nhiều phương pháp khác nhau nhưng để đảm bảo dưỡng chất và hương vị thì yến chưng được đánh giá cao nhất. Đối với các món ăn khác có sử dụng yến sào, người tiêu dùng cũng nên chưng yến trước rồi thêm vào sau khi món ăn đã hoàn thành. Dưới đây là phương pháp chưng yến khoa học và đảm bảo:</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;"><span style="color: #0000ff; font-size: 16px;" data-mce-style="color: #0000ff; font-size: 16px;"><span style="font-weight: bolder;" data-mce-style="font-weight: bolder;">➢&nbsp;Dùng nồi chưng yến chuyên dụng:</span></span></p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;Bước 1: Cân tổ yến và ngâm tổ yến trong nước tinh khiết sao cho ngập hết tổ trong 1 giờ.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;Bước 2: Khi tổ yến mềm và tách ra vớt ra rá dầy để ráo nước.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;Bước 3: Sử dụng nồi chưng yến cho nước ngập mức tiêu chuẩn và đặt bát đựng yến vào.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧&nbsp;Bước 4: Dùng nước tinh khiết đổ vào bát đựng yến sao cho ngập hết tổ yến.</p><p style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;" data-mce-style="margin-bottom: 1.3em; margin-top: 0px; color: #777777; font-family: Roboto, sans-serif; font-size: 17px;">✧ Bước 5: Chọn thời gian chưng từ 45 phút – 1 giờ. Chưng khoảng 40 phút nước bắt đầu sôi, đợi thêm 25 phút là yến chín. Trước khi lấy yến ra kh oảng 5 phút thì cho đường phèn vào nồi trộn đều</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col col-md-12">
                                <div class="product-preview__footer">
                                    <a href="#" class="btn btn-primary product-preview__edit-btn">
                                        <span><i class="fa fa-edit"></i></span>
                                        <span>Chỉnh sửa</span>
                                    </a>
                                    <a href="#" class="btn btn-danger product-preview__delete-btn">
                                        <span><i class="fa fa-trash"></i></span>
                                        <span>Xóa</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-md-5">
                <div id="detail-product-analytics-table" class="card p-3">
                    <div class="table-heading">
                        <h4 class="heading">Số liệu chi tiết</h4>
                        <span class="product-analytics-id">Tổng quan</span>
                    </div>
                    <table
                            id="detail-product-analytics"
                            class="<?php if($is_admin) : echo 'admin-view'; endif; ?> table table-striped display"
                            data-ajax-source="<?php echo $detail_product_by_domain_report_ajax_source_url ?>"
                            style="width: 100%">
                        <thead>
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
    </div>

</main>



<?php get_footer() ?>
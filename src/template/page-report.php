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

wp_head(); ?>

    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img class="lazy" src="<?php $image = get_field('logo', 'customizer'); echo $image['url']; ?>" alt="<?php bloginfo( 'name' ); ?>"/>
                <noscript><img src="<?php $image = get_field('logo', 'customizer'); echo $image['url']; ?>" alt="Chợ bất động sản"/></noscript>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url("/nguoi-dung/thong-ke-hoat-dong") ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Thống kê hoạt động</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url("/nguoi-dung/dang-sach-tin-dang") ?>">
                    <i class="fas fa-fw fa-list-alt"></i>
                    <span>Danh sách tin đăng</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url("/nguoi-dung/dang-tin") ?>">
                    <i class="fas fa-fw fa-pen-alt"></i>
                    <span>Đăng tin</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo site_url("/nguoi-dung/thong-ke-hoat-dong") ?>">
                    <i class="fas fa-fw fa-user-alt"></i>
                    <span>Tài khoản</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                 aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                               placeholder="Search for..." aria-label="Search"
                                               aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                 aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Counter - Messages -->
                                <span class="badge badge-danger badge-counter">7</span>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                 aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo STORE_HIGHT_LIGHT_PLUGIN_DIR_URL ?>theme/img/undraw_profile_1.svg"
                                             alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                            problem I've been having.</div>
                                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo STORE_HIGHT_LIGHT_PLUGIN_DIR_URL ?>theme/img/undraw_profile_2.svg"
                                             alt="...">
                                        <div class="status-indicator"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">I have the photos that you ordered last month, how
                                            would you like them sent to you?</div>
                                        <div class="small text-gray-500">Jae Chun · 1d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="<?php echo STORE_HIGHT_LIGHT_PLUGIN_DIR_URL ?>theme/img/undraw_profile_3.svg"
                                             alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Last month's report looks great, I am very happy with
                                            the progress so far, keep up the good work!</div>
                                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                             alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                            told me that people say this to all dogs, even if they aren't good...</div>
                                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $current_user->display_name ?></span>
                                <!--<img class="img-profile rounded-circle"
                                     src="img/undraw_profile.svg">-->

                                <span class="img-profile rounded-circle">
                                    <i class="fa fa-user" style="font-size: 1.3rem"></i>
                                </span>

                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                 aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo site_url("/nguoi-dung/tai-khoan") ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Tài khoản
                                </a>
                                <a class="dropdown-item" href="<?php echo site_url("/nguoi-dung/danh-sach-tin-dang") ?>">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Danh sách tin đăng
                                </a>
                                <a class="dropdown-item" href="<?php echo site_url("/nguoi-dung/dang-tin") ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Đăng tin
                                </a>
                                <a class="dropdown-item" href="<?php echo site_url("/nguoi-dung/thong-ke-hoat-dong") ?>">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Thống kê hoạt động
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo wp_logout_url( home_url() ); ?>">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Đăng xuất
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="row mt-4 mb-4">
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
                    </div>

                    <div class="row mb-4">
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
                                                <a href="#" class="btn btn-info product-preview__edit-btn">
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
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>

<?php wp_footer(); ?>
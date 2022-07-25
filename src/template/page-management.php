<?php
/**
 * Template Name: Quản Lý Dữ Liệu
 *
 * @package willgroup
 */

if( ! is_user_logged_in() ) {
    wp_redirect( site_url() . "/dang-nhap" );
    exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();

$users = get_users();
$categories = get_terms("re_cat");

$storeHL = new \StoreHightLight\StoreHL();

get_header(); ?>

<main id="main" class="col-12 site-main" role="main">
<!--    <h3 class="text-center">Report quyền hệ thống</h3>-->

    <!--<div class="container-fluid">
        <div class="row">
            <div class="col col-md-4">
                <label for="filter-domains">Lọc theo tên miền</label>
                <select name="filter-by-domain" id="filter-domains">
                    <option value="0">Tất cả</option>
                    <option value="store.dizital.vn">
                        store.dizital.vn
                    </option>
                    <option value="store.hightlight.net">
                        store.hightlight.net
                    </option>
                    <option value="giagoc.dizital.vn">
                        giagoc.dizital.vn
                    </option>
                    <option value="giagoc247.com">
                        giagoc247.com
                    </option>
                </select>
            </div>

            <div class="col col-md-4">
                <label for="filter-category">Lọc them danh mục</label>
                <select name="filter-by-category" id="filter-category">
                    <option value="0">Tất cả</option>
                    <?php /*foreach ($categories as $category) { */?>
                        <option value="<?php /*echo $category->term_id; */?>">
                            <?php /*echo $category->name; */?>
                        </option>
                    <?php /*} */?>
                </select>
            </div>

            <div class="col col-md-4">
                <label for="filter-category">Lọc theo người dùng</label>
                <select name="filter-by-user" id="filter-user">
                    <option value="0">Tất cả</option>
                    <?php /*foreach ($users as $user) { */?>
                        <option value="<?php /*echo $user->ID */?>">
                            <?php /*echo $user->display_name */?>
                        </option>
                    <?php /*} */?>
                </select>
            </div>

            <div class="col col-md-12">
                <table
                        id="system-report-table"
                        class="table responsive table-striped display"
                        data-ajax-source="<?php /*echo get_rest_url() . "hightlight/v1/reportSystemDataTable?" */?>"
                        style="width: 100%">
                    <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Loại sản phẩm</th>
                        <th>Tác giả</th>
                        <th class="text-center">Tổng lượt xem</th>
                        <th class="text-center">Lượt click cửa hàng</th>
                        <th class="text-center">Lượt click mua hàng</th>
                        <th class="text-center">Thời gian xem trung bình</th>
                        <th class="text-center">Tình trạng</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>-->

    <!--<div class="container-fluid">
        <div class="row">
            <div class="col col-md-4">
                <a class="button-manager" href="<?php /*echo $current_link . "quan-li-he-thong" */?>">
                    Hệ thống
                </a>
            </div>
            <div class="col col-md-4">
                <a class="button-manager" href="<?php /*echo $current_link . "quan-li-website" */?>">
                    Tên miền
                </a>
            </div>
            <div class="col col-md-4">
                <a class="button-manager" href="<?php /*echo $current_link . "quan-li-user" */?>">
                    User
                </a>
            </div>
        </div>
    </div>-->

    <?php echo $storeHL::instance()->ManagerDataNavigation() ?>

</main>

<?php get_footer(); ?>

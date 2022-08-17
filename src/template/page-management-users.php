<?php
/**
 * Template Name: Tổng Quan Số Liệu - Users
 *
 * @package willgroup
 */


if( ! is_user_logged_in() ) {
    wp_redirect( site_url() . "/dang-nhap" );
    exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();

$get_user_meta = get_user_meta($current_user->ID);
$level_manager_user_meta = unserialize($get_user_meta["level_manager_data"][0]);

$has_role = is_array($level_manager_user_meta) && ( in_array("level_1",$level_manager_user_meta) || in_array("level_3",$level_manager_user_meta) );

if (!$has_role) {
    wp_redirect( site_url() . "/tong-quan-so-lieu" );
    exit;
}

$users = get_users();
$categories = get_terms("re_cat");
$hostNames = \StoreHightLight\StoreHL::instance()->getHostNames();

$is_admin = in_array("administrator", $current_user->roles);
$ajaxArrayParams = array(
//    "author" => $current_user->ID,
);
$str_params = http_build_query($ajaxArrayParams);
$ajax_source_url = get_rest_url() . "hightlight/v1/reportUsersDataTable?" . $str_params;

get_header() ?>

<main id="main" class="col-12 site-main" role="main">

    <?php echo \StoreHightLight\StoreHL::instance()->ManagerDataNavigation() ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col col-md-3">
                <label for="user-filter-domains">Lọc theo tên miền</label>
                <select name="filter-by-domain" id="user-filter-domains">
                    <option value="0">Tất cả</option>
                    <?php foreach ($hostNames as $hostName) { ?>
                        <option value="<?php echo $hostName->hostName ?>"><?php echo $hostName->hostName ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col col-md-3">
                <label for="user-filter-category">Lọc them danh mục</label>
                <select name="filter-by-category" id="user-filter-category">
                    <option value="0">Tất cả</option>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo $category->term_id; ?>">
                            <?php echo $category->name; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col col-md-3">
                <label for="user-filter-user">Lọc theo người dùng</label>
                <select name="filter-by-user" id="user-filter-user">
                    <option value="0">Tất cả</option>
                    <?php foreach ($users as $user) { ?>
                        <option value="<?php echo $user->ID ?>">
                            <?php echo $user->display_name ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!--<div class="col col-md-3">
                <label for="filter-category">Lọc theo thời gian</label>
                <input type="text" id="user-filter-daterange" name="daterange" />
            </div>-->

            <div class="col col-md-12">

                <table id="users-report-table"
                       data-ajax-source="<?php echo $ajax_source_url ?>"
                       class="table responsive table-striped display"
                       style="width: 100%">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Tác giả</th>
                        <th class="text-center">Lượt xem</th>
                        <th class="text-center">Lượt click mua hàng</th>
                        <th class="text-center">Lượt click cửa hàng</th>
                        <th class="text-center">Thời gian xem trung bình</th>
                    </tr>
                    </thead>
                </table>

                <?php /*

                <table id="users-report-table"
                       class="table responsive table-striped display"
                       data-ajax-source="<?php echo $ajax_source_url ?>"
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

                */ ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer() ?>

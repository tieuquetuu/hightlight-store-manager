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

    <?php echo $storeHL::instance()->ManagerDataNavigation() ?>

</main>

<?php get_footer(); ?>

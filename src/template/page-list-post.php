<?php
/**
 * Template Name: User Danh sách tin đăng
 *
 * @package store-hightlight-manager
 */

if( ! is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();

$storeHL = new StoreHightLight\StoreHL();
$expiredSoonProducts = $storeHL::instance()->listProductsExpiredSoon(array("user_id"=> $current_user->ID));

if( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
    $id = $_GET['id'];

    $args = array(
        'post_type'      => 're',
        'posts_per_page' => 1,
        'p'				 => $id,
        'author'		 => $current_user->ID
    );
    $query = new WP_Query($args);
    // if ( ! $query->have_posts() ) {
    // 	wp_redirect( home_url() );
    // 	exit();
    // }

    $attachments = get_field('re_gallery', $id);
    foreach($attachments as $attachment) :
        wp_delete_attachment($attachment['ID'], true );
    endforeach;
    delete_post_thumbnail($id);
    wp_delete_post($id);
    wp_redirect( $current_link );
    exit();
}

wp_head();?>

<div id="wrapper">

    <?php echo $storeHL->AdminSideBar() ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <?php  echo $storeHL->AdminTopBar(); ?>

            <?php echo $storeHL::instance()->ManagerDataNavigation() ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <div class="row">
                    <main id="main" class="col-12 site-main" role="main">

                        <?php if ($expiredSoonProducts && count($expiredSoonProducts) > 0) { ?>
                            <div class="alert alert-warning">
                                <ul style="margin: 0">
                                    <?php foreach ($expiredSoonProducts as $expireItem) {
                                        $countDown = $storeHL::instance()->countDownDateProductExpired($expireItem); ?>

                                        <li>
                            <span>
                                <?php echo $expireItem->post_title
                                    . " sắp hết hạn, "; ?>
                            </span>
                                            <span style="color: red">
                                <?php
                                echo "còn:" . $countDown->d
                                    . " ngày " . $countDown->h
                                    . " giờ "
                                    . $countDown->m . " phút. Gia hạn ngay"
                                ?>
                            </span>


                                            <a href="#" style="color: #0B7FC7;font-weight: bold;">Link url</a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>

                        <?php
                        // echo do_shortcode('[hightlight_related_product_shortcode title="chính" cat="108" products="10" class="home-product"]');
                        ?>

                        <?php
                        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                        $args = array(
                            'post_type' 	 => 're',
                            'posts_per_page' => 20,
                            'post_status'	 => 'any',
                            'paged'	         => $paged,
                            'author' 		 => $current_user->ID
                        );
                        $query = new WP_Query( $args );
                        if( $query->have_posts() ) : ?>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="d-none d-sm-table-cell"><?php _e( 'STT', 'willgroup' ); ?></th>
                                    <th style="width: 5%;" class="d-none d-sm-table-cell"><?php _e( 'Hình ảnh', 'willgroup' ); ?></th>
                                    <th style="width: 20%;" class="d-none d-lg-table-cell"><?php _e( 'Mô tả ngắn', 'willgroup' ); ?></th>
                                    <th style="width: 15%;"><?php _e( 'Tiêu đề', 'willgroup' ); ?></th>
                                    <th class="d-none d-lg-table-cell" style="width: 10%;"><?php _e( 'Nhu cầu nhóm', 'willgroup' ); ?></th>
                                    <th style="width: 7%;" class="d-none d-lg-table-cell" style="width: 10%;"><?php _e( 'Loại sản phẩm', 'willgroup' ); ?></th>
                                    <th style="width: 15%;" class="d-none d-lg-table-cell" style="width: 10%;"><?php _e( 'Địa chỉ', 'willgroup' ); ?></th>
                                    <th style="width: 7%;" class="d-none d-sm-table-cell" style="width: 10%;"><?php _e( 'Số lượng<br> / Khối lượng', 'willgroup' ); ?></th>
                                    <th style="width: 5%;" class="d-none d-lg-table-cell" style="width: 10%;"><?php _e( 'Giá', 'willgroup' ); ?></th>
                                    <th style="width: 5%;" class="text-center" style="width: 10%;"><?php _e( 'Trạng thái', 'willgroup' ); ?></th>
                                    <th class="text-center" style="width: 10%;"><?php _e( 'Hành động', 'willgroup' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while( $query->have_posts() ) : $query->the_post(); ?>
                                    <tr>
                                        <td class="d-none d-sm-table-cell"><?php echo ((int)$query->current_post) + 1; ?></td>
                                        <td class="d-none d-sm-table-cell">
                                            <a href="<?php the_permalink(); ?>">
                                                <img class="rounded" style="width: 3.125rem;" src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>"/>
                                            </a>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php echo wp_trim_words( get_the_excerpt(), 20, '...' );?>
                                        </td>
                                        <td>
                                            <a href="<?php the_permalink(); ?>"><strong><?php the_title(); //echo $post->ID; ?></strong></a>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php echo get_term(get_field('re_demand'))->name; ?>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php echo get_term(get_field('re_cat'))->name; ?>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php
                                            // $provinces = willgroup_get_assoc_array_of_provinces();
                                            // echo $provinces[get_field('re_province')];
                                            echo get_the_address($post->ID);
                                            ?>
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            <?php the_field('re_area'); ?>
                                            <!-- m<sup>2</sup> -->
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php the_price(); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if( get_post_status( get_the_ID() ) == 'publish' ) : ?>
                                                <i class="fas fa-check-circle h5 text-success mb-0" title="<?php _e( 'Đã duyệt', 'willgroup' ); ?>"></i>
                                            <?php else : ?>
                                                <i class="fas fa-ellipsis-h text-info border border-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 1.25rem; height: 1.25rem;" title="<?php _e( 'Chờ xét duyệt', 'willgroup' ); ?>"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="h5 text-warning mx-1 mb-0" href="<?php echo home_url('nguoi-dung/dang-tin'); ?>?action=edit&id=<?php the_ID(); ?>" title="<?php _e( 'Sửa', 'willgroup' ); ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="h5 text-danger mx-1 mb-0" href="<?php echo $current_link; ?>?action=delete&id=<?php the_ID(); ?>" title="<?php _e( 'Xóa', 'willgroup' ); ?>">
                                                <i class="fas fa-times-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; wp_reset_postdata(); ?>
                                </tbody>
                            </table>
                            <?php willgroup_pagination( $query->max_num_pages ); ?>
                        <?php else : ?>
                            <div class="alert alert-danger"><?php _e( 'Bạn chưa có tin đăng nào.', 'willgroup' ); ?></div>
                        <?php endif; ?>
                    </main>
                </div>

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

</div>

<?php wp_footer(); ?>

<?php
/**
 * Template Name: Thống kê Số Liệu Backup
 *
 * @package willgroup
 */

use HightLightStore\StoreHLGA4;

if( ! is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}
$current_user = wp_get_current_user();
$current_link = get_the_permalink();

get_header(); 
$domain = isset( $_GET['domain'] ) ? $_GET['domain']:0;
$size = isset( $_GET['size'] ) ? $_GET['size']:10;
$domains = array(
"0"=>"Lọc theo tên miền",
"1"=>"Tên miền 1",
"2"=>"Tên miền 2",
"3"=>"Tên miền 3",
"4"=>"Tên miền 4",
"5"=>"Tên miền 5",
"6"=>"Tên miền 6",
"7"=>"Tên miền 7"
);
$sizes = array(
"10"=>"10",
"25"=>"25",
"50"=>"50",
"100"=>"100",
"200"=>"200" 
);

?>

<div class="select_report_filter" style="float:right">
	<?php if (in_array("administrator", $current_user->roles)) { ?>
	<select name="domain" id="domain">
	<?php 
	foreach ( $domains as $key => $value ) : ?>
								<option value="<?php echo $key; ?>" <?php echo $domain == $key ? 'selected' : ''; ?>>
									<?php echo $value; ?>    
								</option>
	<?php endforeach; ?>
	
	</select>
	<?php } ?>
	<div  class="report-filter-date">
		<label for="start-date">Ngày bắt đầu:</label>

		<input type="date" id="start" name="trip-start"
		   value="2022-07-13"
		   max="2022-07-13">
	</div>
	
	<div class="report-filter-date">
		<label for="end-date">Ngày kết thúc:</label>

		<input type="date" id="start" name="trip-end"
		   value="2022-07-13"
		   max="2022-07-13">
	</div>
</div>

<main id="main" class="col-12 site-main" role="main">

    <?php
			// echo do_shortcode('[hightlight_related_product_shortcode title="chính" cat="108" products="10" class="home-product"]');
		?>
	
    <?php
		
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$args = array(
			'post_type' 	 => 're',
			'posts_per_page' => $size,
			//'posts_per_page' => $domain,
			'post_status'	 => 'any',
			'paged'	         => $paged
		);
		if (!in_array("administrator", $current_user->roles)) $args += [ "author" => $current_user->ID ];
		$query = new WP_Query( $args ); 
		if( $query->have_posts() ) : ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th style="width: 5%;" class="d-sm-table-cell"><?php _e( 'STT', 'willgroup' ); ?></th>
                <th style="width: 5%;" class="d-none d-sm-table-cell"><?php _e( 'Hình ảnh', 'willgroup' ); ?></th>
                <th style="width: 25%;" class="d-none d-lg-table-cell"><?php _e( 'Mô tả ngắn', 'willgroup' ); ?></th>
                <th style="width: 25%;"><?php _e( 'Tiêu đề', 'willgroup' ); ?></th>
                <th style="width: 10%;" class=" d-lg-table-cell" ><?php _e( 'Lượt click mua hàng', 'willgroup' ); ?></th>
                <th style="width: 10%;" class=" d-lg-table-cell" >
                    <?php _e( 'Lượt click cửa hàng', 'willgroup' ); ?></th>
                <th style="width: 10%;" class=" d-lg-table-cell" >
                    <?php _e( 'Thời gian xem trung bình của sản phẩm', 'willgroup' ); ?></th>
                <th style="width: 10%;" class=" d-sm-table-cell" >
                    <?php _e( 'lượt hiển thị sản phẩm', 'willgroup' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php

            while( $query->have_posts() ) : $query->the_post();
                global $post;
                    $report = HightLightStore\StoreHLGA4::instance()->reportByProductSlug(array("slug" => $post->post_name));
                    $report_data = $report['data']; ?>

            <tr>
                <td class=" d-sm-table-cell"><?php echo ((int)$query->current_post) + 1; ?></td>
                <td class="d-none d-sm-table-cell">
                    <a href="<?php the_permalink(); ?>">
                        <img class="rounded" style="width: 3.125rem;"
                            src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>" />
                    </a>
                </td>
                <td class="d-none d-lg-table-cell">
                    <?php echo wp_trim_words( get_the_excerpt(), 20, '...' );?>
                </td>
                <td>
                    <a href="<?php the_permalink(); ?>"><strong><?php the_title(); //echo $post->ID; ?></strong></a>
                </td>
                <td class=" d-lg-table-cell">

                    <?php echo $report_data->click_buy_product ?>
<!--                    1000-->
                </td>
                <td class=" d-lg-table-cell">
                    <?php echo $report_data->click_view_shop ?>
<!--                    1000-->
                </td>
                <td class=" d-lg-table-cell">
                    1000
                </td>
                <td class=" d-sm-table-cell">
                    <?php echo $report_data->page_view ?>
<!--                    1000-->
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata(); ?>
        </tbody>
    </table>
    <?php willgroup_pagination( $query->max_num_pages ); ?>
	
	<div class="select_report_filter" style="float: right;">
	Số lượng hiển thị
	<select name="page-size" id="size">
	<?php 
	foreach ( $sizes as $key => $value ) : ?>
								<option value="<?php echo $key; ?>" <?php echo $size == $key ? 'selected' : ''; ?>>
									<?php echo $value; ?>    
								</option>
	<?php endforeach; ?>
	
	</select>

</div>
	
    <?php else : ?>
    <div class="alert alert-danger"><?php _e( 'Bạn chưa có tin đăng nào.', 'willgroup' ); ?></div>
    <?php endif; ?>
</main>

<?php
get_footer();
?>


<script type="text/javascript">
	function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
		return "";
	};
    
	$(document).ready(function(){
		$('.select_report_filter select').change(function(){
			window.location.href = "/nguoi-dung/thong-ke-hoat-dong/?domain="+$( ".select_report_filter select#domain option:selected" ).val() + "&size="+$( ".select_report_filter select#size option:selected" ).val();
			
		})
	});

	
</script>
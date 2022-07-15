<?php
/**
 * Template Name: Quản Lý Dữ Liệu Sản Phẩm
 *
 * @package willgroup
 */

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
//current_page_item
//aria-current="page"
$get_user_meta = get_user_meta($current_user->ID);
$roles = array(
0=>"Cấp 1 (Thể hiện cấp bậc dành cho user khách)",
1=>"Cấp 2 (Thể hiện cấp bậc theo từng website)",
2=>"Cấp 3 (Có thể xem toàn bộ hệ thống)"
);
$url = "/quan-ly-du-lieu-san-pham/";
$day_before =3;
$time_zone_7=7*60*60;
$timestamp_location = strtotime(gmdate("Ymd"))+$time_zone_7;
if(!isset($_GET['role'])){
	foreach($roles as $key => $value){
		echo $value;
		if (str_contains($get_user_meta["role_management_data"][0], $value)){			
			wp_redirect( $url."?role=".$key );
			exit;
		}
	}
}
?>

<nav class="user-nav" style="width:100%"><ul id="menu-dieu-huong-nguoi-dung" class="menu">
<li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item <?php if (!str_contains($get_user_meta["role_management_data"][0], $roles[0])) echo "management-data-menu-disabled"; if($_GET['role']==0) echo " current_page_item";?>"><a href="<?php echo $url; ?>?role=0"><i class="fas fa-user-alt"></i>Cấp 1: Dành cho user khách</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page <?php if (!str_contains($get_user_meta["role_management_data"][0], $roles[1])) echo "management-data-menu-disabled";if($_GET['role']==1) echo " current_page_item";?>"><a href="<?php echo $url; ?>?role=1"><i class="fas fa-user-plus"></i>Cấp 2: Theo từng website</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page <?php if (!str_contains($get_user_meta["role_management_data"][0], $roles[2])) echo "management-data-menu-disabled";if($_GET['role']==2) echo " current_page_item";?>"><a href="<?php echo $url; ?>?role=2"><i class="fa fa-user-friends"></i>Cấp 3: Toàn bộ hệ thống</a></li>
</ul></nav>
<?php 
		if (str_contains($get_user_meta["role_management_data"][0], $roles[0]) && $_GET['role']==0) { 
			$args_notify = array(
				'post_type' 	 => 're',
				'post_status'	 => 'publish',
				'posts_per_page' => -1,
				"author" => $current_user->ID
			);
			
			$query_notify = new WP_Query( $args_notify ); 
			if( $query_notify->have_posts() ){
				?>
				<div class="page-management-data-notice-error">
					<a href="#" class="icon-close-container"><i class="fas fa-window-close"></i></a>
					<p><strong>Những dịch vụ sau đây của bạn sắp hết hạn:</strong></p>				
				<?php
				while( $query_notify->have_posts() ) : $query_notify->the_post();
					if(!empty(get_post_meta($query_notify->post->ID)["end_day"][0]) && $timestamp_location >=strtotime(get_post_meta($query_notify->post->ID)["end_day"][0])- $day_before*86400 && $timestamp_location<=strtotime(get_post_meta($query_notify->post->ID)["end_day"][0]))
					{
						?>												
							<p> - Sản phẩm <a href="<?php the_permalink(); ?>"><?php the_title();?></a> còn <?php echo gmdate('d',strtotime(get_post_meta($query_notify->post->ID)["end_day"][0])-$timestamp_location); ?> ngày nữa cần gia hạn</p>
							
						
						<?php
					}
				endwhile; 
				?>
				</div>
				<?php
				wp_reset_postdata();
			}
		}
?>

<div class="select_report_filter" style="float:right">
	<?php 
	if ((str_contains($get_user_meta["role_management_data"][0], $roles[1]))||(str_contains($get_user_meta["role_management_data"][0], $roles[2])) && ($_GET['role']==1 || $_GET['role']==2) ) { ?>
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
		   value="<?php echo gmdate('Y-m-d',$timestamp_location); ?>"
		   max="<?php echo gmdate('Y-m-d',$timestamp_location); ?>">
	</div>
	
	<div class="report-filter-date">
		<label for="end-date">Ngày kết thúc:</label>

		<input type="date" id="start" name="trip-end"
		   value="<?php echo gmdate('Y-m-d',$timestamp_location); ?>"
		   max="<?php echo gmdate('Y-m-d',$timestamp_location); ?>">
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
            <?php while( $query->have_posts() ) : $query->the_post(); 
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
                </td>
                <td class=" d-lg-table-cell">
                    <?php echo $report_data->click_view_shop ?>
                </td>
                <td class=" d-lg-table-cell">
                    1000
                </td>
                <td class=" d-sm-table-cell">
                    <?php echo $report_data->page_view ?>
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
		$('.page-management-data-notice-error .icon-close-container').click(function(e){
			e.currentTarget.parentElement.style.display = "none";
			
		})
	});

	
</script>
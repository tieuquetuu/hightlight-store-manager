<?php
/**
 * Template Name: Login (Quản lý dữ liệu)
 *
 * @package willgroup
 */
if(is_user_logged_in()) {
//	wp_redirect( "/quan-ly-du-lieu-san-pham/du-lieu-he-thong");
	wp_redirect( home_url(), 301 );
    exit;
}
get_header();
if( ! is_user_logged_in() ) : ?>
<div class="login-container">
	<div class="login-body">
		<div class="row row-form">
			<div class="col-12 col-sm-12">
				<form class="form-login-store-hightlight-manager" action="" method="POST">
					<p class="h6 mb-3"><?php _e( 'Đăng nhập', 'willgroup' ); ?></p>
					<div class="form-group">
						<input class="form-control" type="text" name="email" placeholder="<?php _e('Email', 'willgroup'); ?>"/>
					</div>
					<div class="form-group">
						<input class="form-control" type="password" name="password" placeholder="<?php _e('Mật khẩu', 'willgroup'); ?>"/>
					</div>
					<div class="form-group">
						<button class="btn btn-primary btn-block" type="submit"><?php _e('Đăng nhập', 'willgroup'); ?></button>
					</div>
					<input type="hidden" name="action" value="willgroup_login"/>
				</form>
				
				
			</div>
			
		</div>
	</div>
</div>
<?php endif; ?>

</div><!-- .row -->
</div><!-- .site-main-->
</div><!-- .container fluid-->
</div><!-- .site-content -->
<?php wp_footer(); ?>
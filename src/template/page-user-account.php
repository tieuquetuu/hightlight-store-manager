<?php
/**
 * Template Name: User Tài khoản
 *
 * @package store-hightlight-manager
 */
$storeHL = new StoreHightLight\StoreHL();
if( ! is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

$current_user = wp_get_current_user();
if( isset( $_COOKIE['error'] ) ) {
    $error = $_COOKIE['error'];
    unset( $_COOKIE['error'] );
    setcookie( 'error', null, -1, '/' );
}
if( isset( $_COOKIE['success'] ) ) {
    $success = $_COOKIE['success'];
    unset( $_COOKIE['success'] );
    setcookie( 'success', null, -1, '/' );
}

if( isset( $_POST['action'] ) ) {
    $display_name = $_POST['display_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if ( $display_name == '' ) {
        $error = __( 'Bạn chưa nhập họ tên.', 'willgroup' ) . '<br>';
    }
    if ( $email == '' ) {
        $error .= __( 'Bạn chưa nhập email.', 'willgroup' ) . '<br>';
    }
    if ( ! is_email( $email ) ) {
        $error .= __( 'Email của bạn không đúng.', 'willgroup' ) . '<br>';
    }
    if ( email_exists( $email ) && $email != $current_user->user_email ) {
        $error .= __( 'Email đã tồn tại.', 'willgroup' ) . '<br>';
    }
    $regex = "/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i";
    if ( $phone == '' ) {
        $error .= __( 'Bạn chưa nhập số điện thoại.', 'willgroup' ) . '<br>';
    }
    if ( ! is_numeric ( $phone ) ) {
        $error .= __( 'Số điện thoại chỉ bao gồm những số.', 'willgroup' ) . '<br>';
    }
    if ( strlen( $phone ) < 10 ) {
        $error .= __( 'Số điện thoại phải có nhiều hơn 9 số.', 'willgroup' ) . '<br>';
    }

    if( $error == '' ) {
        $userdata = array(
            'ID' 	       => $current_user->ID,
            'display_name' => $_POST['display_name'],
            'user_email'   => $email,
        );
        $user_id = wp_update_user( $userdata );
        update_user_meta( $user_id, 'user_phone', $phone );
        $success = __( 'Cập nhật thành công.', 'willgroup' ) . '<br>';
        setcookie( 'success', $success, time() + 3600, '/');
    } else {
        setcookie( 'error', $error, time() + 3600, '/');
    }
    wp_redirect( get_the_permalink() );
    exit;
}
wp_head(); ?>

<div id="wrapper">

    <?php echo $storeHL->AdminSideBar() ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <?php echo $storeHL->AdminTopBar(); ?>

            <?php echo $storeHL::instance()->ManagerDataNavigation() ?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <div class="row row-small">
                    <div class="col-12 col-md-6 offset-md-3">
                        <?php if( isset( $error ) && $error != '' ) : ?>
                            <div class="alert alert-danger fade show">
                                <button type="button" class="close" data-dismiss="alert">
                                    <span><i class="ion-android-close"></i></span>
                                </button>
                                <?php echo $error; ?>
                            </div>
                        <?php elseif( isset( $success ) && $success != '' ) : ?>
                            <div class="alert alert-success fade show">
                                <button type="button" class="close" data-dismiss="alert">
                                    <span><i class="ion-android-close"></i></span>
                                </button>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        <form class="form-account" method="POST" action="">
                            <div class="form-group">
                                <label><?php _e( 'Họ tên', 'willgroup' ); ?> <span class="required">*</span></label>
                                <input class="form-control" type="text" name="display_name" value="<?php echo $current_user->display_name; ?>"/>
                            </div>
                            <div class="form-group">
                                <label><?php _e( 'Email', 'willgroup' ); ?> <span class="required">*</span></label>
                                <input class="form-control" type="text" name="email" value="<?php echo $current_user->user_email; ?>"/>
                            </div>
                            <div class="form-group">
                                <label><?php _e( 'Số điện thoại', 'willgroup' ); ?> <span class="required">*</span></label>
                                <input class="form-control" type="text" name="phone" value="<?php echo get_field( 'user_phone', 'user_' . $current_user->ID ); ?>"/>
                            </div>
                            <div class="form-group text-right">
                                <button class="btn btn-primary" type="submit"><?php _e( 'Cập nhật', 'willgroup' ); ?></button>
                            </div>
                            <input type="hidden" name="action" value="update"/>
                        </form>
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

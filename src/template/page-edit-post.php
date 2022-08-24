<?php
/**
 * Template Name: User Đăng Tin
 *
 * @package store-hightlight-manager
 */
$storeHL = new StoreHightLight\StoreHL();

if( ! is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

$current_user = wp_get_current_user();
$re_unit_price = 0;

if( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
    $id = $_GET['id'];

    // $args = array(
    // 	'post_type'      => 're',
    // 	'posts_per_page' => 1,
    // 	'p'				 => $id,
    // 	'author'		 => $current_user->ID
    // );
    // $query = new WP_Query($args);
    // if ( ! $query->have_posts() ) {
    // 	wp_redirect( home_url() );
    // 	exit();
    // }

    $_POST['title'] = get_the_title( $id );
    $_POST['demand'] = get_field( 're_demand', $id );
    $_POST['category'] = get_field( 're_cat', $id );
    $_POST['province'] = get_field( 're_province', $id );
    $_POST['district'] = get_field( 're_district', $id );
    $_POST['ward'] = get_field( 're_ward', $id );
    $_POST['address'] = get_field( 're_address', $id );
    $_POST['area'] = get_field( 're_area', $id );
    $_POST['price'] = get_field( 're_price', $id );
    $_POST['unit_price'] = get_field( 're_unit_price', $id, false );
    $_POST['building_orientation'] = get_field( 're_bo', $id );
    $_POST['front'] = get_field( 're_front', $id );
    $_POST['row'] = get_field( 're_row', $id );
    $_POST['floor'] = get_field( 're_floor', $id );
    $_POST['bedroom'] = get_field( 're_bedroom', $id );
    $_POST['toilet'] = get_field( 're_toilet', $id );
    $_POST['video'] = get_field( 're_video', $id, false );
    $_POST['desc'] = apply_filters( 'the_content', get_post_field( 'post_content', $id ) );

    if( get_field( 're_gallery', $id ) ) {
        foreach( get_field( 're_gallery', $id ) as $value ) {
            $gallery[] = !empty($value['ID']) ? $value['ID'] : $value;
        }
    }

    if( get_field( 're_360', $id ) ) {
        foreach( get_field( 're_360', $id ) as $value ) {
            $image_360[] = $value['ID'];
        }
    }


}

if( isset( $_POST['action'] ) && $_POST['action'] == 'post' ) {
    $title = $_POST['title'];
    $demand = $_POST['demand'];
    $category = $_POST['category'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $ward = $_POST['ward'];
    $address = $_POST['address'];
    $area = $_POST['area'];
    $price = $_POST['price'];
    $unit_price = $_POST['unit_price'];
    $building_orientation = $_POST['building_orientation'];
    $front = $_POST['front'];
    $row = $_POST['row'];
    $floor = $_POST['floor'];
    $bedroom = $_POST['bedroom'];
    $toilet = $_POST['toilet'];
    $video = $_POST['video'];
    $desc = $_POST['desc'];

    if( isset( $_POST['gallery'] ) ) {
        $gallery = $_POST['gallery'];
    }


    if ( $title == '' ) {
        $error = __( 'Bạn chưa nhập tiêu đề.', 'willgroup' ) . '<br>';
    } elseif (strlen($title) < 60 || strlen($title) > 80) {
        $error = __( 'Tiêu đề phải từ 60 - 80 ký tự.', 'willgroup' ) . '<br>';
    }
    if ( $demand == 0 ) {
        $error .= __( 'Bạn chưa chọn nhu cầu.', 'willgroup' ) . '<br>';
    }
    if ( $category == 0 ) {
        $error .= __( 'Bạn chưa chọn loại nhà đất.', 'willgroup' ) . '<br>';
    }
    if ( $province == 0 ) {
        $error .= __( 'Bạn chưa chọn tỉnh/thành.', 'willgroup' ) . '<br>';
    }
    if ( $district == 0 ) {
        $error .= __( 'Bạn chưa chọn quận/huyện.', 'willgroup' ) . '<br>';
    }
    if ( $address == '' ) {
        $error .= __( 'Bạn chưa nhập địa chỉ.', 'willgroup' ) . '<br>';
    }
    if ( $area == '' ) {
        $error .= __( 'Bạn chưa nhập diện tích.', 'willgroup' ) . '<br>';
    }
    if ( $price == '' ) {
        $error .= __( 'Bạn chưa nhập giá.', 'willgroup' ) . '<br>';
    }
    if ( $price != '' && ! is_numeric ( $price ) ) {
        $error .= __( 'Giá chỉ bao gồm những số.', 'willgroup' ) . '<br>';
    }
    if ( $unit_price == 0 ) {
        $error .= __( 'Bạn chưa chọn đơn giá.', 'willgroup' ) . '<br>';
    }
    if ( $desc == '' ) {
        $error .= __( 'Bạn chưa nhập thông tin mô tả.', 'willgroup' ) . '<br>';
    }

    if( $error == '' ) {
        $args = array(
            'post_type'     => 're',
            'post_status'   => 'pending',
            'post_author'	=> $current_user->ID,
            'post_title'    => $title,
            'post_content'  => $desc,
            'meta_input'    => array(
                're_demand'     => $demand,
                're_cat'        => $category,
                're_province'   => $province,
                're_district'   => $district,
                're_ward'   	=> $ward,
                're_address'    => $address,
                're_area'       => $area,
                're_price'	    => $price,
                're_unit_price' => $unit_price,
                're_bo'			=> $building_orientation,
                're_front'		=> $front,
                're_row'		=> $row,
                're_floor'		=> $floor,
                're_bedroom'    => $bedroom,
                're_toilet'     => $toilet,
                're_video'		=> $video,
                're_gallery'    => $gallery,

                're_views'		=> 0
            )
        );
        // var_dump($args);die;
        if( isset( $_POST['id'] ) ) {
            $args['ID'] = $_POST['id'];
            $post_id = wp_update_post( $args );
        } else {
            $post_id = wp_insert_post( $args );
        }
        set_post_thumbnail( $post_id, $gallery[0] );
        wp_set_post_terms( $post_id, array( $demand, $category ), 're_cat' );
        wp_redirect( home_url() . '/nguoi-dung/danh-sach-tin-dang' );
        exit;
    }
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
                   <div id="primary" class="col-12 content-area">
                       <main id="main" class="site-main" role="main">
                           <?php if( isset( $error ) && $error != '' ) : ?>
                               <div class="alert alert-danger"><?php echo $error; ?></div>
                           <?php endif; ?>
                           <form class="form-post" method="POST" action="<?php echo home_url(); ?>/nguoi-dung/dang-tin/">
                               <input type="hidden" name="action" value="post"/>
                               <div class="row">
                                   <div class="col-12 col-lg-6 form-group">
                                       <label><?php _e( 'Tiêu đề', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <input class="form-control" type="text" name="title" value="<?php echo isset( $_POST['title'] ) ? $_POST['title'] : ''; ?>"/>
                                   </div>
                                   <div class="col-6 col-md-3 form-group">
                                       <label><?php _e( 'Chọn Nhu Cầu Nhóm', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="demand">
                                           <option value="0"><?php _e( 'Chọn nhu cầu', 'willgroup' ); ?></option>
                                           <?php
                                           $demands = get_terms( array( 'taxonomy' => 're_cat', 'hide_empty' => false, 'parent' => 0 ) );
                                           foreach ( $demands as $value ) : ?>
                                               <option value="<?php echo $value->term_id; ?>" <?php echo isset( $_POST['demand'] ) && $_POST['demand'] == $value->term_id ? 'selected' : ''; ?>>
                                                   <?php echo $value->name; ?>
                                               </option>
                                           <?php endforeach; ?>
                                       </select>
                                   </div>
                                   <div class="col-6 col-md-3 form-group">
                                       <label><?php _e( 'Loại Sản Phẩm/Dịch Vụ', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="category">
                                           <option value="0"><?php _e( 'Chọn Sản Phẩm/Dịch Vụ', 'willgroup' ); ?></option>
                                           <?php
                                           if( isset( $_POST['demand'] ) && ! empty( $_POST['demand'] ) ) :
                                               $cats = get_terms( array( 'taxonomy' => 're_cat', 'hide_empty' => false, 'parent' => $_POST['demand'] ) );
                                               foreach ( $cats as $value ) : ?>
                                                   <option value="<?php echo $value->term_id; ?>" <?php echo isset( $_POST['category'] ) && $_POST['category'] == $value->term_id ? 'selected' : ''; ?>>
                                                       <?php echo $value->name; ?>
                                                   </option>
                                               <?php
                                               endforeach;
                                           endif; ?>
                                       </select>
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Tỉnh/thành', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="province">
                                           <option>Chọn tỉnh / thành</option>
                                           <?php
                                           $provinces = willgroup_get_assoc_array_of_provinces();
                                           foreach ( $provinces as $key => $value ) : ?>
                                               <option value="<?php echo $key; ?>" <?php echo isset( $_POST['province'] ) && $_POST['province'] == $key ? 'selected' : ''; ?>>
                                                   <?php echo $value; ?>
                                               </option>
                                           <?php endforeach; ?>

                                       </select>
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Quận/huyện', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="district">

                                           <?php
                                           if( isset( $_POST['province'] ) && ! empty( $_POST['province'] ) ) :
                                               $districts = willgroup_get_assoc_array_of_districts($_POST['province']);
                                               foreach ( $districts as $key => $value ) : ?>
                                                   <option value="<?php echo $key; ?>" <?php echo isset( $_POST['district'] ) && $_POST['district'] == $key ? 'selected' : ''; ?>>
                                                       <?php echo $value; ?>
                                                   </option>
                                               <?php
                                               endforeach;
                                           else : ?>
                                               <option value=""><?php _e('Chọn quận/huyện', 'willgroup'); ?></option>
                                           <?php endif; ?>

                                       </select>
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Phường/xã', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="ward">

                                           <?php
                                           if( isset( $_POST['district'] ) && ! empty( $_POST['district'] ) ) :
                                               $wards = willgroup_get_assoc_array_of_wards($_POST['district']);
                                               foreach ( $wards as $key => $value ) : ?>
                                                   <option value="<?php echo $key; ?>" <?php echo isset( $_POST['ward'] ) && $_POST['ward'] == $key ? 'selected' : ''; ?>>
                                                       <?php echo $value; ?>
                                                   </option>
                                               <?php
                                               endforeach;
                                           else : ?>
                                               <option value=""><?php _e('Chọn phường/xã', 'willgroup'); ?></option>
                                           <?php endif; ?>

                                       </select>
                                   </div>
                                   <div class="col-12 col-lg-6 form-group">
                                       <label><?php _e( 'Địa chỉ', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <input class="form-control" type="text" name="address" value="<?php echo isset( $_POST['address'] ) ? $_POST['address'] : ''; ?>" placeholder="Số 88/4, đường Võ Văn Kiệt" />
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Khối lượng', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <div class="input-group">
                                           <input class="form-control" type="text" name="area" value="<?php echo isset( $_POST['area'] ) ? $_POST['area'] : ''; ?>" />

                                       </div>
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Giá', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <input class="form-control" type="text" name="price" value="<?php echo isset( $_POST['price'] ) ? $_POST['price'] : ''; ?>"/>
                                   </div>
                                   <div class="col-6 col-md-3 col-lg-2 form-group">
                                       <label><?php _e( 'Đơn giá', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <select class="form-control custom-select" name="unit_price">
                                           <option value="0"><?php _e( 'Chọn đơn giá', 'willgroup' ); ?></option>
                                           <option value="1" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 1 ? 'selected' : ''; ?>><?php _e( 'VNĐ', 'willgroup' ); ?></option>
                                           <option value="2" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 2 ? 'selected' : ''; ?>><?php _e( 'Người', 'willgroup' ); ?></option>
                                           <option value="3" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 3 ? 'selected' : ''; ?>><?php _e( 'Giờ', 'willgroup' ); ?></option>
                                           <option value="4" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 4 ? 'selected' : ''; ?>><?php _e( 'Tháng', 'willgroup' ); ?></option>
                                           <option value="5" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 5 ? 'selected' : ''; ?>><?php _e( 'Kg', 'willgroup' ); ?></option>
                                           <option value="6" <?php echo isset( $_POST['unit_price'] ) && $_POST['unit_price'] == 6 ? 'selected' : ''; ?>>Thỏa thuận</option>
                                       </select>

                                   </div>
                                   <div class="col-12 col-md-6 col-lg-3 form-group">
                                       <label><?php _e( 'Xuất Xứ', 'willgroup' ); ?></label>
                                       <div class="input-group">
                                           <input class="form-control" type="text" name="floor" value="<?php echo isset( $_POST['floor'] ) ? $_POST['floor'] : ''; ?>"/>
                                       </div>
                                   </div>
                                   <div class="col-12 col-md-6 col-lg-3 form-group">
                                       <label><?php _e( 'Link Cửa Hàng', 'willgroup' ); ?></label>
                                       <input class="form-control" type="text" name="front" value="<?php echo isset( $_POST['front'] ) ? $_POST['front'] : ''; ?>"/>
                                   </div>
                                   <div class="col-12 col-lg-6">
                                       <div class="form-group">
                                           <label><?php _e( 'Hình ảnh', 'willgroup' ); ?></label>
                                           <div class="bg-light p-4 form-upload">

                                               <div class="row images">
                                                   <?php
                                                   if ( ! empty( $gallery ) ) :
                                                       foreach ( $gallery as $value ) : ?>
                                                           <div class="col-2 image">
                                                               <p class="position-relative">
                                                                   <?php echo wp_get_attachment_image( (int)$value, 'thumbnail' ); ?>
                                                                   <input type="hidden" name="gallery[]" value="<?php echo $value; ?>"/>
                                                                   <a class="text-danger position-absolute" style="z-index: 10; top: 0.25rem; right: 0.5rem;" href="javascript:void()" title="<?php _e( 'Xóa', 'willgroup' ); ?>" data-toggle="remove-image" data-attachment-id="<?php echo $value; ?>">
                                                                       <i class="fas fa-times-circle"></i>
                                                                   </a>
                                                               </p>
                                                           </div>
                                                       <?php
                                                       endforeach;
                                                   endif; ?>
                                               </div>

                                               <input class="sr-only" type="file" name="images[]" id="gallery" multiple/>
                                               <p class="text-center">
                                                   <label class="btn btn-secondary mb-0" for="gallery">
                                                       <i class="fas fa-upload h5 mb-0 mr-2"></i><span><?php _e( 'Upload...', 'willgroup' ); ?></span>
                                                   </label>
                                               </p>
                                               <p class="text-center mb-0">
                                                   <?php _e( 'Không upload quá 6 hình ảnh. Mỗi hình không quá 1Mb và bề ngang không quá 1092px', 'willgroup' ); ?><br>
                                               </p>
                                           </div>
                                       </div>


                                   </div>
                                   <div class="col-12 col-lg-6 form-group">
                                       <label><?php _e( 'Thông tin mô tả', 'willgroup' ); ?> <span class="required">*</span></label>
                                       <?php
                                       $content = '';
                                       if ( isset( $_POST['desc'] ) ) {
                                           $content = $_POST['desc'];
                                       }
                                       $settings = array(
                                           'wpautop' => false,
                                           'media_buttons' => false,
                                           'teeny' => true,
                                           'quicktags' => false,
                                           'editor_height' => 250,
                                       );
                                       wp_editor( $content, 'desc', $settings ); ?>
                                   </div>
                                   <div class="col-12 form-group text-right">
                                       <button class="btn btn-primary" type="submit"><?php _e( 'Đăng tin', 'willgroup' ); ?></button>
                                   </div>
                               </div>
                               <?php if( isset( $_GET['id'] ) ) : ?>
                                   <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"/>
                               <?php endif; ?>
                           </form>
                       </main>
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

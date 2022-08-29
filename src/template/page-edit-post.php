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

$form_action = isset( $_GET['action'] ) && $_GET['action'] == "edit" ? "edit" : "post";
$post_id = $_GET['id'];
$is_edit = $_GET['action'] == 'edit' && isset( $post_id ) && (int)$post_id > 0 ? true : false;

$error = '';

$product = $post_id ? get_post($post_id) : null;

if( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
    $id = $_GET['id'];

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
    $_POST['help_note'] = get_field( 're_help_note', $id );
    $_POST['note'] = get_field( 're_note', $id );
    $_POST['start_day'] = get_field( 'start_day', $id );

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
    $start_day = $_POST['start_day'];
    $help_note = $_POST['help_note'];
    $note = $_POST['note'];

    if( isset( $_POST['gallery'] ) ) {
        $gallery = $_POST['gallery'];
    }

    if ( $title == '' ) {
        $error = __( 'Bạn chưa nhập tiêu đề.', 'willgroup' ) . '<br>';
    } elseif (strlen($title) < 60) {
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
                're_views'		=> 0,
                're_help_note'  => $help_note,
                're_note'       => $note,
                'start_day'     => $start_day
            )
        );

        // Tạo bài viết mới
        if (!$_POST['id']) {
            $post_id = wp_insert_post( $args );
        }

        // Cập nhật bài viết
        else {
            $args['ID'] = $_POST['id'];
            $post_id = wp_update_post( $args );
        }

        set_post_thumbnail( $post_id, $gallery[0] );
        wp_set_post_terms( $post_id, array( $demand, $category ), 're_cat' );
        wp_redirect( site_url('/nguoi-dung/danh-sach-tin-dang') );
        exit;

        /*if( $is_edit ) {
            $args['ID'] = $post_id;
            $post_id = wp_update_post( $args );
        } else {
            $post_id = wp_insert_post( $args );
        }
        set_post_thumbnail( $post_id, $gallery[0] );
        wp_set_post_terms( $post_id, array( $demand, $category ), 're_cat' );
        wp_redirect( site_url('/nguoi-dung/danh-sach-tin-dang') );
        exit;*/
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

            <?php // echo $storeHL::instance()->ManagerDataNavigation() ?>

            <!-- Begin Page Content -->
            <form class="form-post" method="POST" action="<?php echo site_url('/nguoi-dung/dang-tin/'); ?>">
                <input type="hidden" name="action" value="post"/>
                <?php if( $post_id ) : ?>
                    <input type="hidden" name="id" value="<?php echo $post_id; ?>"/>
                <?php endif; ?>
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12 content-area">
                            <h1 class="h3 mb-0 text-gray-800">Đăng sản phẩm</h1>
                            <p><small>7 sản phẩm đã đăng</small></p>
                        </div>
                        <div class="col col-md-3 col-xs-12">
                            <div class="w-100">
                                <a href="<?php echo site_url("/danh-sach-tin-dang") ?>" class="btn btn-info w-100">Quản lý sản phẩm</a>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-xs-12 col-md-12">
                            <?php if( isset( $error ) && $error != '' ) : ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="card shadow my-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Cài đặt quảng cáo</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="formControlStartDayInput">Ngày bắt đầu</label>
                                        <input type="date"
                                               name="start_day"
                                               class="form-control"
                                               value="<?php echo $_POST["start_day"]; ?>"
                                               id="formControlStartDayInput"
                                               placeholder="dd/mm/yy">
                                    </div>
                                    <div class="form-group">
                                        <label for="FormControlDayRangeSelect">Thời gian đăng</label>
                                        <select name="day_range" class="form-control" id="FormControlDayRangeSelect">
                                            <option value="1-month">30 Ngày</option>
                                            <option value="2-month">2 Tháng</option>
                                            <option value="3-month">3 Tháng</option>
                                            <option value="4-month">4 Tháng</option>
                                            <option value="5-month">5 Tháng</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><?php _e( 'Chọn Nhu Cầu Nhóm', 'willgroup' ); ?> <span class="required">*</span></label>
                                        <select class="form-control custom-select" name="demand">
                                            <option value="0"><?php _e( 'Chọn nhu cầu', 'willgroup' ); ?></option>
                                            <?php
                                            $demands = get_terms( array( 'taxonomy' => 're_cat', 'hide_empty' => false, 'parent' => 0 ) );
                                            foreach ( $demands as $value ) : ?>
                                                <option value="<?php echo $value->term_id; ?>" <?php echo !empty($_POST['demand']) && $_POST['demand'] == $value->term_id ? 'selected' : ''; ?>>
                                                    <?php echo $value->name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
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
                                    <div class="form-group">
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
                                    <div class="form-group">
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
                                    <div class="form-group">
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
                                    <div class="form-group">
                                        <label for="formControlAddressInput">Địa chỉ bán <span class="required">*</span></label>
                                        <input class="form-control"
                                               type="text"
                                               name="address"
                                               id="formControlAddressInput"
                                               value="<?php echo isset( $_POST['address'] ) ? $_POST['address'] : ''; ?>"
                                               placeholder="Nhập địa chỉ mới nếu có" />
                                    </div>
                                    <div class="form-group">
                                        <label><?php _e( 'Xuất Xứ', 'willgroup' ); ?></label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="floor" value="<?php echo isset( $_POST['floor'] ) ? $_POST['floor'] : ''; ?>"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="formControlStoreAddressInput">Link cửa hàng</label>
                                        <input class="form-control"
                                               type="text"
                                               name="front"
                                               id="formControlStoreAddressInput"
                                               placeholder="https://www.tenshop.com"
                                               value="<?php echo isset( $_POST['front'] ) ? $_POST['front'] : ''; ?>"/>
                                    </div>
                                    <div class="form-group">

                                        <?php
                                            $helpNoteOptions = array(
                                                "option1" => "Bạn muốn đăng theo nguyên văn bản thảo mà bạn đã gửi",
                                                "option2" => "Bạn gửi bản nháp để chúng tôi chỉnh sửa trên nội dung có sẵn",
                                                "option3" => "Bạn cần chúng tôi tư vấn và hỗ trợ thêm về hình ảnh,nội dung sản phẩm"
                                            );

                                            foreach ($helpNoteOptions as $value => $label) {
                                                $is_checked = $value == $_POST['help_note']?>
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="radio"
                                                           name="help_note"
                                                           id="helpNoteRadios1"
                                                           value="<?php echo $value ?>"
                                                    <?php echo $is_checked ? "checked" : "" ?> >
                                                    <label class="form-check-label" for="helpNoteRadios1">
                                                        <?php echo $label ?>
                                                    </label>
                                                </div>
                                            <?php }?>

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlNoteTextarea1">Ghi chú</label>
                                        <textarea name="note" class="form-control" id="exampleFormControlNoteTextarea1" rows="3"><?php echo $_POST['note'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 col-xs-12">
                            <div class="card shadow my-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Nội dung sản phẩm</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Tiêu đề <span class="required">*</span></label>
                                        <input type="text"
                                               name="title"
                                               value="<?php echo $_POST['title']; ?>"
                                               class="form-control"
                                               id="formControlStartDayInput"
                                               placeholder="Tiêu đề:">
                                    </div>
                                    <div class="form-group">
                                        <label>Khối lượng <span class="required">*</span></label>
                                        <div class="input-group">
                                            <input class="form-control"
                                                   type="text"
                                                   name="area"
                                                   value="<?php echo $_POST['area']; ?>"
                                                   placeholder="Nhập khối lượng ước tính của sản phẩm">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col">
                                            <label>Giá tiền<span class="required">*</span></label>
                                            <input class="form-control"
                                                   type="text"
                                                   name="price"
                                                   placeholder="Nhập giá bán của sản phẩm"
                                                   value="<?php echo $_POST['price']; ?>"/>
                                        </div>
                                        <div class="form-group col">
                                            <label><?php _e( 'Đơn giá', 'willgroup' ); ?> <span class="required">*</span></label>
                                            <select class="form-control custom-select" name="unit_price">
                                                <option value="0"><?php _e( 'Chọn đơn giá', 'willgroup' ); ?></option>

                                                <?php foreach (array("VNĐ", "Người", "Giờ", "Tháng", "Kg", "Thỏa thuận") as $key => $donvi) {
                                                    $value = $key + 1; ?>
                                                    <option value="<?php echo $value ?>"
                                                        <?php echo $_POST['unit_price'] == $value ? 'selected' : ''; ?>><?php _e( $donvi, 'willgroup' ); ?></option>
                                                <?php } ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
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
                                    <div class="form-group">
                                        <label><?php _e( 'Ảnh sản phẩm', 'willgroup' ); ?></label>
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
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <div>
                                        <a href="<?php echo site_url("/nguoi-dung/danh-sach-tin-dang") ?>" class="btn btn-danger">Hủy</a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="btn btn-warning">Lưu Nháp</a>
                                        <button href="#" type="submit" class="btn btn-info">
                                            <?php echo $form_action == "edit" ? "Cập nhật" : "Đăng ngay"; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

</div>

<?php wp_footer(); ?>

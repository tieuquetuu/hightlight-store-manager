<?php

namespace StoreHightLight;

use StoreHightLight\StoreHLPageTemplater;
use StoreHightLight\StoreHLRestAPI;
use StoreHightLight\StoreHLGA4;
use WP_Query;
use WP_User;

class StoreHL
{
    private static $instance = NULL;

    public static function instance() {
        if ( ! isset( self::$instance ) || ! ( self::$instance instanceof StoreHL ) ) {
            self::$instance = new StoreHL();
            self::$instance->setup_constants();
            if ( self::$instance->includes() ) {
                self::$instance->actions();
                self::$instance->filters();
            }
        }

        /**
         * Return the HLStore Instance
         */
        return self::$instance;
    }

    public static function setup_constants() {
// Set main file path.
        $main_file_path = dirname( __DIR__ ) . '/store-hightlight-manager.php';

        // Plugin version.
        if ( ! defined( 'STORE_HL_VERSION' ) ) {
            define( 'STORE_HL_VERSION', '0.0.1' );
        }

        // Plugin Folder Path.
        if ( ! defined( 'STORE_HL_PLUGIN_DIR' ) ) {
            define( 'STORE_HL_PLUGIN_DIR', plugin_dir_path( $main_file_path ) );
        }

        // Plugin Root File.
        if ( ! defined( 'STORE_HL_PLUGIN_FILE' ) ) {
            define( 'STORE_HL_PLUGIN_FILE', $main_file_path );
        }

        // Whether to autoload the files or not.
        if ( ! defined( 'STORE_HL_AUTOLOAD' ) ) {
            define( 'STORE_HL_AUTOLOAD', true );
        }

        // The minimum version of PHP this plugin requires to work properly
        if ( ! defined( 'STORE_HL_MIN_PHP_VERSION' ) ) {
            define( 'STORE_HL_MIN_PHP_VERSION', '7.4' );
        }
    }

    /**
     * Include required files.
     * Uses composer's autoload
     *
     * @since  0.0.1
     * @return bool
     */
    public static function includes() {
        if ( defined( 'STORE_HL_AUTOLOAD' ) && true === STORE_HL_AUTOLOAD ) {

            if ( file_exists( STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'vendor/autoload.php' ) ) {
                // Autoload Required Classes.
                require_once STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'vendor/autoload.php';
            }

            // If GraphQL class doesn't exist, then dependencies cannot be
            // detected. This likely means the user cloned the repo from Github
            // but did not run `composer install`
//            if ( ! class_exists( 'HightLightStore\StoreHL' ) ) {
//                return false;
//            }
        }

        require_once STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'src/StoreHLPageTemplater.php';
        require_once STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'src/StoreHLRestAPI.php';
        require_once STORE_HIGHT_LIGHT_PLUGIN_DIR_PATH . 'src/StoreHLGA4.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';

        return true;
    }

    public static function actions() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'load_front_end_scripts' ) );
        add_action( 'plugin_loaded', function() { StoreHLPageTemplater::get_instance(); } );

//        //The Following registers an api route with multiple parameters.
        add_action( 'rest_api_init', array(__CLASS__, 'handle_rest_api_init') );
        add_action( 'rest_api_init', array( 'StoreHightLight\StoreHLRestAPI', 'init_actions') );

        add_action( 'cron_check_end_day', array(__CLASS__, 'check_end_day') );
        add_action( 'cron_send_mail', array(__CLASS__, 'check_end_day_send_mail') );
        add_action( 'init', array(__CLASS__, 'schedule_cron_check_end_day') );
    }

    public static function filters() {

    }


    /**
     * @namespace: load_scripts
     * @description : Load các script cần thiết
     * @author : hieusmall
     */
    public static function load_front_end_scripts() {
        global $query_class, $post;

        $product_slug = get_query_var('product');

        if (!$product_slug) {
            $product_slug = get_query_var('nha-dat');
        }

        $is_satellite_site = isset($query_class) && !is_null($query_class) ? TRUE : FALSE;
        $is_main_site = $is_satellite_site ? FALSE : TRUE;

        $product = NULL;
        $productUrl = NULL;
        $is_hl_product = FALSE;

        if ($is_main_site) {

            if ($post->post_type == 're') {
                $product = $post;
            }

        } elseif ($is_satellite_site) {
            $product = strlen($product_slug) > 0 ? $query_class::cmplugin_get_product_by_slug($product_slug) : NULL;
        }

        $is_hl_product = isset($product) && !is_null($product);
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $host_name = $_SERVER['HTTP_HOST'];
        $user_cookie = $_SERVER['HTTP_COOKIE'];
        $request_uri = $_SERVER['REQUEST_URI'];
        $referer = $_SERVER['HTTP_REFERER'];
        $protocol = $_SERVER['SERVER_PROTOCOL'];

        $translation_array = array(
            'site_url'              =>  get_site_url(),
            'site_rest_url'         =>  get_rest_url(),
            'hostname'              =>  $host_name,
            'is_main_site'          =>  $is_main_site,
            'is_hightlight_product' =>  $is_hl_product,
            'user_ip'               =>  $user_ip,
            'user_cookie'           =>  $user_cookie,
            'referer'               =>  $referer,
            'nonce'                 =>  wp_create_nonce('wp_rest'),
        );

        if (isset($product)) {
            $translation_array = array_merge($translation_array, array(
                'product_id'            =>  $product->ID,
                'product_slug'          =>  $product->post_name,
                'product_title'         =>  $product->post_title,
                // 'product_url'           =>  $productUrl,
                'author_id'             =>  $product->post_author,
            ));
        }

        // Store Hight Light Extension Library
        wp_enqueue_style( 'hightlight-data-table-css',"//cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.4/kt-2.7.0/rg-1.2.0/rr-1.2.8/sc-2.0.7/sb-1.3.4/sp-2.0.2/sl-1.4.0/sr-1.1.1/datatables.min.css");
        wp_enqueue_script( 'hightlight-jquery-js','//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-data-table-pdfmake-js','//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-data-table-pdfmake-vfs_fonts-js','//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-data-table-js','//cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.4/kt-2.7.0/rg-1.2.0/rr-1.2.8/sc-2.0.7/sb-1.3.4/sp-2.0.2/sl-1.4.0/sr-1.1.1/datatables.min.js', array( 'jquery' ), '', true );

        // Store Hight Light Script Tracking
        wp_enqueue_style( 'hightlight-store-css',  STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . "assets/css/main.css");
        wp_enqueue_script( 'hightlight-store-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/main.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-store-statistical-report-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/statistical-report.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-store-domain-manager-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/domain-manager.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-store-users-manager-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/users-manager.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-store-system-manager-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/system-manager.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'hightlight-store-tracking-js', STORE_HIGHT_LIGHT_PLUGIN_DIR_URL . 'assets/js/tracking.js', array( 'jquery' ), '', true );
        // Localize the script with new data
        wp_localize_script( 'hightlight-store-js', 'hightlight_client_object', $translation_array );
    }

    public static function ManagerDataNavigation() {

        $current_user = wp_get_current_user();
        $current_link = get_the_permalink();

        return '<div class="container-fluid my-5">
        <div class="row">
            <div class="col col-md-4">
                <a class="button-manager" href="'.  site_url() . "/tong-quan-so-lieu/quan-li-he-thong" .'">
                    Hệ thống
                </a>
            </div>
            <div class="col col-md-4">
                <a class="button-manager" href="'.  site_url() . "/tong-quan-so-lieu/quan-li-website" .'">
                    Tên miền
                </a>
            </div>
            <div class="col col-md-4">
                <a class="button-manager" href="'.  site_url() . "/tong-quan-so-lieu/quan-li-user" .'">
                    User
                </a>
            </div>
        </div>
    </div>';
    }

    public static function productIsExpireSoon($post) {
        $today = date_create("now");
        $post_id = $post->ID;
        $end_day_meta = get_post_meta($post_id)["end_day"][0];
        $end_day = isset($end_day_meta) && !empty($end_day_meta) && !is_null($end_day_meta) ? date_create($end_day_meta) : null;
        if (!$end_day) {
            return false;
        }
        $interval = date_diff($today, $end_day);
        $flag = $today->getTimestamp() < $end_day->getTimestamp() && $interval->d <= 3;
        return $flag;
    }

    public static function productIsExpired($product) {
        $today = date_create("now");
        $post_id = $product->ID;
        $end_day_meta = get_post_meta($post_id)["end_day"][0];
        $end_day = isset($end_day_meta) && !empty($end_day_meta) && !is_null($end_day_meta) ? date_create($end_day_meta) : null;

        if (!$end_day) {
            return false;
        }

        $interval = date_diff($today, $end_day);
        $flag = $today->getTimestamp() > $end_day->getTimestamp();
        return $flag;
    }

    public static function countDownDateProductExpired($product) {
        $today = date_create("now");
        $end_day_meta = get_post_meta($product->ID)["end_day"][0];
        $end_day = isset($end_day_meta) && !empty($end_day_meta) && !is_null($end_day_meta) ? date_create($end_day_meta) : null;
        if (!$end_day) {
            return null;
        }
        $interval = date_diff($today, $end_day);
        return $interval;
    }

    public static function listProductsExpiredSoon(Array $args = array()) {

        $user_id = isset($args["user_id"]) && !is_null($args["user_id"]) ? $args["user_id"] : null;
        $today = date_create("now");
        $results = array();
        $query_args = array(
            'post_type'   => 're',
            'post_status' =>'publish',
            'sort_order' => 'desc',
            'posts_per_page' => -1
        );

        if ($user_id) {
            $query_args['author'] = $user_id;
        }

        $the_query = new \WP_Query( $query_args );

        if ( $the_query->have_posts() ) :
            foreach ($the_query->posts as $post) {
                /*$post_id = $post->ID;

                $end_day_meta = get_post_meta($post_id)["end_day"][0];
                $end_day = isset($end_day_meta) && !empty($end_day_meta) && !is_null($end_day_meta) ? date_create($end_day_meta) : null;

                if (!$end_day) {
                    continue;
                }

                $interval = date_diff($today, $end_day);
                $flag = $today->getTimestamp() < $end_day->getTimestamp() && $interval->d <= 3;
                if (!$flag) {
                    continue;
                } else {
                    array_push($results, $post);
                }*/
                if (self::productIsExpireSoon($post)) {
                    array_push($results, $post);
                }
            }
        endif;

        return $results;
    }

    public static function check_end_day(){
        $query_args = array(
            'post_type'   => 're',
            'post_status' =>'publish',
            'sort_order' => 'desc',
            'posts_per_page' => -1
        );
        $the_query = new \WP_Query( $query_args );

        if ( $the_query->have_posts() ) :
            foreach ($the_query->posts as $post) {
                $post_id = $post->ID;

                $flag = !empty(get_post_meta($post_id)["end_day"][0]) && strtotime(date("Ymd"))>strtotime(get_post_meta($post_id)["end_day"][0]);

                $flag = self::productIsExpired($post);

                if (!$flag) {
                    continue;
                } else {
                    $my_post = array(
                        'ID'           => $post_id,
                        'post_status'   => 'pending'
                    );
                    wp_update_post( $my_post );
                }
            }

            /*while ( $the_query->have_posts() ) : $the_query->the_post();
                global $post;
                if(!empty(get_post_meta($the_query->post->ID)["end_day"][0]) && strtotime(date("Ymd"))>strtotime(get_post_meta($the_query->post->ID)["end_day"][0]))
                {
                    $my_post = array(
                        'ID'           => $the_query->post->ID,
                        'post_status'   => 'pending'
                    );
                    wp_update_post( $my_post );
                }
            endwhile;*/
        endif;

        // Reset Post Data
//        wp_reset_postdata();
    }

    public static function check_end_day_send_mail(){
        $day_before = 3;
        $time_zone_7=7*60*60;
        $query_args = array(
            'post_type'   => 're',
            'post_status' =>'publish',
            'sort_order' => 'desc',
            'posts_per_page' => -1
        );
        $the_query = new WP_Query( $query_args );
        $send_mails_log = array();

        $expireSoonItems = self::listProductsExpiredSoon();

        foreach ($expireSoonItems as $expireSoonItem) {
            $flag = !self::productIsExpired($expireSoonItem) && self::productIsExpireSoon($expireSoonItem);
            if($flag)
            {
                $author_id = get_post_field('post_author', $expireSoonItem->ID);
                $author = \WP_User::get_data_by('id', $author_id);
                $user_email = $author->user_email;
                //php mailer variables
                $to = $user_email;
                $subject = "Thông báo gia hạn dịch vụ";
                $message = "Sản phẩm của bạn còn 3 ngày nữa sẽ hết hạn, bạn cần gia hạn: ".get_permalink($expireSoonItem->ID);
                //Here put your Validation and send mail
                $sent = wp_mail( $to, $subject, $message);
                array_push($send_mails_log, array(
                    $to, $subject, $message, $sent
                ));
            }
        }

        /*if ( $the_query->have_posts() ) :

            foreach ($the_query->posts as $post) {

                $flag = !empty(get_post_meta($post->ID)["end_day"][0]) && strtotime(date("Ymd")) + $day_before*86400==strtotime(get_post_meta($post->ID)["end_day"][0]);

                if($flag)
                {
                    $author_id = get_post_field('post_author', $post->ID);
                    $author = \WP_User::get_data_by('id', $author_id);

                    $user_email = $author->user_email;

//                    $user_email = get_the_author_meta( 'user_email' , $author_id );

                    //php mailer variables
                    $to = $user_email;
                    $subject = "Thông báo gia hạn dịch vụ";
                    $message = "Sản phẩm của bạn còn 3 ngày nữa sẽ hết hạn, bạn cần gia hạn: ".get_permalink($post->ID);

                    //Here put your Validation and send mail
                    $sent = wp_mail( $to, $subject, $message);
                    array_push($send_mails_log, array(
                        $to, $subject, $message, $sent
                    ));
                }
            }
        endif;*/

        return $send_mails_log;

        // Reset Post Data
//        wp_reset_postdata();
    }

    public static function schedule_cron_check_end_day() {
        if ( ! wp_next_scheduled('cron_check_end_day') ) {
            //condition to makes sure that the task is not re-created if it already exists
            wp_schedule_event( strtotime(date("Ymd"))+24*60*60+1, 'daily', 'cron_check_end_day' );
        }
        if ( ! wp_next_scheduled('cron_send_mail') ) {
            //condition to makes sure that the task is not re-created if it already exists
            wp_schedule_event( strtotime(date("Ymd"))+24*60*60+1, 'daily', 'cron_send_mail' );
        }
    }

    public static function queryStoreProducts(Array $args = array()) {
        $result = null;
        $default_args = array(
            'post_type' 	 => 're',
            'posts_per_page' => 10,
            'post_status'	 => array(
                "publish",
                "pending",
                "private"
            ),
        );

        foreach ($args as $key => $value) {
            $default_args[$key] = $value;
        }

        $query_args = array_merge($default_args);

        $data = new \WP_Query($query_args);

        return $data;
    }

    public static function getHostNames() {
        $result = null;

        $request = StoreHLGA4::instance()->RequestListHostName();
        $report = StoreHLGA4::makeRunReport($request);
        $pretty_report = StoreHLGA4::makeReportPretty($report);
        return $pretty_report;
    }

    public static function handleHightLightQueryProducts($request) {
        return new \WP_REST_Response(
            array(
                'message' => ''
            ),
            200
        );
    }

    public static function handleHightLightAnalytics($request) {
        $method = $request->get_method();

        $storeHightLightGA4 = new StoreHLGA4();
        $reports = $storeHightLightGA4::instance()->AnalyticsGoogleBatchReport(array());

        print_r($reports);

        die();

        return new \WP_REST_Response(
            array(
                'message' => 'Analytics Setup OK',
                'method' => $method
            ),
            200
        );
    }

    public static function handle_rest_api_init() {
        // Google Analytics API
        register_rest_route('hightlight/v1', '/analytics', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleHightLightAnalytics')
        ));

        // Product hightlight store API
        register_rest_route('hightlight/v1', '/products', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleHightLightQueryProducts')
        ));
    }
}
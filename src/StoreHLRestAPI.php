<?php

namespace StoreHightLight;

use WP_REST_Response;
use StoreHightLight\StoreHLGA4;
use StoreHightLight\StoreHL;

class StoreHLRestAPI
{
    protected $instance = NULL;

    public static function instance() {
        if ( ! isset( self::$instance ) || ! ( self::$instance instanceof StoreHLRestAPI ) ) {
            self::$instance = new StoreHLRestAPI();
//            self::$instance->setup_constants();
//            if ( self::$instance->includes() ) {
//                self::$instance->init_actions();
//                self::$instance->filters();
//            }
        }

        /**
         * Return the HLStore Instance
         */
        return self::$instance;
    }

    public static function handleRunReportGA4($request) {

        $params = $request->get_params();

        $dimensions = isset($params['dimensions']) && is_string($params['dimensions']) && strlen($params['dimensions']) > 0 ? explode(",", $params['dimensions']) : null;
        $metrics = isset($params['metrics']) && is_string($params['metrics']) && strlen($params['metrics']) > 0 ? explode(",",$params['metrics']) : null;

        try {
            $report = StoreHLGA4::instance()->GArunReport([
                'dimensions' => $dimensions,
                'metrics' => $metrics
            ]);

            if (is_null($report) || is_string($report)) {
                return wp_send_json(array("message" => "Không tìm thấy dữ liệu"), 401);
            }

            $report_str = $report->serializeToJsonString();

            return json_decode($report_str);
        } catch (Exception $e) {
            return wp_send_json(array("message" => "Something wrong"), 401);
        }
    }

    public static function handleDataTableReportGA4($request) {
        $params = $request->get_params();

        $sEcho = $params['sEcho'];

        $productQuery = StoreHL::instance()->queryStoreProducts(array(
            "post_status" => "publish",
        ));

        var_dump($productQuery);
        die();

        /*$report = StoreHLGA4::instance()->GArunReport([
            'dimensions' => $dimensions,
            'metrics' => $metrics
        ]);*/

        $result = array(
            "draw" => 1,
            "recordsTotal" => 57,
            "recordsFiltered" => 57,
            "data" => array(
                [
                    "store.dizital.vn",
                    "Mỹ Phẩm",
                    "Mỹ phẩm nhật bản siêu trắng tắm trắng ahihi",
                    "Khanh Dang",
                    1000,
                    100,
                    200,
                    "30 giây",
                    1000
                ],
            ),
        );

        return wp_send_json($result, 200);
//        return new \WP_REST_Response($result, 200);
    }

    public static function handleSystemDataTableReport($request) {
        $params = $request->get_params();

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );
        $data = array();

        $author = isset($params["author"]) && (int) $params["author"] > 0 ? (int)$params["author"] : null;
        $category = isset($params["category"]) && (int) $params["category"] > 0 ? (int)$params["category"] : null;
        $domain = isset($params["domain"]) && is_string($params["domain"]) && strlen($params["domain"]) > 0 ? $params["domain"] : null;
        $dateRanges = isset($params["date_ranges"]) && is_string($params["date_ranges"]) && strlen($params["date_ranges"]) > 0 && gettype(json_decode($params["date_ranges"])) == "object" ? (array) json_decode($params["date_ranges"]) : null;

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $queryArgs = array(
            "posts_per_page" => $limit,
//            "paged" => $pageIndex,
//            "page" => $pageIndex,
            "offset" => $offset,
            "s" => $search
        );
        if ($author) {
            $queryArgs["author"] = $author;
        }
        if ($category){
            $queryArgs["tax_query"] = array(
                "relation" => "AND",
                array(
                    'taxonomy' => 're_cat',
                    'terms' => array( $category ),
                    'operator' => 'IN'
                )
            );
        }

        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return $result;
        }

        $productSlugs = array();

        foreach ($queryProducts->posts as $item) {
            if (strlen($item->post_name) <= 0) {
                continue;
            }
            array_push($productSlugs, $item->post_name);
        }

        $args_request_report = array(
            "productSlugs" => $productSlugs
        );

        if ($domain) {
            $args_request_report["hostNames"] = array($domain);
        }
        if ($dateRanges) {
            $args_request_report["dateRanges"] = array($dateRanges);
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData($args_request_report);

        $report = StoreHLGA4::instance()->makeRunReport($request_report_domain);

        $pretty_report = StoreHLGA4::makeReportPretty($report);

        $report_str = $report->serializeToJsonString();
        $report_json = json_decode($report_str);
        $rowsCount = $report_json->rowCount;
        $rowsData = $report_json->rows;

        $result["recordsFiltered"] = (int) $queryProducts->found_posts;
        $result["recordsTotal"] = (int) $queryProducts->found_posts;

        foreach ($queryProducts->posts as $product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productSlug = $product->post_name;
            $productId = $product->ID;
            $productCategory = get_the_terms($productId, "re_cat");

            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;

            $analytics = null;

            $analytics_filter = array_filter($pretty_report, function($reportItem) use (&$productTitle){
                return str_contains($reportItem->pageTitle, $productTitle);
            });

            $analytics = array_values($analytics_filter);

            $row = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => $productCategory,
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "status" => $status,
                "product" => $product,
                "analytics" => $analytics,
            );
            array_push($result['data'], $row);
        }

        return wp_send_json($result, 200);
    }

    public static function handleDomainDataTableReport($request) {
        $params = $request->get_params();

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );

        $request_report_by_domain = StoreHLGA4::instance()->RequestReportByHostName();

        $response_domain_report = StoreHLGA4::instance()->makeRunReport($request_report_by_domain);

        $data = StoreHLGA4::instance()->makeReportPretty($response_domain_report);

        $convert_domain_rows = array();

        foreach ($data as $countIndex => $item) {
            $keyName = $item->hostName;
            if (!key_exists($keyName, $convert_domain_rows)) {
                $convert_domain_rows[$keyName] = array(
                    "hostName" => $keyName,
                    "click_buy_product" => 0,
                    "click_view_shop" => 0,
                    "screenPageViews" => 0,
                    "averageSessionDuration" => 0,
                    "analytics" => array()
                );
            };

            $convert_domain_rows[$keyName]["averageSessionDuration"] += floatval($item->averageSessionDuration);
            $convert_domain_rows[$keyName]["screenPageViews"] += (int) $item->screenPageViews;
            if ($item->eventName == "click_buy_product") {
                $convert_domain_rows[$keyName]["click_buy_product"] += (int)$item->eventCount;
            }
            if ($item->eventName == "click_view_shop") {
                $convert_domain_rows[$keyName]["click_view_shop"] += (int)$item->eventCount;
            }

            array_push($convert_domain_rows[$keyName]["analytics"], $item);

            // End The Loop
            if ($countIndex + 1 == count($data)) {
                // Tính thời gian xem trung bình
                $totalAverageSessionDuration = $convert_domain_rows[$keyName]["averageSessionDuration"];
                $totalAnalyticItems = count($convert_domain_rows[$keyName]["analytics"]);

                $convert_domain_rows[$keyName]["averageSessionDuration"] = $totalAverageSessionDuration / $totalAnalyticItems;
            }
        }

        $total_rows = count(array_keys($convert_domain_rows));

        if ($total_rows > 0) {
            $result["data"] = array_values($convert_domain_rows);
        }

        $result["recordsFiltered"] = $total_rows;
        $result["recordsTotal"] = $total_rows;

        return wp_send_json($result, 200);
    }

    public static function handleDetailDomainDataTableReport($request) {
        $params = $request->get_params();

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );
        $data = array();

        $author = isset($params["author"]) && (int) $params["author"] > 0 ? (int)$params["author"] : null;
        $category = isset($params["category"]) && (int) $params["category"] > 0 ? (int)$params["category"] : null;
        $domain = isset($params["domain"]) && is_string($params["domain"]) && strlen($params["domain"]) > 0 ? $params["domain"] : null;

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $queryArgs = array(
            "posts_per_page" => -1,
            "post_status" => array(
                "publish", "pending", "draft", "future"
            ),
            "offset" => $offset,
            "s" => $search
        );
        if ($author) {
            $queryArgs["author"] = $author;
        }
        if ($category){
            $queryArgs["tax_query"] = array(
                "relation" => "AND",
                array(
                    'taxonomy' => 're_cat',
                    'terms' => array( $category ),
                    'operator' => 'IN'
                )
            );
        }

        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return $result;
        }

        $args_request_report = array();

        if ($domain) {
            $args_request_report["hostNames"] = array($domain);
        }

        $productSlugs = array_map(function($prod){
            return $prod->post_name;
        },$queryProducts->posts);
        $productSlugs = array_filter($productSlugs, function($slug){
            return strlen($slug) > 0;
        });

        if (count($productSlugs) > 0) {
            $args_request_report["productSlugs"] = $productSlugs;
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData($args_request_report);

        $report = StoreHLGA4::instance()->makeRunReport($request_report_domain);

        $pretty_report = StoreHLGA4::makeReportPretty($report);

        $report_str = $report->serializeToJsonString();
        $report_json = json_decode($report_str);
        $rowsCount = $report_json->rowCount;
        $rowsData = $report_json->rows;

        $result["recordsFiltered"] = (int) $queryProducts->found_posts;
        $result["recordsTotal"] = (int) $queryProducts->found_posts;

        foreach ($queryProducts->posts as $product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productSlug = $product->post_name;
            $productId = $product->ID;
            $productCategory = get_the_terms($productId, "re_cat");

            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;

            $analytics = null;

            $analytics_filter = array_filter($pretty_report, function($reportItem) use (&$productTitle, &$productSlug){

                if(!$productSlug) {
                    return str_contains($reportItem->pageTitle, $productTitle);
                }

                $pathName = $reportItem->pagePath;
                $pathName = str_replace("/product/", "",$pathName);
                $pathName = str_replace("/nha-dat/", "",$pathName);
                $pathName = str_replace("/", "",$pathName);

                return $pathName == $productSlug;
            });

            $analytics = array_values($analytics_filter);

            $row = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => $productCategory,
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "status" => $status,
                "product" => $product,
                "analytics" => $analytics,
            );
            array_push($result['data'], $row);
        }

        return wp_send_json($result, 200);
    }

    public static function handleUsersDataTableReport($request) {
        $params = $request->get_params();

        $author = isset($params["author"]) && (int) $params["author"] > 0 ? (int)$params["author"] : null;
        $category = isset($params["category"]) && (int) $params["category"] > 0 ? (int)$params["category"] : null;
        $domain = isset($params["domain"]) && is_string($params["domain"]) && strlen($params["domain"]) > 0 ? $params["domain"] : null;
        $dateRanges = isset($params["date_ranges"]) && is_string($params["date_ranges"]) && strlen($params["date_ranges"]) > 0 && gettype(json_decode($params["date_ranges"])) == "object" ? (array) json_decode($params["date_ranges"]) : null;

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $sortCol = isset($params['iSortCol_0']) ? $params['iSortCol_0'] : false;
        $sortDir = isset($params['sSortDir_0']) ? $params['sSortDir_0'] : false;

        $queryArgs = array(
            "posts_per_page" => $limit,
            "offset" => $offset,
            "s" => $search
        );

        if ($author) {
            $queryArgs["author"] = $author;
        }

        if ($category){
            $queryArgs["tax_query"] = array(
                "relation" => "AND",
                array(
                    'taxonomy' => 're_cat',
                    'terms' => array( $category ),
                    'operator' => 'IN'
                )
            );
        }

        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return wp_send_json($result, 200);
        }

        /**
         * Nếu $sortCol = 5 , sắp xếp bởi lượt nhiều nhất
         */

        $productSlugs = array();

        foreach ($queryProducts->posts as $item) {
            if (strlen($item->post_name) <= 0) {
                continue;
            }
            array_push($productSlugs, $item->post_name);
        }

        $args_request_report = array(
            "productSlugs" => $productSlugs
        );

        if ($domain) {
            $args_request_report["hostNames"] = array($domain);
        }
        if ($dateRanges) {
            $args_request_report["dateRanges"] = array($dateRanges);
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData($args_request_report);

        $report = StoreHLGA4::instance()->makeRunReport($request_report_domain);

        $pretty_report = StoreHLGA4::makeReportPretty($report);

        $report_str = $report->serializeToJsonString();
        $report_json = json_decode($report_str);

        $result["recordsFiltered"] = (int) $queryProducts->found_posts;
        $result["recordsTotal"] = (int) $queryProducts->found_posts;

        foreach ($queryProducts->posts as $product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productSlug = $product->post_name;
            $productId = $product->ID;
            $productCategory = get_the_terms($productId, "re_cat");

            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;

            $analytics = null;

            $analytics_filter = array_filter($pretty_report, function($reportItem) use (&$productTitle){
                return str_contains($reportItem->pageTitle, $productTitle);
            });

            $analytics = array_values($analytics_filter);

            $row = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => $productCategory,
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "status" => $status,
                "product" => $product,
                "analytics" => $analytics,
            );
            array_push($result['data'], $row);
        }

        return wp_send_json($result, 200);
    }


    public static function handleUserManagerDataReport($request) {
        $params = $request->get_params();

        $author = isset($params["author"]) && (int) $params["author"] > 0 ? (int)$params["author"] : null;
        $category = isset($params["category"]) && (int) $params["category"] > 0 ? (int)$params["category"] : null;
        $domain = isset($params["domain"]) && is_string($params["domain"]) && strlen($params["domain"]) > 0 ? $params["domain"] : null;
        $dateRanges = isset($params["date_ranges"]) && is_string($params["date_ranges"]) && strlen($params["date_ranges"]) > 0 && gettype(json_decode($params["date_ranges"])) == "object" ? (array) json_decode($params["date_ranges"]) : null;

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $result = array(
            "data" => array(),
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );

        // Lấy Danh sách tất cả user
        $query_users = new \WP_User_Query(array(
            "number" => $limit,
            "offset" => $offset
        ));

        $total_users = $query_users->get_total();

        $users = $query_users->get_results();

        // Nếu không có user nào
        if ($total_users <= 0) {
            return wp_send_json($result, 200);
        }

        $data = array();

        $author_ids = array_map(function($user){
            return $user->ID;
        },$users);
        $author_ids_str = implode(",", $author_ids);

        // Lấy danh sách bài viết theo ID tác giả
        $args_query_product = array(
            "posts_per_page"    => -1,
            "author"            => $author_ids_str
        );

        if ($category){
            $queryArgs["tax_query"] = array(
                "relation" => "AND",
                array(
                    'taxonomy' => 're_cat',
                    'terms' => array( $category ),
                    'operator' => 'IN'
                )
            );
        }

        $query_products = StoreHL::queryStoreProducts($args_query_product);
        $products = $query_products->get_posts();
        $product_slugs = array_map(function($prod){
            return $prod->post_name;
        },$products);

        // Lấy danh sách báo cáo analytics theo product slug
        $args_request_report = array(
            "productSlugs" => $product_slugs
        );
        if ($domain) {
            $args_request_report["hostNames"] = array($domain);
        }
        if ($dateRanges) {
            $args_request_report["dateRanges"] = array($dateRanges);
        }
        $analytics_request = StoreHLGA4::RequestReportSummaryData($args_request_report);
        $analytics_report = StoreHLGA4::makeRunReport($analytics_request);
        $analytics_report_pretty = StoreHLGA4::makeReportPretty($analytics_report);

        // Chuyển đổi lại dữ liệu
        foreach ($users as $aut_key => $aut) {

            $aut_posts = array_values(array_filter($products, function($product_var) use (&$aut) {
                return $product_var->post_author == $aut->ID;
            }));

            $aut_posts_analytics = array_map(function($aut_post) use (&$analytics_report_pretty) {
                $analytics = array_values(array_filter($analytics_report_pretty, function ($analytics_item) use (&$aut_post) {
                    $pagePath = $analytics_item->pagePath;
                    $pagePath = str_replace("/product/", "", $pagePath);
                    $pagePath = str_replace("/nha-dat/", "", $pagePath);
                    $pagePath = str_replace("/", "", $pagePath);
                    return $pagePath == $aut_post->post_name;
                }));
                $productId = $aut_post->ID;
                $productTitle = $aut_post->post_title;
                $productCategory = get_the_terms($productId, "re_cat");
                $status = "Chờ duyệt";
                if ($aut_post->post_status == "publish") : $status = "Đang hoạt động"; endif;

                $post_item = array(
                    "id" => $productId,
                    "title" => $productTitle,
                    "category" => $productCategory,
                    "status" => $status,
                    "product" => $aut_post,
                    "analytics" => $analytics,
                );

                return $post_item;
            },$aut_posts);

            $author = (object) array(
                "id"                => $aut->ID,
                "user_email"        => $aut->user_email,
                "display_name"      => $aut->display_name,
                "roles"             => $aut->roles,
                "user_status"       => $aut->user_status,
                "user_registered"   => $aut->user_registered,
                "posts"             => $aut_posts_analytics,
            );

            array_push($data, $author);
        }

        $result["data"] = $data;
        $result["recordsFiltered"] = $total_users;
        $result["recordsTotal"] = $total_users;
        $result["analytics_report"] = $analytics_report_pretty;

        return wp_send_json($result, 200);
    }


    /**
     * @description Lấy dữ liệu thống kê theo danh sách người dùng
     * @columns
     * @param $request
     * @return void
     */
    public static function handleManagerUsersDataReport($request) {
        $params = $request->get_params();

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );

        $author = isset($params["author"]) && (int) $params["author"] > 0 ? (int)$params["author"] : null;
        $category = isset($params["category"]) && (int) $params["category"] > 0 ? (int)$params["category"] : null;
        $domain = isset($params["domain"]) && is_string($params["domain"]) && strlen($params["domain"]) > 0 ? $params["domain"] : null;
        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";
        $sortCol = isset($params['iSortCol_0']) ? $params['iSortCol_0'] : false;
        $sortDir = isset($params['sSortDir_0']) ? $params['sSortDir_0'] : false;

        $queryArgs = array(
            "posts_per_page" => $limit,
            "offset" => $offset,
            "post_status" => array("publish", "pending"),
            "s" => $search
        );
        if ($author) {
            $queryArgs["author"] = $author;
        }
        if ($category){
            $queryArgs["tax_query"] = array(
                "relation" => "AND",
                array(
                    'taxonomy' => 're_cat',
                    'terms' => array( $category ),
                    'operator' => 'IN'
                )
            );
        }

        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Sắp xếp lại danh sách
        $rowsData = array();

        foreach ($queryProducts->posts as $key => $product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productSlug = $product->post_name;
            $productId = $product->ID;
            $productCategory = get_the_terms($productId, "re_cat");
            $productStatus = $product->post_status;
            $statusText = "Chờ duyệt";
            if ($productStatus == "publish") : $statusText = "Đang hoạt động"; endif;

            $row = array();
            $row["numerical_order"] = $key + 1;
            $row["id"] = $productId;
            $row["category"] = $productCategory;
            $row["title"] = $productTitle;
            $row["author"] = array(
                "id" => $author->ID,
                "display_name" => $author->display_name
            );
            $row["status"] = $statusText;

            array_push($rowsData, (object) $row);
        }

        $result["recordsFiltered"] = (int) $queryProducts->found_posts;
        $result["recordsTotal"] = (int) $queryProducts->found_posts;
        $result["data"] = $rowsData;

        return wp_send_json($result, 200);
    }

    public static function handlePageReportDataTable($request) {
        $params = $request->get_params();

        $result = array(
            "data" => array(),
//            "draw" => 1,
            "recordsFiltered" => 0,
            "recordsTotal" => 0
        );

        $dateRanges = isset($params["date_ranges"]) && is_string($params["date_ranges"]) && strlen($params["date_ranges"]) > 0 && gettype(json_decode($params["date_ranges"])) == "object" ? (array) json_decode($params["date_ranges"]) : null;

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $author = $params["author"];

        $queryArgs = array(
            "posts_per_page" => $limit,
//            "paged" => $pageIndex,
//            "page" => $pageIndex,
            "post_status" => array(
                "publish",
                "pending",
                "trash"
            ),
            "offset" => $offset,
            "author" => $author,
            "s" => $search
        );
        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return $result;
        }

        $productSlugs = array();

        foreach ($queryProducts->posts as $item) {
            if (strlen($item->post_name) <= 0) {
                continue;
            }
            array_push($productSlugs, $item->post_name);
        }

        $args_request_report = array(
            "productSlugs" => $productSlugs
        );

        if ($dateRanges) {
            $args_request_report["dateRanges"] = array($dateRanges);
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData($args_request_report);
        $report = StoreHLGA4::instance()->makeRunReport($request_report_domain);
        $pretty_report = StoreHLGA4::makeReportPretty($report);

        $report_str = $report->serializeToJsonString();
        $report_json = json_decode($report_str);

        $rowsCount = $report_json->rowCount;
        $rowsData = $report_json->rows;

        $result["recordsFiltered"] = (int) $queryProducts->found_posts;
        $result["recordsTotal"] = (int) $queryProducts->found_posts;

        foreach ($queryProducts->posts as $product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productSlug = $product->post_name;
            $productId = $product->ID;
            $productCategory = get_the_terms($productId, "re_cat");

            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;
            if ($product->post_status == "trash") : $status = "Đã xóa"; endif;

            $analytics = null;

            $analytics_filter = array_filter($pretty_report, function($reportItem) use (&$productTitle, &$productSlug){
                $pagePath = $reportItem->pagePath;
                $pagePath = str_replace("/product/","", $pagePath);
                $pagePath = str_replace("/nha-dat/","", $pagePath);
                $pagePath = str_replace("/","", $pagePath);

                if (strlen($productSlug) > 0) {
                    return $pagePath == $productSlug;
                }

                return str_contains($reportItem->pageTitle, $productTitle);
            });

            $analytics = array_values($analytics_filter);

            $row = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => $productCategory,
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "status" => $status,
                "product" => $product,
                "analytics" => $analytics,
            );
            array_push($result['data'], $row);
        }

        return wp_send_json($result, 200);
    }


    public static function handleUpdateProductTracking($request) {
        $params = $request->get_params();

        $accept_events = array("page_view", "click_buy_product", "click_view_shop");

        $product_id = is_numeric($params["product_id"]) && (int) $params["product_id"] ? (int) $params["product_id"] : false;
        $event_name = is_string($params["event_name"]) && in_array($params["event_name"], $accept_events) ? $params["event_name"] : false;
        $host_name = is_string($params["host_name"]) && strlen($params["host_name"]) > 0 ? $params["host_name"] : false;
        $start_time = $params["start_time"];
        $end_time = $params["end_time"];

        $now = strtotime("now");

        if (!$event_name) {
            return wp_send_json(array(
                "message" => "Sự kiện không xác định",
                "status" => "Failed"
            ), 401);
        } elseif (!$product_id) {
            return wp_send_json(array(
                "message" => "Thiếu ID sản phẩm",
                "status" => "Failed"
            ), 401);
        }

        $product = get_post($product_id);

        if (!$product) {
            return wp_send_json(array(
                "message" => "Không tìm thấy sản phẩm",
                "status" => "Failed"
            ), 401);
        }

//        var_dump(new \DateTime($start_time));

        $result = "ok";

        return wp_send_json($result, 200);
    }

    public static function init_actions() {
        register_rest_route('hightlight/v1', '/runReport', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleRunReportGA4')
        ));

        register_rest_route('hightlight/v1', '/reportSystemDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleSystemDataTableReport')
        ));

        register_rest_route('hightlight/v1', '/reportDomainDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleDomainDataTableReport')
        ));

        register_rest_route('hightlight/v1', '/reportDetailDomainDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleDetailDomainDataTableReport')
        ));

        register_rest_route('hightlight/v1', '/reportUsersDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleUserManagerDataReport'),
//            'callback' => array(__CLASS__, 'handleUsersDataTableReport')
//            'callback' => array(__CLASS__, 'handleManagerUsersDataReport')
        ));

        register_rest_route('hightlight/v1', '/pageReportDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handlePageReportDataTable')
        ));

        register_rest_route('hightlight/v1', '/reportDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleDataTableReportGA4')
        ));


        register_rest_route('hightlight/v1', '/tracking', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array(__CLASS__, 'handleUpdateProductTracking')
        ));
    }
}
<?php

namespace StoreHightLight;


use PHPUnit\Exception;
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


        $args_request_report = array();

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

        $json_report = json_decode($response_domain_report->serializeToJsonString());

        $data = StoreHLGA4::instance()->makeReportPretty($response_domain_report);

        $convert_domain_rows = array();

        foreach ($data as $item) {
            $keyName = $item->hostName;
            if (!key_exists($keyName, $convert_domain_rows)) {
                $convert_domain_rows[$keyName] = (object) array(
                    "hostName" => $keyName,
                    "click_buy_product" => 0,
                    "click_view_shop" => 0,
                    "screenPageViews" => 0,
                    "averageSessionDuration" => 0,
                    "analytics" => array()
                );
            };

            array_push($convert_domain_rows[$keyName]->analytics, $item);
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

        $args_request_report = array();

        if ($domain) {
            $args_request_report["hostNames"] = array($domain);
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
            return wp_send_json($result, 200);
        }

        /**
         * Nếu $sortCol = 5 , sắp xếp bởi lượt nhiều nhất
         */

        $args_request_report = array();

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
            'callback' => array(__CLASS__, 'handleUsersDataTableReport')
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
    }
}
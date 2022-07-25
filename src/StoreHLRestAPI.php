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

        $pageIndex = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] + 1 : 1;
        $offset = isset($params["iDisplayStart"]) ? (int)$params["iDisplayStart"] : 0;
        $columns = isset($params["iColumns"]) ? (int)$params["iColumns"] : null;
        $limit = isset($params['iDisplayLength']) ? (int)$params['iDisplayLength'] : 10;
        $search = isset($params['sSearch']) ? $params['sSearch'] : "";

        $queryArgs = array(
            "posts_per_page" => $limit,
            "paged" => $pageIndex,
            "page" => $pageIndex,
            "offset" => $offset,
            "s" => $search
        );
        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return $result;
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData();
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

    public static function handlePageReportDataTable($request) {
        $params = $request->get_params();

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

        $author = $params["author"];

        $queryArgs = array(
            "posts_per_page" => $limit,
            "paged" => $pageIndex,
            "page" => $pageIndex,
            "offset" => $offset,
            "author" => $author,
            "s" => $search
        );
        $queryProducts = StoreHL::instance()->queryStoreProducts($queryArgs);

        // Nếu không có bài viết return luôn
        if (!$queryProducts->have_posts()) {
            return $result;
        }

        $request_report_domain = StoreHLGA4::instance()->RequestReportSummaryData();
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
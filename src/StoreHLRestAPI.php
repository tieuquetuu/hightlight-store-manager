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

        $dimension_hostName_key = "hostName";
        $dimension_pagePath_key = "pagePath";
        $dimension_pageTitle_key = "pageTitle";
        $dimension_eventName_key = "eventName";

        $metric_eventCount_key = "eventCount";
        $metric_activeUsers_key = "activeUsers";
        $metric_screenPageViews_key = "screenPageViews";
        $metric_averageSessionDuration_key = "averageSessionDuration";

        $dimension_query_args = array(
            $dimension_hostName_key,
            $dimension_pagePath_key,
            $dimension_pageTitle_key,
            $dimension_eventName_key
        );
        $metric_query_args = array(
            $metric_eventCount_key,
            $metric_activeUsers_key,
            $metric_screenPageViews_key,
            $metric_averageSessionDuration_key
        );

        $dimension_eventName_key_index  = array_search($dimension_eventName_key, $dimension_query_args);
        $dimension_pagePath_key_index  = array_search($dimension_pagePath_key, $dimension_query_args);
        $dimension_pageTitle_key_index  = array_search($dimension_pageTitle_key, $dimension_query_args);
        $dimension_hostName_key_index = array_search($dimension_hostName_key, $dimension_query_args);

        $metric_activeUsers_key_index = array_search($metric_activeUsers_key, $metric_query_args);
        $metric_eventCount_key_index = array_search($metric_eventCount_key, $metric_query_args);
        $metric_screenPageViews_key_index = array_search($metric_screenPageViews_key, $metric_query_args);
        $metric_averageSessionDuration_key_index = array_search($metric_averageSessionDuration_key, $metric_query_args);

        $report = StoreHLGA4::instance()->GArunReport([
            'dimensions' => $dimension_query_args,
            'metrics' => $metric_query_args
        ]);

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
            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;

            $analytics = null;

            // Lọc dữ liệu google analytics bằng tiêu đề
            $analytics_filter = array_filter(
                $rowsData,
                function (
                    $row
                ) use (
                    &$dimension_query_args,
                    &$metric_query_args,
                    &$dimension_pagePath_key,
                    &$dimension_pageTitle_key,
                    &$dimension_pageTitle_key_index,
                    &$productTitle
                ) {
                    return str_contains($row->dimensionValues[$dimension_pageTitle_key_index]->value, $productTitle);
            });

            $analytics = array_values($analytics_filter);
            $analytics_data = array();

            foreach ($analytics as $item) {
                $rowItem = array();
                $dimensionValues = $item->dimensionValues;
                $metricValues = $item->metricValues;

                $rowItem[$dimension_hostName_key] = $dimensionValues[$dimension_hostName_key_index]->value;
                $rowItem[$dimension_pagePath_key] = $dimensionValues[$dimension_pagePath_key_index]->value;
                $rowItem[$dimension_eventName_key] = $dimensionValues[$dimension_eventName_key_index]->value;
                $rowItem[$dimension_pageTitle_key] = $dimensionValues[$dimension_pageTitle_key_index]->value;

                $rowItem[$metric_activeUsers_key] = $metricValues[$metric_activeUsers_key_index]->value;
                $rowItem[$metric_eventCount_key] = $metricValues[$metric_eventCount_key_index]->value;
                $rowItem[$metric_screenPageViews_key] = $metricValues[$metric_screenPageViews_key_index]->value;
                $rowItem[$metric_averageSessionDuration_key] = $metricValues[$metric_averageSessionDuration_key_index]->value;

                array_push($analytics_data,$rowItem);
            }

            /*foreach ($analytics as $analytic_item) {
                $domainKeyName = $analytic_item->dimensionValues[$dimension_hostName_key_index]->value;
                if (!key_exists($domainKeyName, $analytics_data)) {
                    $analytics_data[$domainKeyName] = array(
                        "luotXem" => 0,
                        "luotClickCuaHang" => 0,
                        "luotClickMuaHang" => 0,
                        "thoiGianXemTrungBinh" => 0
                    );
                }

                $analytics_data[$domainKeyName]["luotXem"] += $analytic_item->metricValues[$metric_screenPageViews_key_index];

                if ($analytic_item->dimensionValues[$dimension_eventName_index]->value == "click_view_shop") {
                    $analytics_data[$domainKeyName]["luotClickCuaHang"] += $analytic_item->metricValues[$metric_eventCount_key_index];
                }

                if ($analytic_item->dimensionValues[$dimension_eventName_index]->value == "click_buy_product") {
                    $analytics_data[$domainKeyName]["luotClickMuaHang"] += $analytic_item->metricValues[$metric_eventCount_key_index];
                }

                $analytics_data[$domainKeyName]["luotClickMuaHang"] += $analytic_item->metricValues[$metric_screenPageViews_key_index];

            }*/

            $row = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => "",
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "status" => $status,
                "product" => $product,
                "analytics" => $analytics,
                "analytics_data" => $analytics_data
            );
            array_push($result['data'], $row);
        }

        /*$result["data"] = array_map(function($product) {
            $author = get_user_by("id", $product->post_author);
            $productTitle = $product->post_title;
            $productId = $product->ID;
            $status = "Chờ duyệt";
            if ($product->post_status == "publish") : $status = "Đang hoạt động"; endif;

            $result = array(
                "id" => $productId,
                "title" => $productTitle,
                "category" => "",
                "author" => array(
                    "id" => $author->ID,
                    "display_name" => $author->display_name
                ),
                "analystic_report" => array(
                    "total_screenPageViews" => 1000,
                    "total_click_buy_product" => 100,
                    "total_click_view_shop" => 80,
                    "total_averageSessionDuration" => "30 giây"
                ),
                "status" => $status,
                "product" => $product
            );
            return $result;
        },$queryProducts->posts);*/

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

        register_rest_route('hightlight/v1', '/reportDataTable', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'handleDataTableReportGA4')
        ));
    }
}
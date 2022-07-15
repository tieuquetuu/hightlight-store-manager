<?php

namespace HightLightStore;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\Filter\InListFilter;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Google\Analytics\Data\V1beta\Row;

class StoreHLGA4 {
    private static $instance = NULL;

    protected static $credentials = NULL;

    protected static $properties = NULL;

    protected static $client = NULL;

    public static function instance() {
        if ( ! isset( self::$instance ) || ! ( self::$instance instanceof StoreHLGA4 ) ) {
            self::$instance = new StoreHLGA4();
//            self::$instance->setup_constants();
            /*if ( self::$instance->includes() ) {
                self::$instance->actions();
                self::$instance->filters();
            }*/
        }

        /**
         * Return the HLStore Instance
         */
        return self::$instance;
    }

    public static function credentials() {
        return STORE_HIGHT_LIGHT_GOOGLE_CREDENTIALS;
    }

    public static function public_credentials() {
        return STORE_HIGHT_LIGHT_GOOGLE_CREDENTIALS;
    }

    public static function client() {
        if (self::$client == NULL) {
            self::$client = new BetaAnalyticsDataClient(
                array(
                    'credentials' => self::credentials()
                )
            );
        }

        /**
         * Fire an action when the Schema is returned
         */
        do_action( 'store_hl_get_google_client', self::$client );

        /**
         * Return the Google Analystic Client after applying filters
         */
        return ! empty( self::$client ) ? self::$client : null;
    }

    public static function properties() {
        if (self::$properties == NULL) {
            self::$properties = STORE_HIGHT_LIGHT_GOOGLE_ANALYSTIC_PROPERTIES;
        }

        /**
         * Fire an action when the Properties is returned
         */
        do_action( 'store_hl_get_google_properties', self::$properties );

        /**
         * Return the Google Analystic Properties after applying filters
         */
        return ! empty( self::$properties ) ? self::$properties : null;
    }

    public static function reportByProductSlug($args) {
        $args = is_array($args) ? $args : null;

        if (!$args) {
            return null;
        }
        $slug = isset($args['slug']) && is_string($args['slug']) && strlen($args['slug']) > 0 ? $args['slug'] : false;

        $data = array();
        $result = array(
            "slug" => $slug,
        );
        $date_ranges = [
            new DateRange([
                'start_date' => '30daysAgo',
                'end_date' => 'today',
            ]),
        ];
        $limit = 1000;
        $offset = 0;

        $batchResponse = self::client()->batchRunReports([
            'property' => 'properties/' . self::properties(),
            'requests' => [
                new RunReportRequest([
                    'property' => 'properties/' . self::properties(),
                    'date_ranges' => $date_ranges,
                    'dimensions' => [
                        new Dimension(
                            [
                                'name' => 'eventName',
                            ],
                        ),
                    ],
                    'metrics' => [
                        new Metric(
                            [
                                'name' => 'eventCount', // Tổng số sự kiện
                            ],
                        ),
                    ],
                    'dimension_filter' => new FilterExpression(
                        [
                            'and_group' => new FilterExpressionList(
                                array(
                                    'expressions' => [
                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "pagePath",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => $slug,
                                                                'match_type' => MatchType::CONTAINS,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        ),

                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "eventName",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => 'page_view',
                                                                'match_type' => MatchType::EXACT,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        )
                                    ]
                                )
                            ),
                        ]
                    ),
                    'limit' => $limit,
                    'offset' => $offset
                ]) , // Lấy danh sách lượt xem

                new RunReportRequest([
                    'property' => 'properties/' . self::properties(),
                    'date_ranges' => $date_ranges,
                    'dimensions' => [
                        new Dimension(
                            [
                                'name' => 'eventName',
                            ],
                        ),
                    ],
                    'metrics' => [
                        new Metric(
                            [
                                'name' => 'eventCount', // Tổng số sự kiện
                            ],
                        ),
                    ],
                    'dimension_filter' => new FilterExpression(
                        [
                            'and_group' => new FilterExpressionList(
                                array(
                                    'expressions' => [
                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "pagePath",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => $slug,
                                                                'match_type' => MatchType::CONTAINS,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        ),

                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "eventName",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => 'click_buy_product',
                                                                'match_type' => MatchType::EXACT,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        )
                                    ]
                                )
                            ),
                        ]
                    ),
                    'limit' => $limit,
                    'offset' => $offset
                ]) , // Lấy lượt click button mua hàng

                new RunReportRequest([
                    'property' => 'properties/' . self::properties(),
                    'date_ranges' => $date_ranges,
                    'dimensions' => [
                        new Dimension(
                            [
                                'name' => 'eventName',
                            ],
                        ),
                    ],
                    'metrics' => [
                        new Metric(
                            [
                                'name' => 'eventCount', // Tổng số sự kiện
                            ],
                        ),
                    ],
                    'dimension_filter' => new FilterExpression(
                        [
                            'and_group' => new FilterExpressionList(
                                array(
                                    'expressions' => [
                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "pagePath",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => $slug,
                                                                'match_type' => MatchType::CONTAINS,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        ),

                                        new FilterExpression(
                                            [
                                                'filter' => new Filter(
                                                    [
                                                        'field_name' => "eventName",
                                                        'string_filter' => new StringFilter(
                                                            [
                                                                'value' => 'click_view_shop',
                                                                'match_type' => MatchType::EXACT,
                                                            ]
                                                        )
                                                    ]
                                                )
                                            ]
                                        )
                                    ]
                                )
                            ),
                        ]
                    ),
                    'limit' => $limit,
                    'offset' => $offset
                ]) , // Lấy lượt click button cửa hàng

//                new RunReportRequest([
//                    'property' => 'properties/' . HLSM_GOOGLE_ANALYSTIC_PROPERTY,
//                    'date_ranges' => $date_ranges,
//                    'dimensions' => [
//                        new Dimension(
//                            [
//                                'name' => 'eventName',
//                            ],
//                        ),
//                    ],
//                    'metrics' => [
//                        new Metric(
//                            [
//                                'name' => 'averageSessionDuration', // Thời lượng trung bình (tính bằng giây) trong các phiên của người dùng.
//                            ],
//                        ),
//                    ],
//                    'dimension_filter' => new FilterExpression(
//                        [
//                            'and_group' => new FilterExpressionList(
//                                array(
//                                    'expressions' => [
//                                        new FilterExpression(
//                                            [
//                                                'filter' => new Filter(
//                                                    [
//                                                        'field_name' => "pagePath",
//                                                        'string_filter' => new StringFilter(
//                                                            [
//                                                                'value' => $slug,
//                                                                'match_type' => MatchType::CONTAINS,
//                                                            ]
//                                                        )
//                                                    ]
//                                                )
//                                            ]
//                                        ),
//
//                                        /*new FilterExpression(
//                                            [
//                                                'filter' => new Filter(
//                                                    [
//                                                        'field_name' => "eventName",
//                                                        'in_list_filter' => new InListFilter(
//                                                            [
//                                                                'values' => ["page_view", "user_engagement", "scroll"]
//                                                            ]
//                                                        )
//                                                    ]
//                                                )
//                                            ]
//                                        )*/
//                                    ]
//                                )
//                            ),
//                        ]
//                    ),
//                    'limit' => $limit,
//                    'offset' => $offset
//                ]) , // Lấy thời gian xem trang trung bình
            ]
        ]);

        foreach ($batchResponse->getReports() as $reportKey => $report) {

            $report_total_rows = $report->getRowCount();
            $report_rows = $report->getRows();
            $report_json = json_decode($report->serializeToJsonString());
            $report_data = array();

            if ($report_total_rows == 1) {
                foreach ($report_rows as $report_row) {
                    $report_obj_key_name = $report_row->getDimensionValues()[0]->getValue();
                    $report_obj_value = $report_row->getMetricValues()[0]->getValue();
                    $report_data[$report_obj_key_name] = $report_obj_value;
                    $data = array_merge($data, $report_data);
                }
            }
        }

        // Set up data for result
        if ($data) :
            $result['data'] = json_decode(json_encode($data));
        endif;

        return $result;
    }
}
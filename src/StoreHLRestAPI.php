<?php

namespace StoreHightLight;

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

    public static function HandleHightLightRootApi() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function init_actions() {
        register_rest_route('highlight/v1', '/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'HandleHightLightRootApi')
        ));
    }
}
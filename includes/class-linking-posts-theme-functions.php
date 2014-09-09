<?php

class Linking_Posts_Theme_Functions {

    function __construct() { }

    public static function  define_theme_functions() {
        if( ! function_exists( 'lps_get_related_posts' ) ) {
            function lps_get_related_posts( $post ) {
                $lps_data_model = Linking_Posts_Model::getInstance();
                return $lps_data_model->get_related_posts( $post );
            }
        }

        if( ! function_exists( 'lps_get_possible_linking_posts' ) ) {
            function lps_get_possible_linking_posts( $post_type, $valid_post_status = array(), $single_reference = false ) {
                $lps_data_model = Linking_Posts_Model::getInstance();
                return $lps_data_model->get_possible_linking_posts( $post_type, $valid_post_status, $single_reference );
            }
        }

        if( ! function_exists( 'lps_get_linking_posts' ) ) {
            function lps_get_linking_posts( $post ) {
                $lps_data_model = Linking_Posts_Model::getInstance();
                return $lps_data_model->get_linking_posts( $post );
            }
        }

    }
} 
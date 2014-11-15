<?php
class Linking_Posts_Model {

    private static $_instance = null;

    private function __construct() { }
    private function  __clone() { }

    public static function getInstance() {
        if( !is_object(self::$_instance) )
            self::$_instance = new Linking_Posts_Model();
        return self::$_instance;
    }

    public function get_related_posts( $post ) {

        $args = array(
            'post_type' => $post->post_type,
            'post_status' => 'any',
        );

        add_filter('posts_fields', array( $this, 'posts_fields_filter_related_posts' ) );
        add_filter('posts_join', array( $this, 'posts_join_filter_related_posts' ) );
        add_filter('posts_where', array( $this, 'posts_where_filter_related_posts' ) );

        $linked_posts = new WP_Query($args);

        remove_filter('posts_fields', array( $this, 'posts_fields_filter_related_posts' ) );
        remove_filter('posts_join', array( $this, 'posts_join_filter_related_posts' ) );
        remove_filter('posts_where', array( $this, 'posts_where_filter_related_posts' ) );

        usort( $linked_posts->posts, array($this, 'issue_articles_order_compare') );

        return $linked_posts;
    }

    public function get_possible_linking_posts( $post_type, $valid_post_status = array(), $single_reference = false ) {

        $args = array(
            'post_type' => $post_type,
            'post_status' => $valid_post_status,
        );

        add_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts' ) );
        if( $single_reference ) {
            add_filter('posts_fields', array( $this, 'posts_fields_filter_possible_linking_posts_single_reference' ) );
            add_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts_single_reference' ) );
            add_filter('posts_join', array( $this, 'posts_join_filter_possible_linking_posts_single_reference' ) );
            add_filter( 'posts_groupby', array( $this, 'posts_groupby_filter_possible_linking_posts_single_reference' ) );
        }
        $possible_linking_posts = new WP_Query($args);
        remove_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts' ) );
        if( $single_reference )  {
            remove_filter('posts_fields', array( $this, 'posts_fields_filter_possible_linking_posts_single_reference' ) );
            remove_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts_single_reference' ) );
            remove_filter('posts_join', array( $this, 'posts_join_filter_possible_linking_posts_single_reference' ) );
            remove_filter( 'posts_groupby', array( $this, 'posts_groupby_filter_possible_linking_posts_single_reference' ) );
        }

        return $possible_linking_posts;

    }

    public function get_linking_posts( $post ) {

        $args = array(
            'post_type' => $post->post_type,
            'post_status' => 'any',
        );

        add_filter('posts_fields', array( $this, 'posts_fields_filter_linking_posts' ) );
        add_filter('posts_join', array( $this, 'posts_join_filter_linking_posts' ) );
        add_filter('posts_where', array( $this, 'posts_where_filter_linking_posts' ) );
        $linking_posts = new WP_Query($args);
        remove_filter('posts_fields', array( $this, 'posts_fields_filter_linking_posts' ) );
        remove_filter('posts_join', array( $this, 'posts_join_filter_linking_posts' ) );
        remove_filter('posts_where', array( $this, 'posts_where_filter_linking_posts' ) );

        return $linking_posts;
    }

    public function posts_fields_filter_related_posts( $fields ) {
        global $table_prefix, $wpdb;
        //$fields .= ", " . $table_prefix . "related_posts.order as article_order, related_post_details.ID as related_post_ID, related_post_details.post_title as related_post_title, related_post_details.post_name as related_post_slug";
        $fields = " related_post_details.*, " . $table_prefix . "related_posts.order as article_order ";
        return ($fields);
    }

    public function posts_fields_filter_linking_posts( $fields ) {
        global $table_prefix, $wpdb;
//        $fields .= ", related_post_details.ID as related_post_ID, related_post_details.post_title as related_post_title, related_post_details.post_name as related_post_slug";
        $fields = " related_post_details.*, " . $table_prefix . "related_posts.order as article_order ";
        return ($fields);
    }

    public function posts_fields_filter_possible_linking_posts_single_reference( $fields ) {
        global $table_prefix, $wpdb;
        $fields .= ", " . $table_prefix . "related_posts.related_post_id as related_post_ID";
        return ($fields);
    }

    public function posts_join_filter_related_posts( $join ) {
        global $table_prefix, $wpdb;
        $join .=
            "
              LEFT JOIN " . $table_prefix . "related_posts
                ON (" . $table_prefix . "related_posts.linking_post_id = $wpdb->posts.ID)
              LEFT JOIN " . $table_prefix . "posts as related_post_details
                ON (" . $table_prefix . "related_posts.related_post_id = related_post_details.ID)
            ";
        return $join;
    }

    public function posts_join_filter_linking_posts( $join ) {
        global $table_prefix, $wpdb;
        $join .=
            "
              LEFT JOIN " . $table_prefix . "related_posts
                ON (" . $table_prefix . "related_posts.related_post_id = $wpdb->posts.ID)
              LEFT JOIN " . $table_prefix . "posts as related_post_details
                ON (" . $table_prefix . "related_posts.linking_post_id = related_post_details.ID)
            ";
        return $join;
    }

    public function posts_join_filter_possible_linking_posts_single_reference( $join ) {
        global $table_prefix, $post, $wpdb;
        $join .=
            "
              LEFT JOIN " . $table_prefix . "related_posts
                ON (" . $table_prefix . "related_posts.related_post_id = $wpdb->posts.ID)
            ";
        return $join;
    }


    public function posts_where_filter_related_posts( $where ) {
        global $post, $table_prefix, $wpdb;
        $where .= " AND " . $table_prefix . "related_posts.linking_post_id = " . $post->ID;
        return $where;
    }

    public function posts_where_filter_linking_posts( $where ) {
        global $post, $table_prefix, $wpdb;
        $where .= " AND " . $table_prefix . "related_posts.related_post_id = $post->ID";
        $where .= " AND " . $table_prefix . "posts.ID = $post->ID";
        return $where;
    }

    public function posts_where_filter_possible_linking_posts( $where ) {
        global $post, $wpdb;
        $where .= " AND $wpdb->posts.id != " . $post->ID;
        if ( $data = wp_cache_get( $post->ID, 'linked_posts_ids' ) ) {
            $where .= " AND $wpdb->posts.id NOT IN ( " . implode( ',', $data ) . ")";
        }
        return $where;
    }

    public function posts_where_filter_possible_linking_posts_single_reference( $where ) {
        global $post, $wpdb;
        $where .= " AND related_post_ID is NULL ";
        return $where;
    }

    public function posts_groupby_filter_possible_linking_posts_single_reference( $groupby ) {
        global $wpdb;
        $groupby = "{$wpdb->posts}.ID";
        return $groupby;
    }

    function update_related_posts_orders( $linking_post_id = null, $ordered_related_posts_ids = array() ) {
        global $table_prefix, $wpdb; // this is how you get access to the database

        $order = 0;

        foreach( $ordered_related_posts_ids as $related_post_id ) {
            $order++;
            $wpdb->replace(
                $table_prefix . 'related_posts',
                array(
                    'linking_post_id' => $linking_post_id,
                    'related_post_id' => $related_post_id,
                    'order' => $order,
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                )
            );
        }

        return true;

    }

    function add_related_post( $linking_post_id = null, $related_post_id = null, $order = 0 ) {
        global $table_prefix, $wpdb; // this is how you get access to the database

        $wpdb->replace(
            $table_prefix . 'related_posts',
            array(
                'linking_post_id' => $linking_post_id,
                'related_post_id' => $related_post_id,
                'order' => $order,
            ),
            array(
                '%d',
                '%d',
                '%d',
            )
        );

        return true;
    }

    function remove_related_post( $linking_post_id = null, $related_post_id = null ) {
        global $table_prefix, $wpdb; // this is how you get access to the database

        $wpdb->delete(
            $table_prefix . 'related_posts',
            array(
                'linking_post_id' => $linking_post_id,
                'related_post_id' => $related_post_id,
            ),
            array(
                '%d',
                '%d',
            )
        );

        return true;

    }

    function issue_articles_order_compare($a, $b){
        return $a->article_order - $b->article_order;
    }

} 
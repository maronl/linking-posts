<?php

class Linking_Posts_Manager_Admin extends Linking_Posts_Options {

    private $version;

    private $options;

    private $related_posts_connections = array( 'post' => 'post' );

    private  $linking_posts_connections = array( 'post' => 'post' );

    private $related_posts_valid_status = array( 'any' );

    function __construct($version)
    {
        $this->version = $version;
    }

    function install_db_structure() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'related_posts';

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE $table_name (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `linking_post_id` bigint(20) unsigned NOT NULL,
          `related_post_id` bigint(20) unsigned NOT NULL,
          `order` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `linking_post_id` (`linking_post_id`),
          KEY `related_post_id` (`related_post_id`)
	    ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql );

        add_option( 'linking_posts_db_version', $this->version );

    }

    public function register_scripts() {
        parent::register_scripts();
        wp_register_script( 'linking-posts-admin-js', plugins_url( 'js/linking-posts-admin.js', __FILE__ ), array( 'jquery-ui-sortable' ) );
    }

    public function register_styles() {
        parent::register_styles();
        wp_register_style( 'linking-posts-admin-css', plugins_url( 'css/secure-attachments-admin.css', __FILE__  ) );
    }

    public function enqueue_styles($hook) {
        parent::enqueue_styles($hook);
        if( $this->is_related_posts_metabox_enabled($hook) ) {
            wp_enqueue_style( 'secure-attachments-admin-css', false, array(), $this->version );
       }
    }

    public function enqueue_scripts($hook) {
        parent::enqueue_scripts($hook);
        if( $this->is_related_posts_metabox_enabled($hook) ) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('linking-posts-admin-js');
        }
    }

    function is_related_posts_metabox_enabled( $hook ) {
        global $post_type;
        if ( is_admin()
            && ( 'post.php' == $hook || 'post-new.php' == $hook )
            && isset($this->related_posts_connections[$post_type])
        ) {
            return true;
        } else {
            return false;
        }
    }

    function add_meta_box_related_posts() {

        global $post_type;

        if( isset($this->related_posts_connections[$post_type] ) ) {
            add_meta_box(
                'related_posts_list',
                __("Related Posts", 'linking-posts'),
                array($this, 'render_meta_box_related_posts'),
                $this->related_posts_connections[$post_type]
            );
        }

    }

    function add_meta_box_linking_posts() {

        global $post_type;

        if( isset($this->linking_posts_connections[$post_type] ) ) {
            add_meta_box(
                'linking_posts_list',
                __("Linking Posts", 'linking-posts'),
                array($this, 'render_meta_box_linking_posts'),
                $this->linking_posts_connections[$post_type]
            );
        }

    }

    function render_meta_box_related_posts( $post ) {

        global $post;
        $current_post = $post;

        $args = array(
            'post_type' => $this->related_posts_connections[$post->post_type],
            'post_status' => $this->related_posts_valid_status,
        );

        wp_nonce_field( 'linking_posts_meta_box', 'linking_posts_meta_box_nonce' );

        add_filter('posts_fields', array( $this, 'posts_fields_filter_related_posts' ) );
        add_filter('posts_join', array( $this, 'posts_join_filter_related_posts' ) );
        add_filter('posts_where', array( $this, 'posts_where_filter_related_posts' ) );

        $linked_posts = new WP_Query($args);

        remove_filter('posts_fields', array( $this, 'posts_fields_filter_related_posts' ) );
        remove_filter('posts_join', array( $this, 'posts_join_filter_related_posts' ) );
        remove_filter('posts_where', array( $this, 'posts_where_filter_related_posts' ) );

        echo '<ul id="sortable">';

        $linked_posts_ids = array();

        usort( $linked_posts->posts, array($this, 'issue_articles_order_compare') );

        while ( $linked_posts->have_posts() ) :

            $linked_posts->the_post();

            echo '<li id="linked_posts_orders_'.$post->related_post_ID.'" class="ui-state-default"><div class="dashicons dashicons-sort"></div><a href="/wp-admin/post.php?post='.$post->related_post_ID.'&action=edit">'.$post->related_post_title.'</a> - <a class="remove-post-link" href="#'.$post->related_post_ID.'">remove</a></li>';

            $linked_posts_ids[] = $post->related_post_ID;

        endwhile;

        wp_cache_add( $post->ID, $linked_posts_ids, 'linked_posts_ids' );

        echo '</ul>';

        echo '<hr>';

        add_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts' ) );
        $possible_linking_posts = new WP_Query($args);
        remove_filter('posts_where', array( $this, 'posts_where_filter_possible_linking_posts' ) );

        echo '<h4>Add related post</h4>';

        echo '<select id="possible-related-posts">';

        echo '<option>Select a post</option>';

        while ( $possible_linking_posts->have_posts() ) :

            $possible_linking_posts->the_post();

            echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';

        endwhile;

        echo '</select>';

        echo '<a id="add-article-issue" class="button button-primary">Add Post</a>';

        echo '<hr>';

        echo '<a id="save-issue-menu" class="button button-primary">Save Related Posts</a>';

        $post = $current_post;

        ?>

        <script>



        </script>
    <?php

    }

    function render_meta_box_linking_posts( $post ) {

        global $post;
        $current_post = $post;

        $args = array(
            'post_type' => $this->linking_posts_connections[$post->post_type],
            'post_status' => $this->related_posts_valid_status,
        );

        add_filter('posts_fields', array( $this, 'posts_fields_filter_linking_posts' ) );
        add_filter('posts_join', array( $this, 'posts_join_filter_linking_posts' ) );
        add_filter('posts_where', array( $this, 'posts_where_filter_linking_posts' ) );
        $linking_posts = new WP_Query($args);
        remove_filter('posts_fields', array( $this, 'posts_fields_filter_linking_posts' ) );
        remove_filter('posts_join', array( $this, 'posts_join_filter_linking_posts' ) );
        remove_filter('posts_where', array( $this, 'posts_where_filter_linking_posts' ) );

        echo '<ul>';

        while ( $linking_posts->have_posts() ) :

            $linking_posts->the_post();

            echo '<li><a href="/wp-admin/post.php?post='.$post->related_post_ID.'&action=edit">'.$post->related_post_title.'</a></li>';

        endwhile;

        echo '</ul>';

        $post = $current_post;

    }

    function update_ajax_related_posts_orders() {
        global $table_prefix, $wpdb; // this is how you get access to the database

        if( $this->update_related_posts_orders( $_POST['post_ID'], $_POST['linked_posts_orders'] ) ){
            echo 1;
        }else{
            echo 0;
        }

        die();

    }

    function add_ajax_related_post() {
        global $table_prefix, $wpdb; // this is how you get access to the database

        if( $this->add_related_post( $_POST['post_ID'], $_POST['new_related_post_ID'], $_POST['new_related_post_order'] ) ) {
            $res = array(
                'status' => 1,
                'data' => array(
                    'id' => $_POST['new_related_post_ID'],
                    'title' => $_POST['new_related_post_title'],
                ),
            );
            echo json_encode( $res );
        }else{
            echo 0;
        }

        die();

    }

    function remove_ajax_related_post() {
        global $table_prefix, $wpdb; // this is how you get access to the database

        if( $this->remove_related_post( $_POST['post_ID'], $_POST['related_post_ID'] ) ) {
            $res = array(
                'status' => 1,
                'data' => array(
                    'post_ID' => $_POST['post_ID'],
                    'related_post_ID' => $_POST['related_post_ID'],
                ),
            );
            echo json_encode( $res );
        }else{
            echo 0;
        }

        die();

    }

    public function posts_fields_filter_related_posts( $fields ) {
        global $table_prefix, $wpdb;
        $fields .= ", " . $table_prefix . "related_posts.order as article_order, related_post_details.ID as related_post_ID, related_post_details.post_title as related_post_title, related_post_details.post_name as related_post_slug";
        return ($fields);
    }

    public function posts_fields_filter_linking_posts( $fields ) {
        global $table_prefix, $wpdb;
        $fields .= ", related_post_details.ID as related_post_ID, related_post_details.post_title as related_post_title, related_post_details.post_name as related_post_slug";
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

    public function posts_where_filter_related_posts( $where ) {
        global $post, $table_prefix, $wpdb;
        $where .= " AND " . $table_prefix . "related_posts.linking_post_id = " . $post->ID;
        return $where;
    }

    public function posts_where_filter_linking_posts( $where ) {
        global $post, $table_prefix, $wpdb;
        $where .= " AND " . $table_prefix . "related_posts.related_post_id = $post->ID";
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
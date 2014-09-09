<?php

class Linking_Posts_Manager_Admin extends Linking_Posts_Options {

    private $version;

    private $related_posts_connections = array();

    private $linking_posts_connections = array();

    private $related_posts_valid_status = array( 'any' );

    private $data_model;

    function __construct($version, $data_model)
    {
        parent::__construct();
        $this->version = $version;
        $this->data_model = $data_model;
        $link_settings = $this->options['linking-settings'];
        if( ! empty( $link_settings ) ) {
            $link_settings = explode( ';', $link_settings );
        }else{
            $link_settings = array();
        }
        foreach( $link_settings as $link_setting ) {
            $link_setting_values = explode( ',', $link_setting );
            $this->related_posts_connections[$link_setting_values[0]] = $link_setting_values[1];
            $this->linking_posts_connections[$link_setting_values[1]] = $link_setting_values[0];
        }
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
                $post_type
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
                $post_type
            );
        }

    }

    function render_meta_box_related_posts( $post ) {

        global $post;
        $current_post = $post;

        $linked_posts = $this->data_model->get_related_posts( $post );

        wp_nonce_field( 'linking_posts_meta_box', 'linking_posts_meta_box_nonce' );

        echo '<ul id="sortable">';

        $linked_posts_ids = array();

        while ( $linked_posts->have_posts() ) :

            $linked_posts->the_post();

            echo '<li id="linked_posts_orders_'.$post->ID.'" class="ui-state-default"><div class="dashicons dashicons-sort"></div><a href="/wp-admin/post.php?post='.$post->ID.'&action=edit">'.$post->post_title.'</a> - <a class="remove-post-link" href="#'.$post->ID.'">remove</a></li>';

            $linked_posts_ids[] = $post->related_post_ID;

        endwhile;

        wp_cache_add( $post->ID, $linked_posts_ids, 'linked_posts_ids' );

        echo '</ul>';

        echo '<hr>';

        $post = $current_post;

        $ppost_type = $this->related_posts_connections[$post->post_type];
        $pvalid_status = $this->related_posts_valid_status;
        $psingle_reference = false;
        if( isset( $this->options['single-reference'] ) && ( $this->options['single-reference'] == 1 ) ) {
            $psingle_reference = true;
        }
        $possible_linking_posts = $this->data_model->get_possible_linking_posts( $ppost_type, $pvalid_status, $psingle_reference );

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

        $linking_posts = $this->data_model->get_linking_post( $post, $this->related_posts_valid_status );

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

        if( $this->data_model->update_related_posts_orders( $_POST['post_ID'], $_POST['linked_posts_orders'] ) ){
            echo 1;
        }else{
            echo 0;
        }

        die();

    }

    function add_ajax_related_post() {
        global $table_prefix, $wpdb; // this is how you get access to the database

        if( $this->data_model->add_related_post( $_POST['post_ID'], $_POST['new_related_post_ID'], $_POST['new_related_post_order'] ) ) {
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

        if( $this->data_model->remove_related_post( $_POST['post_ID'], $_POST['related_post_ID'] ) ) {
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

}
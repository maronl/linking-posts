<?php

class Linking_Posts_Options {

    protected $options;

    function __construct()
    {
        $this->options = get_option( 'linking-posts-options' );
    }

    public function register_scripts() {
        wp_register_script( 'linking-posts-admin-options-js', plugins_url( 'js/linking-posts-admin-options.js', __FILE__ ), array( 'jquery-ui-sortable' ) );
    }

    public function register_styles() {

    }

    public function enqueue_styles($hook) {

    }

    public function enqueue_scripts($hook) {
        if( 'settings_page_linking-posts-plugin-options' == $hook ){
            wp_enqueue_script('linking-posts-admin-options-js');
        }
    }

    function add_plugin_options_page() {
        add_options_page(
            'Linking Posts Plugin Options',
            __('Linking Posts', 'linking-posts'),
            'manage_options',
            'linking-posts-plugin-options',
            array( $this, 'render_admin_options_page' )
        );
    }

    function render_admin_options_page()
    {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Linking Posts Plugin Options', 'linking-posts' )?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'linking-posts-options' );
                do_settings_sections( 'linking-posts-options' );
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    function options_page_init()
    {
        register_setting(
            'linking-posts-options', // Option group
            'linking-posts-options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'linking-posts-options', // ID
            'Linking Posts Options', // Title
            array( $this, 'print_section_info' ), // Callback
            'linking-posts-options' // Page
        );

        add_settings_field(
            'single-reference', // ID
            'Single Reference', // Title
            array( $this, 'single_reference_callback' ), // Callback
            'linking-posts-options', // Page
            'linking-posts-options' // Section
        );

        add_settings_field(
            'linking-settings',
            'Linking Settings',
            array( $this, 'linking_settings_callback' ),
            'linking-posts-options',
            'linking-posts-options'
        );

    }

    function sanitize( $input )
    {
        return $input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        _e( 'Enter your settings below:', 'linking-posts' );
    }

    public function single_reference_callback()
    {
        $checked =( isset( $this->options['single-reference'] ) && $this->options['single-reference'] == 1 ) ? 'checked' : '';
        $description = '<p class="description">' . __('check to allow a post to be linked only by one post.', 'linking-posts') . '</p>';
        printf(
            '<input type="checkbox" id="single-reference" name="linking-posts-options[single-reference]" value="1" %s  autocomplete="off"/><label for="single-reference">Single Reference</label>%s',
            $checked,
            $description
        );
    }

    public function linking_settings_callback()
    {
        $value = isset( $this->options['linking-settings'] ) ? esc_attr( $this->options['linking-settings']) : '';
        //$linking_post_types = array();
        //$linked_post_types = array();
        $link_settings = array();
        if( ! empty($value) ){
            $link_settings = explode( ';', $value );
        }
        $class_no_settings = ( empty( $link_settings ) ) ? '' : 'class="hidden"';
        ?>

        <input id="linking-settings" name="linking-posts-options[linking-settings]" type="hidden" value="<?php echo $value; ?>" autocomplete="off">
        <ul id="linking-settings-list"">
            <li id="no-link-settings" <?php echo $class_no_settings; ?>>Nessuno</li>
            <?php
                foreach($link_settings as $link_setting){
                    $link_setting_values = explode( ',', $link_setting );
                    //$linking_post_types[] = $link_setting_values[0];
                    //$linked_post_types[] = $link_setting_values[1];
                    $format = '<li data-linking="%s" data-related="%s">%s => %s - <a href="#%s,%s" class="remove-linking-setting">remove</a></li>';
                    printf( $format, $link_setting_values[0], $link_setting_values[1], $link_setting_values[0], $link_setting_values[1], $link_setting_values[0], $link_setting_values[1]);
                }
            ?>
        </ul>

        <?php
            $linking_elements = $this->get_linking_post_types();
        ?>

        Linking element: <select id="linking-post-type">
            <?php foreach( $linking_elements as $linking_element ) {
                //if( ! in_array( $linking_element, $linking_post_types) ) {
                    $format = '<option value="%s">%s</option>';
                    printf( $format, $linking_element, $linking_element );
                //}
            } ?>
        </select>
        => Related element: <select id="related-post-type">
            <?php foreach( $linking_elements as $linked_element ) {
                //if( ! in_array( $linked_element, $linked_post_types) ) {
                    $format = '<option value="%s">%s</option>';
                    printf( $format, $linked_element, $linked_element );
                //}
            } ?>
        </select>
        <input id="add-linking-setting" type="button" class="button button-primary" value="Add Linking">

        <?php
    }

    function get_linking_post_types() {
        $linking_elements = get_post_types();
        unset($linking_elements['attachment']);
        unset($linking_elements['revision']);
        unset($linking_elements['nav_menu_item']);
        return $linking_elements;
    }

} 
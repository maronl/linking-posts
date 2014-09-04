<?php

/**
 * The Linking Posts Manager is the core plugin responsible for including and
 * instantiating all of the code that composes the plugin
 *
 */

/**
 * The Linking Posts Manager is the core plugin responsible for including and
 * instantiating all of the code that composes the plugin.
 *
 * The Linking Posts Manager includes an instance to the Linking Posts
 * Loader which is responsible for coordinating the hooks that exist within the
 * plugin.
 *
 * It also maintains a reference to the plugin slug which can be used in
 * internationalization, and a reference to the current version of the plugin
 * so that we can easily update the version in a single place to provide
 * cache busting functionality when including scripts and styles.
 *
 * @since 1.0.0
 */
class Linking_Posts_Manager {

    /**
     * A reference to the loader class that coordinates the hooks and callbacks
     * throughout the plugin.
     *
     * @access protected
     * @var Linking_Posts_Loader $loader Manages hooks between the WordPress hooks and the callback functions.
     */
    protected $loader;

    /**
     * Represents the slug of hte plugin that can be used throughout the plugin
     * for internationalization and other purposes.
     *
     * @access protected
     * @var string $plugin_slug The single, hyphenated string used to identify this plugin.
     */
    protected $plugin_slug;

    /**
     * Maintains the current version of the plugin so that we can use it throughout
     * the plugin.
     *
     * @access protected
     * @var string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Instantiates the plugin by setting up the core properties and loading
     * all necessary dependencies and defining the hooks.
     *
     * The constructor will define both the plugin slug and the verison
     * attributes, but will also use internal functions to import all the
     * plugin dependencies, and will leverage the Linking_Posts_Loader for
     * registering the hooks and the callback functions used throughout the
     * plugin.
     */
    public function __construct() {

        $this->plugin_slug = 'linking-posts';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }



    /**
     * Imports the Classes needed for the plugin.
     *
     * @access private
     */
    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-linking-posts-manager-admin.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-linking-posts-loader.php';
        $this->loader = new Linking_Posts_Loader();

    }

    /**
     * Defines the hooks and callback functions that are used for setting up the plugin stylesheets
     * and the plugin's meta box.
     *
     * This function relies on the Linking Posts Admin class and the Linking Posts Meta Manager
     * Loader class property.
     *
     * @access private
     */
    private function define_admin_hooks() {

        $admin = new Linking_Posts_Manager_Admin( $this->version );
        $this->loader->add_action( 'admin_init', $admin, 'register_scripts' );
        $this->loader->add_action( 'admin_init', $admin, 'register_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'wp_ajax_update_related_posts_orders', $admin, 'update_ajax_related_posts_orders' );
        $this->loader->add_action( 'wp_ajax_add_related_post', $admin, 'add_ajax_related_post' );
        $this->loader->add_action( 'wp_ajax_remove_related_post', $admin, 'remove_ajax_related_post' );
        $this->loader->add_action( 'add_meta_boxes', $admin, 'add_meta_box_related_posts' );
        $this->loader->add_action( 'add_meta_boxes', $admin, 'add_meta_box_linking_posts' );
        $this->loader->add_action( 'activate_linking-posts', $admin, 'install_db_structure' );
        register_activation_hook( dirname( dirname( __FILE__ ) ) . '\linking-posts.php' , array( $admin, 'install_db_structure' ) );
    }

    /**
     * Defines the hooks and callback functions that are used for rendering information on the front
     * end of the site.
     *
     * This function relies on the Linking Posts Public class and the Linking Posts Meta Manager
     * Loader class property.
     *
     * @access private
     */
    private function define_public_hooks() {

    }

    /**
     * Sets this class into motion.
     *
     * Executes the plugin by calling the run method of the loader class which will
     * register all of the hooks and callback functions used throughout the plugin
     * with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Returns the current version of the plugin to the caller.
     *
     * @return string $this->version The current version of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
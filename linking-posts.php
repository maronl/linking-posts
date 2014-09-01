<?php
/**
 * The file responsible for starting the Linking Post plugin
 *
 * The Linking Posts is a plugin that allow users to create connection between posts. like all the other related post plugins
 * but developing plugin as open source project, with testing in mind.
 *
 * @package SAMARONL
 *
 * @wordpress-plugin
 * Plugin Name: Linking Posts
 * Plugin URI: http://
 * Description: The Linking Posts is a plugin that allow users to create connection between posts. like all the other related post plugins but developing plugin as open source project, with testing in min
 * Version: 1.0.0
 * Author: Luca Maroni
 * Author URI: http://maronl.it
 * Text Domain: linking-posts
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, then abort execution.
if (!defined('WPINC')) {
    die;
}

/**
 * Include the core class responsible for loading all necessary components of the plugin.
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-linking-posts-manager.php';

/**
 * Instantiates the Single Post Meta Manager class and then
 * calls its run method officially starting up the plugin.
 */
function run_linking_posts_manager()
{

    $lm = new Linking_Posts_Manager();
    $lm->run();


}



// Call the above function to begin execution of the plugin.
run_linking_posts_manager();

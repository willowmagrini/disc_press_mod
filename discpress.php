<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.imbeard.com
 * @since             1.0.0
 * @package           Discpress
 *
 * @wordpress-plugin
 * Plugin Name:       DiscPress
 * Plugin URI:        http://www.imbeard.com/discpress
 * Description:       Sync your Discogs collection with your WordPress website!
 * Version:           1.2.4
 * Author:            Imbeard
 * Author URI:        http://www.imbeard.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       discpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-discpress-activator.php
 */
function activate_discpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-discpress-activator.php';
	Discpress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-discpress-deactivator.php
 */
function deactivate_discpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-discpress-deactivator.php';
	Discpress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_discpress' );
register_deactivation_hook( __FILE__, 'deactivate_discpress' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-discpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_discpress() {

	$plugin = new Discpress();
	$plugin->run();

}
run_discpress();

require plugin_dir_path( __FILE__ ) . 'admin/oauth.php';
require plugin_dir_path( __FILE__ ) . 'admin/create-cpt.php';
require plugin_dir_path( __FILE__ ) . 'admin/authorize.php';
require plugin_dir_path( __FILE__ ) . 'admin/verify.php';
require plugin_dir_path( __FILE__ ) . 'admin/sync-records.php';
require plugin_dir_path( __FILE__ ) . 'admin/sync-images.php';
if(isset($_GET['oauth_verifier'])) {
	add_action('init', 'discpressVerify');
}

<?php
/*
Plugin Name: WP Optimize Speed By xTraffic
Version: 1.1.5
Plugin URI: http://blog-xtraffic.pep.vn/
Author: xTraffic
Author URI: http://blog-xtraffic.pep.vn/
Description: Plugin speeds up your website runs faster and better performance.
*/

// If this file is called directly, abort.
if (!defined( 'WPINC' )) {
	header('Status: 403 Forbidden',true,403);
	header('HTTP/1.1 403 Forbidden',true,403);
	die('This file is called directly. You should not try this because it has been blocked!');
	exit();
}

if (
	(defined('WP_INSTALLING') && WP_INSTALLING)
	|| (defined('WP_SETUP_CONFIG') && WP_SETUP_CONFIG)
) {
    return;
}

//Check if plugin is loaded
if ( !defined( 'WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_INIT_STATUS' ) ) {
    define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_INIT_STATUS', true);
	
    define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_VERSION', '1.1.5' );	//Version
    define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAME', 'WP Optimize Speed By xTraffic');
    define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAMESPACE', 'WPOptimizeSpeedByxTraffic');
    define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG', 'wp-optimize-speed-by-xtraffic');
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NS_SHORT', 'wpopspdxtf');
    
	global $wpOptimizeSpeedByxTraffic;
	$wpOptimizeSpeedByxTraffic = false;
	
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_FILE', __FILE__ );
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_DIR', plugin_dir_path( WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_FILE ) );
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_URI', plugins_url( WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_SLUG ) . '/' );
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_LIBS_DIR', WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_DIR . 'libs' . DIRECTORY_SEPARATOR);
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR', WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_DIR . 'Application' . DIRECTORY_SEPARATOR);
	
	/* 
	* PRIORITY
	Used to specify the order in which the functions associated with a particular action are executed. 
	Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	*/
	
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_PRIORITY_FIRST', 10);
	define('WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_PRIORITY_LAST', 900000000);
	
	include_once(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_ROOT_DIR . 'includes/advanced-cache/init.php');
	
	include_once(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR . 'ApplicationBootstrap.php');
	
	include_once(WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR . 'includes/functions/plugin_activation_deactivation_hook.php');
	
	global $wpOptimizeSpeedByxTraffic;
	
	$wpOptimizeSpeedByxTraffic = new \WPOptimizeSpeedByxTraffic\Application\ApplicationBootstrap();
	$wpOptimizeSpeedByxTraffic->init();
	
	register_activation_hook( __FILE__, array($wpOptimizeSpeedByxTraffic, 'wp_register_activation_hook'));
	register_deactivation_hook( __FILE__, array($wpOptimizeSpeedByxTraffic, 'wp_register_deactivation_hook'));
	register_deactivation_hook( __FILE__, 'wp_optimize_speed_by_xtraffic_plugin_deactivation_hook');
	
}


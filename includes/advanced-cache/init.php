<?php 

if (!defined('ABSPATH')) {
    die();
}

if (
	(defined('WP_INSTALLING') && WP_INSTALLING)
	|| (defined('WP_SETUP_CONFIG') && WP_SETUP_CONFIG)
) {
    return;
}

defined('WPOPTMSPXTR_ADVC_WP_CONTENT_DIR') || define( 'WPOPTMSPXTR_ADVC_WP_CONTENT_DIR', ABSPATH . 'wp-content' );
defined('WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR') || define( 'WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR', WPOPTMSPXTR_ADVC_WP_CONTENT_DIR . '/plugins' );

if(
	!is_file(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/includes/functions/general.php')
	|| !is_file(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/Service/PepVN_Cache.php')
	|| !is_file(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/Service/Mobile_Detect.php')
) {
	return;
}

ob_implicit_flush(false);	//TRUE to turn implicit flushing on, FALSE otherwise.
ob_start();

include_once(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/includes/functions/general.php');

wppepvn_include_once(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/Service/PepVN_Cache.php');
wppepvn_include_once(WPOPTMSPXTR_ADVC_WP_PLUGIN_DIR.'/wp-optimize-by-xtraffic/Application/Service/Mobile_Detect.php');

global $wpOptimizeSpeedByxTraffic_AdvancedCache;

wppepvn_include_once(__DIR__ .'/AdvancedCache.php');

if(function_exists('wppepvn_get_plugin_version')) {
	if(!isset($wpOptimizeSpeedByxTraffic_AdvancedCache)) {
		$wpOptimizeSpeedByxTraffic_AdvancedCache = new \WPOptimizeSpeedByxTraffic\AdvancedCache();
		$wpOptimizeSpeedByxTraffic_AdvancedCache->checkAndGetPageCache();
	}
}


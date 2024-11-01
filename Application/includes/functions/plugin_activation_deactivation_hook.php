<?php 

function wp_optimize_speed_by_xtraffic_plugin_activation_hook()
{
	
}


function wp_optimize_speed_by_xtraffic_plugin_deactivation_hook()
{
	
	$wp_config_file_path = ABSPATH . 'wp-config.php';
	
	if(is_file($wp_config_file_path) && is_readable($wp_config_file_path) && is_writable($wp_config_file_path)) {
		$wp_config_content = file_get_contents($wp_config_file_path);
		if($wp_config_content) {
			
			$wp_config_content = preg_replace('#\s*define\s*\(\s*(\'|\")WP_CACHE\1\s*,\s*(true|false)\s*\)\s*;\s*#is',PHP_EOL,$wp_config_content);
			
			$wp_config_content = preg_replace('/# BEGIN WP OPTIMIZE BY XTRAFFIC - ADVANCED CACHE #.+# END WP OPTIMIZE BY XTRAFFIC - ADVANCED CACHE #/s',PHP_EOL,$wp_config_content);
			
			file_put_contents($wp_config_file_path,$wp_config_content);
			
		}
	}
	
	$arrFilesPathNeedRemove = array(
		WP_CONTENT_DIR . '/object-cache.php'
		,WP_CONTENT_DIR . '/db.php'
		,WP_CONTENT_DIR . '/advanced-cache.php'
	);
	
	foreach($arrFilesPathNeedRemove as $file_path) {
		if($file_path && is_file($file_path)) {
			@unlink($file_path);
		}
	}
}




<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service;

use WPOptimizeSpeedByxTraffic\Application\Service\PepVN_CacheSimpleFile
;

class PluginManager
{
	const PLUGIN_APPLICATION_DIR = WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR;
	
	public static $defaultParams = false;
	
	public static $cacheObject = false;
	
	protected static $_tempData = array();
	
	protected $_pluginPathActived = array();
	
	public function __construct() 
	{
		self::setDefaultParams();
	}
	
	public static function setDefaultParams()
	{
		if(false === self::$defaultParams) {
			
			self::$defaultParams['status'] = true;
			
			self::initCacheObject();
			
		}
	}
	
	/*
	* Cache is important to avoid affecting the speed of website
	*/
	public static function initCacheObject()
	{
		$cacheDir = self::PLUGIN_APPLICATION_DIR . implode( DIRECTORY_SEPARATOR, array(
			'includes'
			,'storages'
			,'cache'
			,'s'
		)) . DIRECTORY_SEPARATOR;
		
		if(!file_exists($cacheDir) || !is_dir($cacheDir)) {
			mkdir($cacheDir, 0755, true);
		}
		
		if(file_exists($cacheDir) && is_dir($cacheDir) && is_readable($cacheDir) && is_writable($cacheDir)) {
			
			$cacheKeySalt = md5(__FILE__ . __METHOD__ . $cacheDir);
			
			self::$cacheObject = new PepVN_CacheSimpleFile(array(
				'cache_timeout' => 86400				//seconds
				,'hash_key_method' => 'crc32b'		//best is crc32b
				,'hash_key_salt' => $cacheKeySalt
				,'gzcompress_level' => 0
				,'key_prefix' => 'dts_'
				,'cache_dir' => $cacheDir
			));
		} else {
			self::$cacheObject = new PepVN_CacheSimpleFile(array()); 
		}
		
	}
	
	public static function hashKey($data)
	{
		return hash('crc32b', md5(serialize($data)));
	}
	
	public static function init_require_functions()
	{
		if(
			!function_exists('install_plugin_install_status')
			|| !function_exists('plugins_api')
		) {
			require_once (ABSPATH . implode(DIRECTORY_SEPARATOR, array(
				'wp-admin'
				,'includes'
				,'plugin-install.php'
			)));
		}
		
		if(!function_exists('get_plugins')) {
			require_once (ABSPATH . implode(DIRECTORY_SEPARATOR, array(
				'wp-admin'
				,'includes'
				,'plugin.php'
			)));
		}
				
	}
	
	public static function activate_plugin( $plugin ) 
	{
		$current = get_option( 'active_plugins' );
		
		$plugin = plugin_basename( trim( $plugin ) );
		
		if ( !in_array( $plugin, $current ) ) {
			$current[] = $plugin;
			sort( $current );
			do_action( 'activate_plugin', trim( $plugin ) );
			update_option( 'active_plugins', $current );
			do_action( 'activate_' . trim( $plugin ) );
			do_action( 'activated_plugin', trim( $plugin) );
		}
		
		return null;
	}
	
	//install_plugin_install_status($api, $loop = false) {
	public static function install_plugin_install_status($api, $loop = false)
	{
		$keyCache = self::hashKey(array('install_plugin_install_status', $api, $loop));
		
		if(!isset(self::$_tempData[$keyCache])) {
			
			self::$_tempData[$keyCache] = self::$cacheObject->get_cache($keyCache);
			
			if(null === self::$_tempData[$keyCache]) {
				
				self::init_require_functions();
				
				self::$_tempData[$keyCache] = install_plugin_install_status($api, $loop);
				
				self::$cacheObject->set_cache($keyCache, self::$_tempData[$keyCache]);
			}
		}
		
		return self::$_tempData[$keyCache];
	}
	
	
	public function get_plugin_info($input_args)
	{
        
		if(!isset($input_args['fields'])) {
			$input_args['fields'] = array();
		}
		
		$keyCache = self::hashKey(array('get_plugin_info', $input_args));
		
		if(!isset(self::$_tempData[$keyCache])) {
				
			self::$_tempData[$keyCache] = self::$cacheObject->get_cache($keyCache);
			
			if(null === self::$_tempData[$keyCache]) {
				
				self::init_require_functions();
				
				$fields = array(
					'short_description' => true,
					'screenshots' => false,
					'changelog' => false,
					'installation' => false,
					'description' => false
				);
				
				$fields = array_merge($fields, (array)$input_args['fields']);
				
				$args = array(
					'slug' => $input_args['slug'],
					'fields' => $fields
				);
				
				self::$_tempData[$keyCache] = plugins_api('plugin_information', $args);
				
				self::$cacheObject->set_cache($keyCache, self::$_tempData[$keyCache]);
			}
		}
		
        return self::$_tempData[$keyCache];
		
    }
	
	
	private function _isUserCanManagePlugin()
	{
		$k = '_isUserCanManagePlugin';
		
		if(!isset(self::$_tempData[$k])) {
			self::$_tempData[$k] = false;
			
			if(is_user_logged_in()) {
			
				if(current_user_can('activate_plugins')) {
					
					if(current_user_can('delete_plugins')) {
						
						if(current_user_can('edit_plugins')) {
							
							if(current_user_can('install_plugins')) {
								
								if(current_user_can('update_plugins')) {
									
									self::$_tempData[$k] = true;
								}
							}
						}
					}
				}
			
			}
		}
		
		return self::$_tempData[$k];
	}
	
	public function checkActionManagePluginsForDependency()
	{
		$resultData = array();
		
		$keyActiveKey = 'wppepvn-active-plugin-key';
		$keyActiveName = 'wppepvn-active-plugin-name';
		$keyActiveVia = 'wppepvn-active-plugin-via';
		
		$pluginPathActived = array();
		
		if(
			isset($_GET[$keyActiveKey]) && $_GET[$keyActiveKey]
		) {
			if(
				isset($_GET[$keyActiveName]) && $_GET[$keyActiveName]
				&& isset($_GET[$keyActiveVia]) && $_GET[$keyActiveVia]
			) {
				if($this->_currentPluginSlug === $_GET[$keyActiveVia]) {
					if(is_admin()) {
						if($this->_isUserCanManagePlugin()) {
							$this->activate_plugin($_GET[$keyActiveKey]);
							$this->_pluginPathActived[$_GET[$keyActiveKey]] = $_GET[$keyActiveKey];
							$resultData['notice']['success'][] = 'Plugin "<u><b>'.$_GET[$keyActiveName].'</b></u>" activated successfully!';
						}
						
					}
				}
			}
		}
		
		return $resultData;
	}
	
	
	
}

PluginManager::setDefaultParams();
<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service;

use WPOptimizeSpeedByxTraffic\Application\Service\PluginManager
;

class PluginDependencyManager extends PluginManager
{
	
	const INSTALLED_SUCCESS_STATUS = 1;
	const INSTALLED_ERROR_STATUS = -1;
	
	const ACTIVATED_SUCCESS_STATUS = 2;
	const ACTIVATED_ERROR_STATUS = -2;
	
	const VERSION_SUCCESS_STATUS = 3;
	const VERSION_ERROR_STATUS = -3;
	
	const VALID_SUCCESS_STATUS = 6;
	
	const GET_ACTIVE_PLUGIN_NS = 'wppepvn-active-plugin';
	
	private $_bag = 0;
	
	private $_requiredPluginDependency = false;
	protected $_currentPluginName = '';
	protected $_currentPluginSlug = '';
	
	public function __construct($bag = 0) 
	{
		parent::__construct();
		
		if(!$bag) {
			$bag = mt_rand(1,900000000);
		}
		
		$this->_bag = $bag;
	}
	
	/*
	$requiredPluginDependency = array(
		'plugins' => array(
			'wp-optimize-by-xtraffic' => array(
				'name' => 'WP Optimize By xTraffic'
				, 'slug' => 'wp-optimize-by-xtraffic'
				, 'wp_plugin_url' => 'https://wordpress.org/plugins/wp-optimize-by-xtraffic/'
				, 'version' => '>=5.0.0'
				, 'check' => array(
					'variable_name' => 'wpOptimizeByxTraffic'
					,'constant_version_name' => 'WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_VERSION'
					,'method_init_status' => 'initStatus'
				)
			)
		)
	);
	*/
	
	public function setRequiredPluginDependency($requiredPluginDependency) 
	{
		$this->_requiredPluginDependency = (array)$requiredPluginDependency;
	}
	
	public function setCurrentPluginName($currentPluginName) 
	{
		$this->_currentPluginName = (string)$currentPluginName;
	}
	
	public function setCurrentPluginSlug($currentPluginSlug) 
	{
		$this->_currentPluginSlug = (string)$currentPluginSlug;
	}
	
	public function current_user_can($capability) 
	{
		$keyCache = self::hashKey(array('current_user_can',$capability));
		
		if(!isset(self::$_tempData[$keyCache])) {
			self::$_tempData[$keyCache] = current_user_can($capability);
		}
		
		return self::$_tempData[$keyCache];
	}
	
	
	public function checkRequiredPluginDependency()
	{
		$keyCache = self::hashKey(array($this->_bag,'checkRequiredPluginDependency'));
		
		if(!isset(self::$_tempData[$keyCache])) {
			
			$resultData = array();
			
			if($this->_requiredPluginDependency) {
					
				foreach($this->_requiredPluginDependency['plugins'] as $keyOne => $valueOne) {
					
					if(
						!isset($valueOne['check']['variable_name'])
						|| !isset($valueOne['check']['constant_version_name'])
					) {
						throw new \Exception('Require "variable_name" & "constant_version_name" to check required plugin dependency!');
					}
					
					$valueOne['file_path_key'] = $valueOne['slug']. DIRECTORY_SEPARATOR .$valueOne['slug'].'.php';
					$valueOne['file_path'] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR .$valueOne['file_path_key'];
					
					$valueOne['version'] = trim($valueOne['version']);
					
					$slug = $valueOne['slug'];
					
					$variable_name = $valueOne['check']['variable_name'];
					$constant_version_name = $valueOne['check']['constant_version_name'];
					$method_init_status = $valueOne['check']['method_init_status'];
					
					if(
						file_exists($valueOne['file_path'])
						&& is_file($valueOne['file_path'])
					) {
						$valueOne['status'] = self::INSTALLED_SUCCESS_STATUS;
						
						if(defined($constant_version_name)) {
							$valueOne['status'] = self::ACTIVATED_SUCCESS_STATUS;
							
							$constant_version_value = constant($constant_version_name);
							
							$version_compare_operator = '>=';
							$version_compare_value = false;
							
							preg_match('#^([^0-9\.]+)?([0-9\.]+)$#',$valueOne['version'],$matched1);
							
							if(isset($matched1[1]) && $matched1[1]) {
								$version_compare_operator = $matched1[1];
							}
							
							$version_compare_value = $matched1[2];
							
							if(version_compare($constant_version_value, $version_compare_value, $version_compare_operator)) {
								$valueOne['status'] = self::VERSION_SUCCESS_STATUS;
								global $$variable_name;
								if(isset($$variable_name) && ($$variable_name)) {
									if('success' === $$variable_name->$method_init_status()) {
										$valueOne['status'] = self::VALID_SUCCESS_STATUS;
									} else {
										$valueOne['status'] = 0;
									}
								} else {
									$valueOne['status'] = 0;
								}
							} else {
								$valueOne['status'] = self::VERSION_ERROR_STATUS;
							}
							
						} else {
							$valueOne['status'] = self::ACTIVATED_ERROR_STATUS;
						}
						
					} else {
						$valueOne['status'] = self::INSTALLED_ERROR_STATUS;
					}
					
					if($this->current_user_can('install_plugins')) {
						
						if(
							(self::INSTALLED_ERROR_STATUS === $valueOne['status'])	//not install
							|| (self::VERSION_ERROR_STATUS === $valueOne['status'])	//not right version
						) {
							$install_plugin_install_status = self::install_plugin_install_status(self::get_plugin_info(array(
								'slug' => $slug
								,'fields' => array()
							)), true);
							
							if(isset($install_plugin_install_status['url']) && $install_plugin_install_status['url']) {
								
								if((self::INSTALLED_ERROR_STATUS === $valueOne['status'])) {
									$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>"!<br />Please <a href="'.$install_plugin_install_status['url'].'"><u><b><i>click here to install plugin "'.$valueOne['name'].'"</i></b></u></a>!';
								} else if((self::VERSION_ERROR_STATUS === $valueOne['status'])) {
									$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>" has version "<b><u>' . $version_compare_operator . ' ' . $version_compare_value . '</u></b>"!<br />Please <a href="'.$install_plugin_install_status['url'].'"><u><b><i>click here to update plugin "'.$valueOne['name'].'"</i></b></u></a>!';
								}
								
							} else {
								
								if((self::INSTALLED_ERROR_STATUS === $valueOne['status'])) {
									$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>"!<br />Please <a href="'.$valueOne['wp_plugin_url'].'" target="_blank"><u><b><i>download & install plugin "'.$valueOne['name'].'" here</i></b></u></a>!';
								} else if((self::VERSION_ERROR_STATUS === $valueOne['status'])) {
									$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>" has version "<b><u>' . $version_compare_operator . ' ' . $version_compare_value . '</u></b>"!<br />Please <a href="'.$valueOne['wp_plugin_url'].'" target="_blank"><u><b><i>download & update plugin "'.$valueOne['name'].'" here</i></b></u></a>!';
								}
								
							}
							
						} else if(
							(self::ACTIVATED_ERROR_STATUS === $valueOne['status'])	//installed but not active
						) {
							
							$addNoticeStatus = true;
							
							if(isset($this->_pluginPathActived[$valueOne['file_path_key']])) {
								$addNoticeStatus = false;
							} else {
								$getActiveKey = self::GET_ACTIVE_PLUGIN_NS.'-key';
								if(isset($_GET[$getActiveKey]) && $_GET[$getActiveKey]) {
									$addNoticeStatus = false;
								}
							}
							
							if($addNoticeStatus) {
								if(is_ssl()) {
									$adminUrl = admin_url( '', 'https' );
								} else {
									$adminUrl = admin_url( '', 'http' );
								}
								
								$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>"!<br />Please <a href="'.add_query_arg(array(
									self::GET_ACTIVE_PLUGIN_NS.'-key' => rawurlencode($valueOne['file_path_key'])
									,self::GET_ACTIVE_PLUGIN_NS.'-name' => rawurlencode($valueOne['name'])
									,self::GET_ACTIVE_PLUGIN_NS.'-via' => rawurlencode($this->_currentPluginSlug)
								), $adminUrl.'plugins.php?').'"><u><b><i>click here to activate this plugin</i></b></u></a>!';
							}
							
						} else if(
							(self::VALID_SUCCESS_STATUS !== $valueOne['status'])	//installed but not active
						) {
							$valueOne['notice']['error'][] = 'Plugin "<u><b>'.$this->_currentPluginName.'</b></u>" requires the following plugin "<u><b>'.$valueOne['name'].'</b></u>"! But there is an unspecified error make this plugin can not run. Please <a href="https://www.facebook.com/wpoptimizebyxtraffic" target="_blank"><u><b><i>contact us here for assistance</i></b></u></a>!';
						}
					}
					
					if(isset($valueOne['notice']['error'])) {
						$valueOne['notice']['error'] = array_unique($valueOne['notice']['error']);
					}
					
					$resultData[$keyOne] = $valueOne;
				}
				
			}
			
			self::$_tempData[$keyCache] = $resultData;
		}
		
		return self::$_tempData[$keyCache];
	}
	
}

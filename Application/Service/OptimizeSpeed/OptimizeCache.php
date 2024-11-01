<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\CacheRequestUri
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\Database as OptimizeCache_Database
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCache\ObjectCache as OptimizeCache_ObjectCache
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
;

class OptimizeCache
{
	public $di = false;
	
	protected static $_tempData = array();
	
	private $_statisticAccess = false;
	
	private $_staticVarObject = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$options = OptimizeSpeed::getOption();
		
		$optimizeCache_Database = new OptimizeCache_Database($this->di);
		$optimizeCache_Database->init($options);
		
		$optimizeCache_ObjectCache = new OptimizeCache_ObjectCache($this->di);
		$optimizeCache_ObjectCache->init($options);
		
		$tmp = array();
		$tmp['group_urls_prebuild_cache'] = array();
		$this->_staticVarObject = new ServiceStaticVar(md5('WPOptimizeSpeedByxTraffic/Application/Service/OptimizeSpeed/OptimizeCache/construct'), $tmp);
		
		$hook = $this->di->getShared('hook');
		
		$hook->add_action('clean_cache',array($this, 'on_clean_cache_action'));
		
		$hook->add_action('cronjob', array($this,'prebuild_urls_cache'), WP_PEPVN_PRIORITY_LAST);
		
		
	}
	
    public function setStatisticAccess($statisticAccess)
	{
		$this->_statisticAccess = $statisticAccess;
	}
	
	public function initFrontend()
	{
		
	}
	
	public function initBackend()
	{
		
	}
	
	public function set_cache_for_web_server($buffer)
	{
		
		$cacheRequestUri = $this->di->getShared('cacheRequestUri');
		
		$cacheRequestUri->set_cache_for_web_server($buffer, array(
			'force_override' => true
		));
	}
	
	public function on_clean_cache_action() 
    {
		$staticVarData = $this->_staticVarObject->get();
		$staticVarData['group_urls_prebuild_cache'] = array();
		$this->_staticVarObject->save($staticVarData, 'r');
		
	}
	
	public function prebuild_urls_cache() 
    {
		
		$options = OptimizeSpeed::getOption();
		
		$checkStatus1 = false;
		
		if(isset($options['optimize_cache_enable']) && ('on' === $options['optimize_cache_enable'])) {
			$checkStatus1 = true;
		}
		
		if($checkStatus1) {
			if(isset($options['optimize_cache_prebuild_cache_enable']) && ('on' === $options['optimize_cache_prebuild_cache_enable'])) {
			} else {
				$checkStatus1 = false;
			}
		}
		
		if(!$checkStatus1) {
			return $resultData;
		}
		
		if(!isset($options['optimize_cache_prebuild_cache_number_pages_each_process'])) {
			$options['optimize_cache_prebuild_cache_number_pages_each_process'] = 1;
		}
		$options['optimize_cache_prebuild_cache_number_pages_each_process'] = (int)$options['optimize_cache_prebuild_cache_number_pages_each_process'];
		if($options['optimize_cache_prebuild_cache_number_pages_each_process'] < 1) {
			$options['optimize_cache_prebuild_cache_number_pages_each_process'] = 1;
		}
		
		if(!isset($options['optimize_cache_cachetimeout'])) {
			$options['optimize_cache_cachetimeout'] = 3600;
		}
		
		$timeoutRequest = 60;//seconds
		
		$maxTimePrebuild = 180;//seconds
		
		$staticVarData = array_merge($this->_statisticAccess->get_data(), $this->_staticVarObject->get());
		
		$groupUrlsStatistics = array();
		
		$groupUrlsNeedPrebuild = array();
		
		$groupScreenWidth = array();
		
		if(isset($staticVarData['statistics']['group_urls']) && is_array($staticVarData['statistics']['group_urls'])) {
			$groupUrlsStatistics = $staticVarData['statistics']['group_urls'];
		}
		
		if(isset($staticVarData['statistics']['group_screen_width']) && is_array($staticVarData['statistics']['group_screen_width'])) {
			$groupScreenWidth = $staticVarData['statistics']['group_screen_width'];
		}
		
		$maxNumberUrlsPrebuild = 999999;
		
		if(
			isset($staticVarData['total_hits_microtime_process']) && $staticVarData['total_hits_microtime_process']
			&& isset($staticVarData['total_microtime_process']) && $staticVarData['total_microtime_process']
		) {
			$microtimeProcessPerRequest = $staticVarData['total_microtime_process'] / $staticVarData['total_hits_microtime_process'];
			$microtimeProcessPerRequest = (float)$microtimeProcessPerRequest;
			
			$maxNumberUrlsPrebuild1 = ceil($maxTimePrebuild / $microtimeProcessPerRequest);
			if($maxNumberUrlsPrebuild > $maxNumberUrlsPrebuild1) {
				$maxNumberUrlsPrebuild = $maxNumberUrlsPrebuild1;
			}
			
		}
		
		$maxNumberUrlsPrebuild = (int)$maxNumberUrlsPrebuild;
	
		if($maxNumberUrlsPrebuild > $options['optimize_cache_prebuild_cache_number_pages_each_process']) {
			$maxNumberUrlsPrebuild = $options['optimize_cache_prebuild_cache_number_pages_each_process'];
		}
		
		if($maxNumberUrlsPrebuild < 1) {
			$maxNumberUrlsPrebuild = 1;
		}
		
		$maxNumberUrlsPrebuild = (int)$maxNumberUrlsPrebuild;
		
		if($maxNumberUrlsPrebuild > 100) {
			$maxNumberUrlsPrebuild = 100;
		}
		
		if(!empty($groupUrlsStatistics)) {
			PepVN_Data::ref_sort_array_by_key($groupUrlsStatistics, 'r', 'desc');
			
			$iNumber1 = 0;
			
			foreach($groupUrlsStatistics as $key1 => $value1) {
				unset($groupUrlsStatistics[$key1]);
				if($key1) {
					
					$checkStatus2 = true;
					
					if(isset($staticVarData['group_urls_prebuild_cache'][$key1]) && $staticVarData['group_urls_prebuild_cache'][$key1]) {
						$checkStatus2 = false;
						if(($staticVarData['group_urls_prebuild_cache'][$key1] + $options['optimize_cache_cachetimeout']) < PepVN_Data::$defaultParams['requestTime']) {
							$checkStatus2 = true;
						}
					}
					
					if($checkStatus2) {
						$groupUrlsNeedPrebuild[] = $key1;
						$iNumber1++;
						if($iNumber1 >= $maxNumberUrlsPrebuild) {
							break;
						}
					}
				}
				
			}
			$groupUrlsStatistics = 0;
		}
		
		if(!empty($groupUrlsNeedPrebuild)) {
			
			$remote = $this->di->getShared('remote');
			
			$groupUrlsNeedPrebuild = array_unique($groupUrlsNeedPrebuild);
			
			$optimize_images_auto_resize_images_enable = false;
			
			if(class_exists('\WPOptimizeByxTraffic\Application\Service\OptimizeImages')) {
				$tmp = \WPOptimizeByxTraffic\Application\Service\OptimizeImages::getOption();
				
				if(isset($tmp['optimize_images_auto_resize_images_enable']) && ('on' === $tmp['optimize_images_auto_resize_images_enable'])) {
					$optimize_images_auto_resize_images_enable = true;
					if(!empty($groupScreenWidth)) {
						arsort($groupScreenWidth);
						reset($groupScreenWidth);
						$iNumber1 = 0;
						foreach($groupScreenWidth as $key1 => $value1) {
							unset($groupScreenWidth[$key1]);
							$iNumber1++;
							if($iNumber1 < 10) {
								$key1 = preg_replace('#[^0-9]+#is','',$key1);
								$key1 = (int)$key1;
								$groupScreenWidth[$key1]  = $value1;
							}
						}
					}
				}
				unset($tmp);
			}
			
			
			foreach($groupUrlsNeedPrebuild as $key1 => $value1) {
				unset($groupUrlsNeedPrebuild[$key1]);
				
				$remote->get($value1,array(
					'request_timeout' => 1
					,'redirection' => 1
				));
				
				$staticVarData['group_urls_prebuild_cache'][$value1] = PepVN_Data::$defaultParams['requestTime'];
				
				$this->_staticVarObject->save(array(
					'group_urls_prebuild_cache' => $staticVarData['group_urls_prebuild_cache']
				), 'm');
				
				$resultData['optimize_cache_prebuild_urls'][$value1] = 1;
				
				sleep( 1 );
				
				if($optimize_images_auto_resize_images_enable) {
					if(!empty($groupScreenWidth)) {
						reset($groupScreenWidth);
						foreach($groupScreenWidth as $key2 => $value2) {
							$remote->get($value1, array(
								'request_timeout' => $timeoutRequest
								,'redirection' => 1
								,'headers' => array(
									'Cookie:xtrdvscwd='.$key2
								)
								,'cookies' => array(
									'xtrdvscwd' => $key2
								)
							));
							sleep(1);
						}
					}
				}
				
			}
		}
		
		$this->_staticVarObject->save(array(
			'group_urls_prebuild_cache' => $staticVarData['group_urls_prebuild_cache']
		), 'm');
		
		return $resultData;
	}
	
}
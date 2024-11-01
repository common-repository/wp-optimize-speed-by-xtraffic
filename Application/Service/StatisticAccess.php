<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
	, WpPepVN\DependencyInjection
	, WpPepVN\Utils
	, WpPepVN\Text
	, WpPepVN\System
;

class StatisticAccess
{
	private $_staticVarObject = false;
	
	private $di = false;
	
	public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$tmp = array();
		$tmp['statistics']['group_urls'] = array();
		$tmp['total_microtime_process'] = 0;
		$tmp['total_hits_microtime_process'] = 0;
		$tmp['total_number_requests'] = 0;
		$tmp['last_time_clean_data'] = 0;
		$tmp['group_screen_width'] = array();
		$this->_staticVarObject = new ServiceStaticVar(md5('WPOptimizeSpeedByxTraffic/Application/Service/StatisticAccess/construct'), $tmp);
		
	}
    
	public function statistic_access_urls_sites($input_options)
	{
		$device = $this->di->getShared('device');
		
		$urlsNeedStatistics = array();
		
		if(isset(PepVN_Data::$defaultParams['urlFullRequest']) && PepVN_Data::$defaultParams['urlFullRequest']) {
			$urlsNeedStatistics[] = PepVN_Data::$defaultParams['urlFullRequest'];
		}
		
		
		$dataSent = PepVN_Data::getDataSent();
		if($dataSent && isset($dataSent['localTimeSent']) && $dataSent['localTimeSent']) {
			if(isset($dataSent['window_location_href']) && $dataSent['window_location_href']) {
				$urlsNeedStatistics[] = $dataSent['window_location_href'];
			}
		}
		$dataSent = 0;
		
		$urlsNeedStatistics = array_unique($urlsNeedStatistics);
		if(!empty($urlsNeedStatistics)) {
			foreach($urlsNeedStatistics as $key1 => $val1) {
				
				$checkStatus1 = false;
				
				if(preg_match('#^https?://.+#is',$val1)) {
					if(!preg_match('#(\#|\?|wp\-(admin|content|includes)|\.php)+#i',$val1)) {
						$checkStatus1 = true;
					}
				}
				
				if(!$checkStatus1) {
					unset($urlsNeedStatistics[$key1]);
				}
			}
		}
		
		$staticVarData = $this->_staticVarObject->get();
		
		if(!empty($urlsNeedStatistics)) {
			foreach($urlsNeedStatistics as $key1 => $val1) {
				
				if(!isset($staticVarData['statistics']['group_urls'][$val1])) {
					$staticVarData['statistics']['group_urls'][$val1] = array(
						'v' => 0	//total number views (request)
						,'t' => 0	//total time process (float)
						,'ht' => 0	//total hits with time process (request)
						,'r' => 0	//rank - float (higher is more important)
					);
				}
				
				if(isset($input_options['calculate_time_php_process_status'])) {
					$timeProcess = abs(microtime(true) - WP_PEPVN_MICROTIME_START);
					$timeProcess = (float)$timeProcess;
					
					$staticVarData['statistics']['group_urls'][$val1]['t'] += $timeProcess;
					$staticVarData['total_microtime_process'] += $timeProcess;
					
					$staticVarData['total_hits_microtime_process']++;
					$staticVarData['statistics']['group_urls'][$val1]['ht']++;
				} else {
					$staticVarData['statistics']['group_urls'][$val1]['v']++;
					$staticVarData['total_number_requests']++;
				}
				
				//calculate rank
				
				$staticVarData['statistics']['group_urls'][$val1]['r'] = $staticVarData['statistics']['group_urls'][$val1]['v'];
				
				$averageTimeProcessEachAllViews = 0;
				
				if($staticVarData['total_hits_microtime_process'] > 0) {
					$averageTimeProcessEachAllViews = $staticVarData['total_microtime_process'] / $staticVarData['total_hits_microtime_process'];
				}
				
				$averageTimeProcessEachAllViews = (float)$averageTimeProcessEachAllViews;
				
				if($averageTimeProcessEachAllViews > 0) {
					if($staticVarData['statistics']['group_urls'][$val1]['ht'] > 0) {
						
						$averageTimeProcessEachThisViews = $staticVarData['statistics']['group_urls'][$val1]['t'] / $staticVarData['statistics']['group_urls'][$val1]['ht'];
						$averageTimeProcessEachThisViews = (float)$averageTimeProcessEachThisViews;
						
						$staticVarData['statistics']['group_urls'][$val1]['r'] += ($staticVarData['statistics']['group_urls'][$val1]['ht'] * ($averageTimeProcessEachThisViews / $averageTimeProcessEachAllViews));
						
					}
					
				}
				
				$staticVarData['statistics']['group_urls'][$val1]['r'] = (float)$staticVarData['statistics']['group_urls'][$val1]['r'];
			}
			
			if(isset($input_options['calculate_time_php_process_status'])) {
				
				$screenWidth = $device->get_device_screen_width();
				$screenWidth = (int)$screenWidth;
				if($screenWidth && ($screenWidth>0)) {
					$keyScreenWidth = 'w'.$screenWidth;
					if(!isset($staticVarData['statistics']['group_screen_width'][$keyScreenWidth])) {
						$staticVarData['statistics']['group_screen_width'][$keyScreenWidth] = 0;
					}
					$staticVarData['statistics']['group_screen_width'][$keyScreenWidth]++;
				}
				
			}
		}
		
		if(
			(0 === $staticVarData['last_time_clean_data'])
			|| ($staticVarData['last_time_clean_data'] <= ( PepVN_Data::$defaultParams['requestTime'] - (2 * 3600)))	//is timeout
		) {
			
			PepVN_Data::ref_sort_array_by_key($staticVarData['statistics']['group_urls'], 'r', 'desc');
			
			$iNumber = 0;
			foreach($staticVarData['statistics']['group_urls'] as $key1 => $val1) {
				++$iNumber;
				if($iNumber >= 2000) {
					unset($staticVarData['statistics']['group_urls'][$key1]);
				}
			}
			
			$staticVarData['last_time_clean_data'] = PepVN_Data::$defaultParams['requestTime'];
			
		}
		
		$this->_staticVarObject->save($staticVarData, 'm');
		
		unset($staticVarData);
	}
	
	
	public function get_data()
	{
		return $this->_staticVarObject->get();
	}
}
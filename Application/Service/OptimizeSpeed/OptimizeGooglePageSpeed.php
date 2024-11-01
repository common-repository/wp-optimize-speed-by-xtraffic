<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\TempDataAndCacheFile
	, WPOptimizeByxTraffic\Application\Service\CSSFixer
	, WpPepVN\System
	, WpPepVN\Hash
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
;

class OptimizeGooglePageSpeed
{
	public $di = false;
	
	private $_tempData = array();
	
	private $_staticVarObject = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$tmp = array();
		
		$this->_staticVarObject = new ServiceStaticVar(md5(__CLASS__ . __METHOD__), $tmp);
		
		$hook = $this->di->getShared('hook');
		
		$hook->add_filter('ajax', array($this, 'filter_ajax'));
		
		$hook->add_action('queue_jobs', array($this, 'action_queue_jobs'));
		
	}
	
	public function initFrontend()
	{
		$options = OptimizeSpeed::getOption();
		
		if(isset($options['learn_improve_google_pagespeed_enable']) && ('on' === $options['learn_improve_google_pagespeed_enable'])) {
			$hook = $this->di->getShared('hook');
			
			$priorityLast = WP_PEPVN_PRIORITY_LAST;
			
			add_action('wp_footer',  array($this, 'add_action_wp_footer'), $priorityLast);
			
			$hook->add_filter('optimize_speed_before_process_html_output_buffer', array($this, 'action_before_process_html_output_buffer'));
			
			$hook->add_filter('before_set_cache_output_buffer', array($this, 'filter_before_set_cache_output_buffer'));
			
			$hook = $this->di->getShared('hook');
			
		}
	}
	
	
	public function getKeyOfCurrentPage() 
	{
		$wpExtend = $this->di->getShared('wpExtend');
		
		$keys = array(__CLASS__ . __METHOD__);
		
		$typeOfPage = $wpExtend->getTypeOfPage();
		
		if(isset($typeOfPage['singular'])) {
			$keys[] = 'singular';
		} 
		
		if(isset($typeOfPage['archive'])) {
			$keys[] = 'archive';
		}
		
		if(isset($typeOfPage['front_page'])) {
			$keys[] = 'front_page';
		}
		
		if(isset($typeOfPage['error_404'])) {
			$keys[] = 'error_404';
		}

		if(isset($typeOfPage['others'])) {
			$keys[] = 'others';
		}
		
		$keys = Utils::hashKey($keys);
		
		return $keys;
		
	}
	
	private function _getKeyOfCurrentPageBaseOnDeviceScreenWidth() 
	{
		
		$device = $this->di->getShared('device');
		$device_screen_width = $device->get_device_screen_width();
		$device_screen_width = (int)$device_screen_width;
		
		return 'dsw-'.$device_screen_width.'-'.$this->getKeyOfCurrentPage();
	}
	
	public function add_action_wp_footer() 
	{
		
		$device = $this->di->getShared('device');
		$device_screen_width = $device->get_device_screen_width();
		$device_screen_width = (int)$device_screen_width;
		
		if($device_screen_width>0) {
			
			$wpExtend = $this->di->getShared('wpExtend');
			
			if(!$wpExtend->is_user_logged_in()) {
				
				$staticVarData_Collects_Clients = $this->_staticVarObject->get('Collects_Clients');
				
				$keyTmp = $this->_getKeyOfCurrentPageBaseOnDeviceScreenWidth();
				
				if(!isset($staticVarData_Collects_Clients['group_raw_by_screens_width'][$keyTmp])) {
					
					echo '<script language="javascript" type="text/javascript" xtraffic-exclude>window.wppepvn_l_i_g_ps_s = {"k":"'.$keyTmp.'"};</script>';
					
					$session = $this->di->getShared('session');
					
					$opmspd_ligpsk = $session->get('opmspd_ligpsk');
					
					$opmspd_ligpsk[$keyTmp] = true;
					
					$session->set('opmspd_ligpsk',$opmspd_ligpsk);
				}
				
			}
		}
	}
	
	public function filter_ajax($dataSent)
	{
		$resultData = array();
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		if(
			isset($dataSent['learnImproveGooglePageSpeed']['targetElements']) 
			&& $dataSent['learnImproveGooglePageSpeed']['targetElements']
			&& isset($dataSent['learnImproveGooglePageSpeed']['k']) 
			&& $dataSent['learnImproveGooglePageSpeed']['k']
			
			&& !$wpExtend->is_user_logged_in()
		) {
			
			$deviceScreenWidthKey = (string)$dataSent['learnImproveGooglePageSpeed']['k'];
			
			$session = $this->di->getShared('session');
			
			$opmspd_ligpsk = $session->get('opmspd_ligpsk');
			
			if(isset($opmspd_ligpsk[$deviceScreenWidthKey])) {
				
				unset($opmspd_ligpsk,$session);
				
				$device = $this->di->getShared('device');
				$device_screen_width = $device->get_device_screen_width();
				$device_screen_width = (int)$device_screen_width;
				
				if($device_screen_width>0) {
					
					$staticVarData_Collects_Clients = $this->_staticVarObject->get('Collects_Clients');
					
					if(!isset($staticVarData_Collects_Clients['group_raw_by_screens_width'][$deviceScreenWidthKey])) {
						
						$staticVarData_Collects_Clients['group_raw_by_screens_width'][$deviceScreenWidthKey] = true;
						
						$arrayFieldsATFC = array(
							'atfc_tagName'
							, 'atfc_id'
							, 'atfc_class'
						);
						
						foreach($arrayFieldsATFC as $value1) {
							
							if(
								isset($dataSent['learnImproveGooglePageSpeed']['targetElements'][$value1]) 
								&& $dataSent['learnImproveGooglePageSpeed']['targetElements'][$value1]
								&& is_array($dataSent['learnImproveGooglePageSpeed']['targetElements'][$value1])
								&& !empty($dataSent['learnImproveGooglePageSpeed']['targetElements'][$value1])
							) {
								foreach($dataSent['learnImproveGooglePageSpeed']['targetElements'][$value1] as $key2 => $value2) {
									if($value2) {
										$value2 = trim($value2);
										if($value2) {
											$value2 = strtolower($value2);
											$staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1][$value2] = $value2;
										}
									}
									
								}
							}
							
						}
						
						$this->_staticVarObject->save($staticVarData_Collects_Clients, 'm' , 'Collects_Clients');
						
						unset($staticVarData_Collects_Clients);
					}
				}
			}
		}
		
		return $resultData;
	}
	
	private function _parse_get_style_of_pattern($pattern,$css,$matchedIndex)
	{
		
		$keyCache = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$pattern
			,$css
			,$matchedIndex
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			preg_match_all($pattern,$css,$matched1);
			
			$matchedIndex = (array)$matchedIndex;
			
			foreach($matchedIndex as $index) {
				if(
					isset($matched1[$index]) 
					&& $matched1[$index]
					&& is_array($matched1[$index])
					&& !empty($matched1[$index])
				) {
					
					foreach($matched1[$index] as $key2 => $value2) {
						unset($matched1[$index][$key2]);
						if($value2) {
							$value2 = trim($value2);
							if($value2) {
								$resultData[$index][] = $value2;
							}
						}
						
					}
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache,$resultData);
			
		}
		
		return $resultData;
	}
	
	private function _parse_get_media_of_style($style)
	{
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			, $style
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = $style;
			
			$staticVarData_HasMedia = $this->_staticVarObject->get('data_has_media');
			
			if(isset($staticVarData_HasMedia[$keyCache1]['d'])) {
				$resultData = $staticVarData_HasMedia[$keyCache1]['d'];
			} else {
				
				$staticVarData_CSS_Minified = $this->_staticVarObject->get('CSS_Minified');
				
				$styleProcessed = array();
				
				$tmp1 = $style;
				$tmp2 = '';
				
				$maxLength = 250;
				if(strlen($style) >= $maxLength) {
					$tmp1 = substr($style,0,$maxLength-2);
					$tmp2 = '[^\{\}]+{[^\{\}]+\}';
				}
				$pattern1 = '#\@media([^\{\}]+?)\{(((?!\@media).)*('.preg_quote($tmp1,'#').$tmp2.')((?!\@media).)*)\}#is';
				$pattern2 = '#('.preg_quote($tmp1,'#').$tmp2.')#is';
				
				
				foreach($staticVarData_CSS_Minified as $key1 => $value1) {
					unset($staticVarData_CSS_Minified[$key1]);
					if($value1['d'] && !empty($value1['d'])) {
						preg_match_all($pattern1,$value1['d'],$matched1); 
						
						if(
							isset($matched1[0])
							&& $matched1[0]
							&& is_array($matched1[0])
							&& !empty($matched1[0])
						) {
							
							$matched1 = $matched1[0];
							
							foreach($matched1 as $key2 => $value2) {
								unset($matched1[$key2]);
								$styleProcessed[$value2] = 1;
								unset($value2);
							}
							
						} else if(preg_match($pattern2,$value1['d'])) {
							$styleProcessed[$style] = 1;
						}
						
						unset($matched1);
					}
					
					unset($key1,$value1);
				}
				
				if(!empty($styleProcessed)) {
					
					$styleProcessed = array_keys($styleProcessed);
					$styleProcessed = implode('',$styleProcessed);
					$styleProcessed = pepvn_MinifyCss($styleProcessed);
					
					$staticVarData_HasMedia[$keyCache1] = array(
						'd' => $styleProcessed
						,'r' => 1
						,'t' => time()
					);
					
					$this->_staticVarObject->save($staticVarData_HasMedia, 'm','data_has_media');
					
					unset($staticVarData_HasMedia);
					
					$resultData = $styleProcessed;
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1,$resultData);
		}
		
		return $resultData;
	}
	
	
	private function _get_minify_css_item($data)
	{
		$resultData = array(
			'data_processed' => false
		);
		
		$optimizeCDN = $this->di->getShared('optimizeCDN');
		
		$classMethodKey = crc32(__CLASS__ . __METHOD__);
		
		if(isset($data['href'])) {
			
			$data['key_processed'] = Utils::hashKey(array(
				$classMethodKey
				, $data['href']
			));
			
			$remote = $this->di->getShared('remote');
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($data['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $remote->get($data['href'],array(
					'cache_timeout' => WP_PEPVN_CACHE_TIMEOUT_NORMAL
				));
				
				if(false !== $resultData['data_processed']) {
					
					$cssFixer = new CSSFixer();
					
					$resultData['data_processed'] = $cssFixer->fix(array(
						'css_url' => $data['href']
						, 'css_content' => $resultData['data_processed']
						, 'minify_status' => true
					));
					
					unset($cssFixer);
					
					$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
					
					PepVN_Data::$cacheObject->set_cache($data['key_processed'],$resultData['data_processed']);
				}
				
			}
			
		} else if(isset($data['code'])) {
			
			$data['key_processed'] = Utils::hashKey(array(
				$classMethodKey
				, $data['code']
			));
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($data['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $data['code'];
				
				unset($data['code']);
				
				$cssFixer = new CSSFixer();
				
				$resultData['data_processed'] = $cssFixer->fix(array(
					'css_url' => PepVN_Data::$defaultParams['urlFullRequest']
					, 'css_content' => $resultData['data_processed']
					, 'minify_status' => true
				));
				
				unset($cssFixer);
				
				$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
				
				PepVN_Data::$cacheObject->set_cache($data['key_processed'],trim($resultData['data_processed']));
				
			}
			
		}
		
		return $resultData;
	}
	
	public function process_css_item($data, $configs = array())
	{
		
		$classMethodKey = crc32(__CLASS__ . __METHOD__);
		
		$keyCache1 = Utils::hashKey(array(
			$classMethodKey
			, $data
			, $configs
		));
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null !== $tmp) {
			return true;
		}
		
		if(isset($configs['deviceScreenWidthKey'])) {
			$deviceScreenWidthKey = $configs['deviceScreenWidthKey'];
		} else {
			$deviceScreenWidthKey = $this->_getKeyOfCurrentPageBaseOnDeviceScreenWidth();
		}
		
		$staticVarData_CSS_Minified = $this->_staticVarObject->get('CSS_Minified');
		
		$staticVarData_Collects_Clients = $this->_staticVarObject->get('Collects_Clients');
		
		$optimizeCSS_KeyProcessed = Utils::hashKey(array($keyCache1));
		
		if(!isset($staticVarData_CSS_Minified[$optimizeCSS_KeyProcessed])) {
			
			$staticVarData_CSS_Minified[$optimizeCSS_KeyProcessed] = array(
				'd' => ''
				,'r' => 1
				,'t' => time()
			);
			
			$options = OptimizeSpeed::getOption();
			
			$optimizeCSS = $this->di->getShared('optimizeCSS');
			
			$infoCss = $optimizeCSS->get_info_css($data);
			
			$tmp = array();
			
			if(isset($infoCss['href'])) {
				
				if(Utils::isUrlSameDomain($infoCss['href'],PepVN_Data::$defaultParams['domainName'],false)) {
					$tmp['href'] = $infoCss['href'];
				} else {
					$tmp = false;
				}
				
			} else if(isset($infoCss['code'])) {
				$tmp['code'] = $infoCss['code'];
			} else {
				$tmp = false;
			}
			
			$rsTwo = false;
			
			if($tmp) {
				$rsTwo = $this->_get_minify_css_item($tmp);
				unset($tmp, $options);
			}
			
			if($rsTwo && isset($rsTwo['data_processed']) && $rsTwo['data_processed']) {
				$staticVarData_CSS_Minified[$optimizeCSS_KeyProcessed] = array(
					'd' => $rsTwo['data_processed']
					,'r' => 1
					,'t' => time()
				);
				
			}
			
			$this->_staticVarObject->save($staticVarData_CSS_Minified, 'm', 'CSS_Minified');
		}
		
		if(isset($staticVarData_Collects_Clients[$deviceScreenWidthKey])) {
			
			$staticVarData_NotMedia = $this->_staticVarObject->get('data_not_media_'.$deviceScreenWidthKey);
			
			$staticVarData_NotMedia_Distinct = $this->_staticVarObject->get('data_not_media_distinct_'.$deviceScreenWidthKey);
			
			$updateStaticVarDataNotMediaStatus = false;
			
			//Match tag name first
			if(isset($staticVarData_Collects_Clients[$deviceScreenWidthKey]['atfc_tagName'])) {
				
				$key_atfc_tagName = Utils::hashKey(array(
					$optimizeCSS_KeyProcessed
					,$staticVarData_Collects_Clients[$deviceScreenWidthKey]['atfc_tagName']
				));
				
				if(!isset($staticVarData_NotMedia[$key_atfc_tagName.'0'])) {
					
					$updateStaticVarDataNotMediaStatus = true;
					
					$staticVarData_NotMedia[$key_atfc_tagName.'0'] = array(
						'd' => ''
						,'r' => 0
						,'t' => time()
					);
					
					$patternsNeedMatch = array(
						'html'
						,'body'
						,'header'
						//,'footer','header'
						,'@font-face'
						
					);
					
					foreach($staticVarData_Collects_Clients[$deviceScreenWidthKey]['atfc_tagName'] as $key1 => $value1) {
						$patternsNeedMatch[] = $value1;
					}
					
					$patternsNeedMatch = array_unique($patternsNeedMatch);
					$patternsNeedMatch = PepVN_Data::cleanPregPatternsArray($patternsNeedMatch);
					
					//$tmp = '#(@media([^\{]+?)\{)?(([^\{\}]+)[\,\s \t]+?('.implode('|',$patternsNeedMatch).')[\,\s \t]+[^\{]+?\{[^\}]+\})#is';
					//$tmp = '#([^\{\}]+)?([\,\s \t]+)?('.implode('|',$patternsNeedMatch).')([\,\s \t]+)?([^\{\}]+)?\{([^\{\}]+)\}#is';
					//$tmp = '#([^\{\}]*)([\,\s \t]+)('.implode('|',$patternsNeedMatch).')([\,\s \t]+)([^\{\}]*)\{([^\{\}]+)\}#is';
					//$tmp = '#([^\{\}]*)([\,\s \t]+)('.implode('|',$patternsNeedMatch).')([\,\s \t\{]+)([^\{\}]*\{)?([^\{\}]+)\}#is';
					$tmp = '#([^\{\}]*)((\,|[^a-z0-9\-\.\_\s \t\{\}\[\]]+)[\s \t]*)('.implode('|',$patternsNeedMatch).')([\,\s \t\{]+)([^\{\}]*\{)?([^\{\}]+)\}#is';
					
					$rsOne = $this->_parse_get_style_of_pattern($tmp, $staticVarData_CSS_Minified[$optimizeCSS_KeyProcessed]['d'], array(0));
					
					if(isset($rsOne[0]) && $rsOne[0] && is_array($rsOne[0]) && !empty($rsOne[0])) {
						
						$rsOne = $rsOne[0];
						
						foreach($rsOne as $key1 => $value1) {
							unset($rsOne[$key1]);
							
							$staticVarData_NotMedia[$key_atfc_tagName.$key1] = array(
								'd' => $value1
								,'r' => 0
								,'t' => time()
							);
							
							$keyValue1 = Hash::crc32b($value1);
							
							$staticVarData_NotMedia_Distinct[$keyValue1] = array(
								'd' => $value1
								,'r' => 0
								,'t' => time()
							);
						}
					}
					
				}
			}
			
			
			//Begin ID & Class
			$patternsNeedMatch = array();
			
			$arrayFieldsATFC = array(
				'atfc_id'
				, 'atfc_class'
			);
			
			foreach($arrayFieldsATFC as $value1) {
				
				if(
					isset($staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1]) 
					&& $staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1]
					&& is_array($staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1])
					&& !empty($staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1])
				) {
					foreach($staticVarData_Collects_Clients[$deviceScreenWidthKey][$value1] as $key2 => $value2) {
						
						if($value2) {
							$value2 = trim($value2);
							if($value2) {
								if('atfc_id' === $value1) {
									$patternsNeedMatch[] = '#'.$value2;
								} else {
									$patternsNeedMatch[] = '.'.$value2;
								}
							}
						}
					}
				}
			}
			
			if(!empty($patternsNeedMatch)) {
				
				$key_atfc_id_class = Utils::hashKey(array($optimizeCSS_KeyProcessed,$patternsNeedMatch));
				
				if(!isset($staticVarData_NotMedia[$key_atfc_id_class.'0'])) {
					
					$updateStaticVarDataNotMediaStatus = true;
					
					$staticVarData_NotMedia[$key_atfc_id_class.'0'] = array(
						'd' => ''
						,'r' => 0
						,'t' => time()
					);
					
					$patternsNeedMatch = array_unique($patternsNeedMatch);
					$patternsNeedMatch = PepVN_Data::cleanPregPatternsArray($patternsNeedMatch);
					
					//$tmp = '#(@media([^\{]+?)\{)?(([^\{\}]+)[\,\s \t]+?('.implode('|',$patternsNeedMatch).')[\,\s \t\.\#]+?[^\{]+?\{[^\}]+\})#is';
					//$tmp = '#(\@media([^\{]+?)\{)?(([^\{\}]+)?('.implode('|',$patternsNeedMatch).')[^\{\}]+?\{[^\}]+\})#is';
					
					$tmp = '#([^\{\}]+)?('.implode('|',$patternsNeedMatch).')([^\{\}]+)?\{([^\{\}]+)\}#is';
					
					$rsOne = $this->_parse_get_style_of_pattern($tmp, $staticVarData_CSS_Minified[$optimizeCSS_KeyProcessed]['d'], array(0));
					
					if(isset($rsOne[0]) && $rsOne[0] && is_array($rsOne[0]) && !empty($rsOne[0])) {
						
						$rsOne = $rsOne[0];
						
						foreach($rsOne as $key1 => $value1) {
							
							unset($rsOne[$key1]);
							
							$staticVarData_NotMedia[$key_atfc_id_class.$key1] = array(
								'd' => $value1
								,'r' => 0
								,'t' => time()
							);
							
							
							$keyValue1 = Hash::crc32b($value1);
							
							$staticVarData_NotMedia_Distinct[$keyValue1] = array(
								'd' => $value1
								,'r' => 0
								,'t' => time()
							);
						}
					}
				}
			}
			
			if($updateStaticVarDataNotMediaStatus) {
				
				$this->_staticVarObject->save($staticVarData_NotMedia, 'm', 'data_not_media_'.$deviceScreenWidthKey);
				
				$this->_staticVarObject->save($staticVarData_NotMedia_Distinct, 'm', 'data_not_media_distinct_'.$deviceScreenWidthKey);
				
			}
			
		}
		
		PepVN_Data::$cacheObject->set_cache($keyCache1, true);
		
		return true;
		
	}
	
	
	public function process_get_all_css($data, $configs = array())
	{
	
		$classMethodKey = crc32(__CLASS__ . __METHOD__);
		
		$keyCache1 = Utils::hashKey(array(
			$classMethodKey
			, $data
			, $configs
		));
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null !== $tmp) {
			return true;
		} else {
			if(!isset($configs['process_if_not_exists'])) {
				
				$queue = $this->di->getShared('queue');
				
				$queue->add(
					'WPOptimizeSpeedByxTraffic_OptimizeSpeed_optimize_google_pagespeed_process_get_all_css'
					, array(
						'data' => $data
						, 'configs' => array(
							'deviceScreenWidthKey' => $this->_getKeyOfCurrentPageBaseOnDeviceScreenWidth()
						)
					)
				);
				
				return true;
			}
		}
		
		foreach($data as $key1 => $value1) {
			unset($data[$key1]);
			
			$this->process_css_item($value1,$configs);
		}
		
		PepVN_Data::$cacheObject->set_cache($keyCache1, true);
		
	}
	
	
	public function get_style_has_media($configs = array())
	{
		if(isset($configs['deviceScreenWidthKey'])) {
			$deviceScreenWidthKey = $configs['deviceScreenWidthKey'];
		} else {
			$deviceScreenWidthKey = $this->_getKeyOfCurrentPageBaseOnDeviceScreenWidth();
		}
		
		$keyCache = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			, $deviceScreenWidthKey
		));
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(null !== $tmp) {
			return $tmp;
		} else {
			if(!isset($configs['process_if_not_exists'])) {
				
				$queue = $this->di->getShared('queue');
				
				$queue->add(
					'WPOptimizeSpeedByxTraffic_OptimizeSpeed_optimize_google_pagespeed_get_style_has_media'	//job_name
					, array(
						'configs' => array(
							'deviceScreenWidthKey' => $deviceScreenWidthKey
						)
					)	//job_data
				);
				
				return '';
			}
		}
		
		$resultData = '';
		
		$staticVarData_NotMedia = $this->_staticVarObject->get('data_not_media_'.$deviceScreenWidthKey);
		
		$staticVarData_NotMedia_Distinct = $this->_staticVarObject->get('data_not_media_distinct_'.$deviceScreenWidthKey);
		
		if($staticVarData_NotMedia_Distinct && is_array($staticVarData_NotMedia_Distinct) && !empty($staticVarData_NotMedia_Distinct)) {
			foreach($staticVarData_NotMedia_Distinct as $key1 => $value1) {
				
				unset($staticVarData_NotMedia_Distinct[$key1]);
				
				if($value1['d'] && !empty($value1['d'])) {
					$resultData .= $this->_parse_get_media_of_style($value1['d']);
				}
				
				unset($key1,$value1);
			}
		}
		
		$resultData = pepvn_MinifyCss($resultData);
		
		PepVN_Data::$cacheObject->set_cache($keyCache,$resultData);
		
		return $resultData;
	}
	
	public function action_before_process_html_output_buffer($buffer)
	{
		$optimizeCSS = $this->di->getShared('optimizeCSS');
		
		$this->process_get_all_css($optimizeCSS->get_all_css($buffer));
		
	}
	
	public function filter_before_set_cache_output_buffer($buffer)
	{
		
		$styleProcessed = $this->get_style_has_media();
		
		if($styleProcessed) {
			$styleProcessed = '<style type="text/css" wppepvn-remove-after-window-loaded="1" xtraffic-not-async>'.$styleProcessed.'</style>';
			$buffer = preg_replace('#</title>#is','</title>'.$styleProcessed,$buffer,1,$count);
		}
		
		return $buffer;
	}
	
	
	
	public function action_queue_jobs($job)
	{
		if(
			isset($job['job_name'])
			&& $job['job_name']
			
			&& isset($job['job_data'])
			&& $job['job_data']
		) {
			
			if('WPOptimizeSpeedByxTraffic_OptimizeSpeed_optimize_google_pagespeed_process_get_all_css' === $job['job_name']) {
				$this->process_get_all_css(
					$job['job_data']['data']
					, array_merge(
						array(
							'process_if_not_exists' => true
						)
						, (array)$job['job_data']['configs']
					)
				);
			} else if('WPOptimizeSpeedByxTraffic_OptimizeSpeed_optimize_google_pagespeed_get_style_has_media' === $job['job_name']) {
				$this->get_style_has_media(array_merge(
					array(
						'process_if_not_exists' => true
					)
					, (array)$job['job_data']['configs']
				));
			}
		}
	}
}
<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\TempDataAndCacheFile
	, WpPepVN\System
;

class OptimizeJS
{
	public $di = false;
	
	private $_tempData = array();
	
	private $_configs = array();
	
	private $_folderStorePath = false;
	private $_folderStoreUrl = false;
	
	private $patternsIgnoredJavascript_Uri = array(
		'adsbygoogle'
		,'/wp-admin/'
		,'stats.wp.com'
	);
	
	private $_patternsIgnoredJavascript_Code = '#(document\.write|_ase\.push|st_go|_stq\.)#is';
	
	private $_patternsJavascriptNotAsync_Code = '#(document\.write|_ase\.push|st_go|adsbygoogle)#is';
	
	private $_patternsJavascriptNotAsync_Uri = '#(stats\.wp\.com|googlesyndication\.com|adsbygoogle\.js)#is';
	
	private $_patternsJavascriptUnchanged_Code = '#(adsbygoogle)#is';
	
	private $_patternsJavascriptUnchanged_Uri = '#(adsbygoogle\.js)#is';
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$this->_folderStorePath = WP_CONTENT_PEPVN_DIR . 'cache' . DIRECTORY_SEPARATOR . 'static-files' . DIRECTORY_SEPARATOR;
		$this->_folderStoreUrl = WP_CONTENT_PEPVN_URL . 'cache/static-files/';
		
		if(!is_dir($this->_folderStorePath)) {
			System::mkdir($this->_folderStorePath);
		}
		
		$options = OptimizeSpeed::getOption();
		
		$this->_configs['round_time'] = $options['optimize_cache_cachetimeout'] * 3;
		
		$this->_configs['round_time'] = (int)$this->_configs['round_time'];
		
	}
	
	public function fix_javascript_code($input_data) 
	{
		$patterns = array(
			'#document.write\((\'|\")(.+)\1\)#is' => 'wpOptimizeByxtraffic_appendHtml(document.getElementsByTagName("body")[0],$1$2$1)' 
		);
		
		return preg_replace(array_keys($patterns), array_values($patterns), $input_data);
	}
	
	private function _get_all_javascripts_for_process($text) 
	{
		$keyCache = Utils::hashKey(array(
			__METHOD__
			, $text
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(null === $resultData) {
			
			$resultData = $this->get_all_javascripts($text);
			
			foreach($resultData as $key1 => $value1) {
				
				$checkStatus1 = false;
				
				if($value1) {
					if(!preg_match('#<(script)[^><]*xtraffic-exclude[^><]*/?>.*?</script>#is',$value1,$matched2)) {
						$checkStatus1 = true;
					}
					$matched2 = 0;
				}
				
				if(true !== $checkStatus1) {
					unset($resultData[$key1]);
				}
				
				$value1 = 0;
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache, $resultData);
            
		}
		
		return $resultData;
		
	}
	
	public function get_all_javascripts($text) 
	{
		$keyCache = Utils::hashKey(array(
			__METHOD__
			, $text
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(null === $resultData) {
			$resultData = array();
			
			preg_match_all('#<script[^><]*>.*?</script>#is',$text,$matched1);
			
			if(isset($matched1[0]) && !PepVN_Data::isEmptyArray($matched1[0])) {
				$matched1 = $matched1[0];
				foreach($matched1 as $key1 => $value1) {
					unset($matched1[$key1]);
					
					if($value1) {
						
						$checkStatus1 = true;
						
						if(preg_match('#type=(\'|")([^"\']+)\1#i',$value1,$matched2)) {
							if(isset($matched2[2]) && $matched2[2]) {
								$matched2 = trim($matched2[2]);
								if($matched2) {
									$checkStatus1 = false;
									if(false !== stripos($matched2,'javascript')) {
										$checkStatus1 = true;
									}
								}
							}
						}
						
						if($checkStatus1) {
							$resultData[] = $value1;
						}
						
					}
					
					$value1 = 0;
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache, $resultData);
		}
		
		return $resultData;
		
	}
	
	private function _process_each_item($value1, $options)
	{
		$resultData = array(
			'data_processed' => false
		);
		
		$isMinifyJavascriptStatus = false;
		if(isset($options['optimize_javascript_minify_javascript_enable']) && ('on' === $options['optimize_javascript_minify_javascript_enable'])) {
			$isMinifyJavascriptStatus = true;
		}
		
		$isAsynchronousJavascriptLoadingStatus = false;
		if(isset($options['optimize_javascript_asynchronous_javascript_loading_enable']) && ('on' === $options['optimize_javascript_asynchronous_javascript_loading_enable'])) {
			$isAsynchronousJavascriptLoadingStatus = true;
		}
		
		unset($options);
		
		$optimizeCDN = $this->di->getShared('optimizeCDN');
		
		if(isset($value1['src'])) {
			
			$remote = $this->di->getShared('remote');
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($value1['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $remote->get($value1['src'],array(
					'cache_timeout' => WP_PEPVN_CACHE_TIMEOUT_NORMAL
				));
				
				if(false !== $resultData['data_processed']) {
					if($isMinifyJavascriptStatus) {
						$resultData['data_processed'] = pepvn_MinifyJavascript($resultData['data_processed']);
					}
					
					$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
					
					PepVN_Data::$cacheObject->set_cache($value1['key_processed'],$resultData['data_processed']);
				}
				
			}
			
		} else if(isset($value1['code'])) {
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($value1['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $value1['code'];
				
				if($isMinifyJavascriptStatus) {
					$resultData['data_processed'] = pepvn_MinifyJavascript($resultData['data_processed']);
				}
				
				$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
				
				PepVN_Data::$cacheObject->set_cache($value1['key_processed'],trim($resultData['data_processed']));
				
			}
			
		}
		
		return $resultData;
	}
	
	private function _process_combine($rsGetAllJavascripts, $options)
	{
		$classMethodKey = crc32(__CLASS__ . '_' . __METHOD__);
		
		$resultData = array(
			'data_need_replace' => array()
		);
		
		System::mkdir($this->_folderStorePath);
		
		$isMinifyJavascriptStatus = false;
		if(isset($options['optimize_javascript_minify_javascript_enable']) && ('on' === $options['optimize_javascript_minify_javascript_enable'])) {
			$isMinifyJavascriptStatus = true;
		}
		
		$isAsynchronousJavascriptLoadingStatus = false;
		if(isset($options['optimize_javascript_asynchronous_javascript_loading_enable']) && ('on' === $options['optimize_javascript_asynchronous_javascript_loading_enable'])) {
			$isAsynchronousJavascriptLoadingStatus = true;
		}
		
		$tmp = array();
		
		foreach($rsGetAllJavascripts as $key1 => $value1) {
			$tmp[] = $value1['key'];
			unset($key1, $value1);
		}
		
		$fileCombinedStorePath = $this->_folderStorePath . Utils::hashKey(array($classMethodKey,'processed',$tmp)) . '.js';
		
		unset($tmp);
		
		$isProcessSuccessStatus = true;
		
		$rsOne = OptimizeSpeed::checkFileProcess($fileCombinedStorePath);
		
		if($rsOne['need_process']) {
			
			if(false !== file_put_contents($fileCombinedStorePath,'')) {
				
				$fileHandle = fopen($fileCombinedStorePath,'w+');
				
				if($fileHandle) {
					
					foreach($rsGetAllJavascripts as $key1 => $value1) {
						
						unset($rsGetAllJavascripts[$key1]);
						
						$rsTwo = $this->_process_each_item($value1, $options);
						
						if(
							(false !== $rsTwo['data_processed'])
							&& (null !== $rsTwo['data_processed'])
						) {
							if(false === fwrite($fileHandle, $rsTwo['data_processed'] . PHP_EOL)) {
								$isProcessSuccessStatus = false;
								unset($rsTwo);
								break;
							} else {
								$resultData['data_need_replace'][$value1['old']] = '';
							}
						} else {
							$isProcessSuccessStatus = false;
							unset($rsTwo);
							break;
						}
						
						unset($rsTwo);
					}
					
					fclose($fileHandle);
					
					if(false === $isProcessSuccessStatus) {
						file_put_contents($fileCombinedStorePath,'');
					}
					
				} else {
					$isProcessSuccessStatus = false;
				}
				
				unset($fileHandle);
				
			} else {
				$isProcessSuccessStatus = false;
			}
			
			unset($rsGetAllJavascripts);
			
		} else if($rsOne['file_valid']) {
			
			touch($fileCombinedStorePath);
			
			foreach($rsGetAllJavascripts as $key1 => $value1) {
				unset($rsGetAllJavascripts[$key1]);
				$resultData['data_need_replace'][$value1['old']] = '';
				unset($value1);
			}
			
			unset($rsGetAllJavascripts);
			
		} else {
			$isProcessSuccessStatus = false;
			unset($rsGetAllJavascripts);
		}
		
		if(true === $isProcessSuccessStatus) {
			$resultData['file_url'] = str_replace($this->_folderStorePath,$this->_folderStoreUrl,$fileCombinedStorePath);
		} else {
			$resultData['data_need_replace'] = array();
		}
		
		return $resultData;
	}
	
	public function _move_js_to_bottom($text)
	{
		$classMethodKey = crc32(__CLASS__ . '_' . __METHOD__);
		$keyCache = Utils::hashKey(array(
			$classMethodKey
			, $text
		));
		
		$tmp = TempDataAndCacheFile::get_cache($keyCache);
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		$patternJavascriptExcludeUrl = array();
		
		//cleanPregPatternsArray
		if(isset($options['optimize_javascript_exclude_url']) && $options['optimize_javascript_exclude_url']) {
			$tmp = $options['optimize_javascript_exclude_url'];
			$tmp = PepVN_Data::cleanPregPatternsArray($tmp);
			if(!PepVN_Data::isEmptyArray($tmp)) {
				$patternJavascriptExcludeUrl = array_merge($patternJavascriptExcludeUrl, $tmp);
			}
		}
		
		$patternJavascriptExcludeUrl = implode('|',$patternJavascriptExcludeUrl);
		$patternJavascriptExcludeUrl = trim($patternJavascriptExcludeUrl);
		
		$arrayFindAndReplace = array();
		
		$textAppendToBody = '';
		
		$patternsEscaped = array();
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'iframe');
		
		$text = $rsOne['content'];
		if(!empty($rsOne['patterns'])) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		unset($rsOne);
		
		$rsGetAllJavascripts = $this->get_all_javascripts($text);
		
		if(!PepVN_Data::isEmptyArray($rsGetAllJavascripts)) {
		
			foreach($rsGetAllJavascripts as $key1 => $value1) {
				
				unset($rsGetAllJavascripts[$key1]);
				
				//link
				preg_match('#<script[^><]*?src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2);
				
				if(isset($matched2[2]) && $matched2[2]) {
					
					$matched2 = trim($matched2[2]);
					
					$isProcessStatus1 = true;
					
					if(preg_match('#<(script)[^><]*xtraffic-exclude[^><]*/?>.*?</script>#is',$value1)) {
						$isProcessStatus1 = false;
					}
					
					if(true === $isProcessStatus1) {
						if($patternJavascriptExcludeUrl) {
							if(preg_match('#('.$patternJavascriptExcludeUrl.')#i',$matched2)) {
								$isProcessStatus1 = false;
							}
						}
					}
					
					if(true === $isProcessStatus1) {
						if(preg_match($this->_patternsJavascriptUnchanged_Uri,$matched2)) {
							$isProcessStatus1 = false;
						}
					}
					
					if(true === $isProcessStatus1) {
						
						$arrayFindAndReplace[$value1] = '';
						$textAppendToBody .= $value1;
					}
					
					unset($matched2);
					
				} else {
					
					//code
					preg_match('/<script[^><]*>(.*?)<\/script>/is',$value1,$matched2);
					
					if(isset($matched2[1]) && $matched2[1]) {
						$matched2 = trim($matched2[1]);
						if($matched2) {
							$isProcessStatus1 = true;
							
							if(true === $isProcessStatus1) {
								if(preg_match('#<(script)[^><]*xtraffic-exclude[^><]*/?>.*?</script>#is',$value1)) {
									$isProcessStatus1 = false;
								}
							}
							
							if(true === $isProcessStatus1) {
								if(preg_match($this->_patternsIgnoredJavascript_Code,$matched2)) {
									$isProcessStatus1 = false;
								}
							}
							
							if(true === $isProcessStatus1) {
								if(preg_match($this->_patternsJavascriptUnchanged_Code,$matched2)) {
									$isProcessStatus1 = false;
								}
							}
							
							if(true === $isProcessStatus1) {
								$arrayFindAndReplace[$value1] = '';
								$textAppendToBody .= $value1;
							}
							
						}
					}
					
					unset($matched2);
				}
				
				unset($value1,$matched2);
				
			}
			
		}
		
		if(!empty($arrayFindAndReplace)) {
			$text = str_replace(array_keys($arrayFindAndReplace),array_values($arrayFindAndReplace),$text); 
		}
		unset($arrayFindAndReplace);
		
		if($textAppendToBody) {
			$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToBody,$text);
		}
		
		if(!empty($patternsEscaped)) {
			$text = str_replace(array_values($patternsEscaped),array_keys($patternsEscaped),$text); 
		}
		unset($patternsEscaped);
		
		TempDataAndCacheFile::set_cache($keyCache, $text);
		
		return $text;
		
	}
	
	public function process_html($text, $options)
	{
		
		$classMethodKey = crc32(__CLASS__ . '_' . __METHOD__);
		
		$keyCache = Utils::hashKey(array(
			$classMethodKey
			, $text
			, $options
		));
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		$round_time = $this->_configs['round_time'];
		
		if($round_time < 86400) {
			$round_time = 86400;
		}
		
		$textAppendToBody = '';
		
		if(!is_dir($this->_folderStorePath)) {
			System::mkdir($this->_folderStorePath);
		}
		
		$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		$fullDomainNamePregQuote = Utils::preg_quote($fullDomainName); 
		
		$arrayDataTextNeedReplace = array();
		
		$patternJavascriptExcludeUrl = $this->patternsIgnoredJavascript_Uri;
		
		//cleanPregPatternsArray
		if(isset($options['optimize_javascript_exclude_url']) && $options['optimize_javascript_exclude_url']) {
			$tmp = $options['optimize_javascript_exclude_url'];
			$tmp = PepVN_Data::cleanPregPatternsArray($tmp);
			if(!PepVN_Data::isEmptyArray($tmp)) {
				$patternJavascriptExcludeUrl = array_merge($patternJavascriptExcludeUrl, $tmp);
			}
		}
		
		$patternJavascriptExcludeUrl = implode('|',$patternJavascriptExcludeUrl);
		$patternJavascriptExcludeUrl = trim($patternJavascriptExcludeUrl);
		
		$isCombineJavascriptsStatus = false;
		if(isset($options['optimize_javascript_combine_javascript_enable']) && ('on' === $options['optimize_javascript_combine_javascript_enable'])) {	
			$isCombineJavascriptsStatus = true;
		}
		
		$isExcludeInlineJavascriptStatus = false;
		if(isset($options['optimize_javascript_exclude_inline_javascript_enable']) && ('on' === $options['optimize_javascript_exclude_inline_javascript_enable'])) {
			$isExcludeInlineJavascriptStatus = true;
		}
		
		$isExcludeExternalJavascriptStatus = false;
		if(isset($options['optimize_javascript_exclude_external_javascript_enable']) && ('on' === $options['optimize_javascript_exclude_external_javascript_enable'])) {
			$isExcludeExternalJavascriptStatus = true;
		}
		
		$isMinifyJavascriptStatus = false;
		if(isset($options['optimize_javascript_minify_javascript_enable']) && ('on' === $options['optimize_javascript_minify_javascript_enable'])) {
			$isMinifyJavascriptStatus = true;
		}
		
		$isAsynchronousJavascriptLoadingStatus = false;
		if(isset($options['optimize_javascript_asynchronous_javascript_loading_enable']) && ('on' === $options['optimize_javascript_asynchronous_javascript_loading_enable'])) {
			$isAsynchronousJavascriptLoadingStatus = true;
		}
		
		if(isset($options['optimize_javascript_move_bottom_enable']) && ('on' === $options['optimize_javascript_move_bottom_enable'])) {
			$text = $this->_move_js_to_bottom($text);
		}
		
		$patternsEscaped = array();
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'iframe');
		
		$text = $rsOne['content'];
		if(!empty($rsOne['patterns'])) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		unset($rsOne);
		
		$rsGetAllJavascripts = $this->get_all_javascripts($text);
		
		if(!PepVN_Data::isEmptyArray($rsGetAllJavascripts)) {
			
			$remote = $this->di->getShared('remote');
			
			$rsGetAllJavascripts1_KeyCache1 = Utils::hashKey(array(
				$classMethodKey
				, 'rsGetAllJavascripts1'
				, $rsGetAllJavascripts
			));
			
			$rsGetAllJavascripts1 = TempDataAndCacheFile::get_cache($rsGetAllJavascripts1_KeyCache1);
			
			if(null === $rsGetAllJavascripts1) {
				
				$rsGetAllJavascripts1 = array();
				
				foreach($rsGetAllJavascripts as $key1 => $value1) {
					
					unset($rsGetAllJavascripts[$key1]);
					
					//link
					preg_match('#<script[^><]*?src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2);
					
					if(isset($matched2[2]) && $matched2[2]) {
						
						$matched2 = trim($matched2[2]);
						
						$isProcessStatus1 = true;
						
						if(preg_match('#<(script)[^><]*xtraffic-exclude[^><]*/?>.*?</script>#is',$value1)) {
							$isProcessStatus1 = false;
						}
						
						if(true === $isProcessStatus1) {
							if($patternJavascriptExcludeUrl) {
								if(preg_match('#('.$patternJavascriptExcludeUrl.')#i',$matched2)) {
									$isProcessStatus1 = false;
								}
							}
						}
						
						if(true === $isProcessStatus1) {
							if($isExcludeExternalJavascriptStatus) {
								if(!preg_match('#^(https?)?:?(//)?'.$fullDomainNamePregQuote.'#i',$matched2)) {
									$isProcessStatus1 = false;
								}
							}
						}
						
						$rsGetAllJavascripts1[$key1] = array(
							'src' => $matched2
							, 'key' => Utils::hashKey(array($classMethodKey,$matched2))
							, 'old' => $value1
						);
						
						if(preg_match($this->_patternsJavascriptNotAsync_Uri,$matched2)) {
							$isProcessStatus1 = false;
							$rsGetAllJavascripts1[$key1]['keepOriginal'] = true;
						} else {
							$rsRemote = $remote->get($matched2,array(
								'cache_timeout' => WP_PEPVN_CACHE_TIMEOUT_NORMAL
							));
							
							if($rsRemote) {
								if(preg_match($this->_patternsJavascriptNotAsync_Code,$rsRemote)) {
									$isProcessStatus1 = false;
									$rsGetAllJavascripts1[$key1]['keepOriginal'] = true;
								}
							}
							
							unset($rsRemote);
						}
						
						if(false === $isProcessStatus1) {
							$rsGetAllJavascripts1[$key1]['exclude'] = true;
						}
						
						unset($matched2);
						
					} else {
						
						//code
						preg_match('/<script[^><]*>(.*?)<\/script>/is',$value1,$matched2);
						
						if(isset($matched2[1]) && $matched2[1]) {
							$matched2 = trim($matched2[1]);
							if($matched2) {
								$isProcessStatus1 = true;
								
								$isKeepOriginalStatus1 = false;
								
								if(true === $isProcessStatus1) {
									if($isExcludeInlineJavascriptStatus) {
										$isProcessStatus1 = false;
									}
								}
								
								if(true === $isProcessStatus1) {
									if(preg_match('#<(script)[^><]*xtraffic-exclude[^><]*/?>.*?</script>#is',$value1)) {
										$isProcessStatus1 = false;
									}
								}
								
								if(true === $isProcessStatus1) {
									if(preg_match($this->_patternsIgnoredJavascript_Code,$matched2)) {
										$isProcessStatus1 = false;
									}
								}
								
								if(preg_match($this->_patternsJavascriptNotAsync_Code,$matched2)) {
									$isProcessStatus1 = false;
									$isKeepOriginalStatus1 = true;
								}
								
								$rsGetAllJavascripts1[$key1] = array(
									'code' => $matched2
									, 'key' => Utils::hashKey(array($classMethodKey,$matched2))
									, 'old' => $value1
								);
								
								if(true === $isKeepOriginalStatus1) {
									$rsGetAllJavascripts1[$key1]['keepOriginal'] = true;
									$isProcessStatus1 = false;
								}
								
								if(false === $isProcessStatus1) {
									$rsGetAllJavascripts1[$key1]['exclude'] = true;
								}
								
							}
						}
						
						unset($matched2);
					}
					
					if(isset($rsGetAllJavascripts1[$key1]['key'])) {
						$rsGetAllJavascripts1[$key1]['key_processed'] = Utils::hashKey(array(
							$classMethodKey
							,'processed'
							,$rsGetAllJavascripts1[$key1]['key']
							,PepVN_Data::getRoundUpTime($round_time)
						));
					}
					
					unset($value1,$matched2);
					
				}
				
				TempDataAndCacheFile::set_cache($rsGetAllJavascripts1_KeyCache1, $rsGetAllJavascripts1);
				
			}
			
			$rsGetAllJavascripts = $rsGetAllJavascripts1;
			unset($rsGetAllJavascripts1);
			
			$isProcessSuccessStatus = true;
			
			$allJavascriptsNeedCombine = array();
			
			foreach($rsGetAllJavascripts as $key1 => $value1) {
				
				if($isCombineJavascriptsStatus) {	//combine
					
					if(isset($value1['exclude'])) {
						
						if(!empty($allJavascriptsNeedCombine)) {
							
							$rsOne = $this->_process_combine($allJavascriptsNeedCombine, $options);
							
							if(isset($rsOne['file_url']) && $rsOne['file_url']) {
								$arrayDataTextNeedReplace = array_merge($arrayDataTextNeedReplace, $rsOne['data_need_replace']);
								
								if($isAsynchronousJavascriptLoadingStatus) {
									
									if(isset($value1['keepOriginal'])) {
										$tmp = false;
									} else {
										$tmp = array(
											'load_by' => 'js_data'//js_data,div_tag
											,'type' => 'js'
										);
										
										if(isset($value1['src'])) {
											$tmp['url'] = $value1['src'];
										} else if(isset($value1['code'])) {
											$tmp['code'] = $value1['code'];
										}
										
									}
									
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag(array(
										'url' => $rsOne['file_url']
										,'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'js'
									)) . ( $tmp ? OptimizeSpeed::parse_load_html_scripts_by_tag($tmp) : $value1['old'] );
									
									unset($tmp);
									
								} else {
									$arrayDataTextNeedReplace[$value1['old']] ='<script language="javascript" type="text/javascript" src="'.$rsOne['file_url'].'" ></script>'.$value1['old'];
								}
								
							} else {
								$isProcessSuccessStatus = false;
								break;
							}
							
							unset($rsOne);
						} else {
							
							if($isAsynchronousJavascriptLoadingStatus) {
								
								if(isset($value1['keepOriginal'])) {
									$tmp = false;
								} else {
									$tmp = array(
										'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'js'
									);
									
									if(isset($value1['src'])) {
										$tmp['url'] = $value1['src'];
									} else if(isset($value1['code'])) {
										$tmp['code'] = $value1['code'];
									}
									
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								}
								
								unset($tmp);
								
							}
							
						}
						
						$allJavascriptsNeedCombine = array();
						
					} else {
						$allJavascriptsNeedCombine[$key1] = $value1;
					}
					
				} else {	//not combine
					
					if($isMinifyJavascriptStatus) {	//minify
						
						if(isset($value1['exclude'])) {	//exclude (not minify)
							
							if($isAsynchronousJavascriptLoadingStatus) {//only process when load async
								
								if(isset($value1['keepOriginal'])) {
									$tmp = false;
								} else {
									$tmp = array(
										'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'js'
									);
									
									if(isset($value1['src'])) {
										$tmp['url'] = $value1['src'];
									} else if(isset($value1['code'])) {
										$tmp['code'] = $value1['code'];
									}
									
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								}
								
							}
							
						} else {
							
							$valueProcessedCode = false;
							$fileStoreUrl = false;
							
							if(isset($value1['src'])) {
								
								$fileStorePath = $this->_folderStorePath . $value1['key_processed'].'.js';
								
								$rsOne = OptimizeSpeed::checkFileProcess($fileStorePath);
								
								if($rsOne['need_process']) {
									
									if(false !== file_put_contents($fileStorePath,'')) {
										
										$rsTwo = $this->_process_each_item($value1, $options);
										
										if(
											(false !== $rsTwo['data_processed'])
											&& (null !== $rsTwo['data_processed'])
										) {
											if(false === file_put_contents($fileStorePath, $rsTwo['data_processed'])) {
												$isProcessSuccessStatus = false;
												break;
											}
										} else {
											$isProcessSuccessStatus = false;
											unset($rsTwo);
											break;
										}
										
										unset($rsTwo);
									} else {
										$isProcessSuccessStatus = false;
										break;
									}
									
								} else if($rsOne['file_valid']) {
									touch($fileStorePath);
								} else {
									$isProcessSuccessStatus = false;
									break;
								}
								
								$fileStoreUrl = str_replace($this->_folderStorePath,$this->_folderStoreUrl,$fileStorePath);
								
							} else if(isset($value1['code'])) {
								
								$rsTwo = $this->_process_each_item($value1, $options);
								
								if(
									(false !== $rsTwo['data_processed'])
									&& (null !== $rsTwo['data_processed'])
								) {
									$valueProcessedCode = $rsTwo['data_processed'];
									unset($rsTwo);
								} else {
									$isProcessSuccessStatus = false;
									unset($rsTwo);
									break;
								}
							}
							
							if($isAsynchronousJavascriptLoadingStatus) {
								
								$tmp = array(
									'load_by' => 'js_data'//js_data,div_tag
									,'type' => 'js'
								);
								
								if($fileStoreUrl) {
									$tmp['url'] = $fileStoreUrl;
								} else if($valueProcessedCode) {
									if(preg_match($this->_patternsJavascriptNotAsync_Code,$valueProcessedCode)) {
										$tmp = false;
									} else {
										$tmp['code'] = $valueProcessedCode;
									}
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								} else {
									if($fileStoreUrl) {
										$arrayDataTextNeedReplace[$value1['old']] = '<script language="javascript" type="text/javascript" src="'.$fileStoreUrl.'" ></script>';
									} else if($valueProcessedCode) {
										$arrayDataTextNeedReplace[$value1['old']] = '<script language="javascript" type="text/javascript">'.$valueProcessedCode.'</script>';
									}
								}
								
							} else {
								if($fileStoreUrl) {
									$arrayDataTextNeedReplace[$value1['old']] = '<script language="javascript" type="text/javascript" src="'.$fileStoreUrl.'" ></script>';
								} else if($valueProcessedCode) {
									$arrayDataTextNeedReplace[$value1['old']] = '<script language="javascript" type="text/javascript">'.$valueProcessedCode.'</script>';
								}
							}
							
							unset($fileStoreUrl,$valueProcessedCode);
						}
						
					} else {	//not minify
						
						if($isAsynchronousJavascriptLoadingStatus) {	//async
							
							$tmp = array(
								'load_by' => 'js_data'//js_data,div_tag
								,'type' => 'js'
							);
							
							if(isset($value1['src'])) {
								$tmp['url'] = $value1['src'];
							} else if(isset($value1['code'])) {
								$tmp['code'] = $value1['code'];
							}
							
							$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
							
							unset($tmp);
							
						}
					}
				}
				
			}
			
			
			if(!empty($allJavascriptsNeedCombine)) {
				
				$rsOne = $this->_process_combine($allJavascriptsNeedCombine, $options);
				
				if(isset($rsOne['file_url']) && $rsOne['file_url']) {
					
					$arrayDataTextNeedReplace = array_merge($arrayDataTextNeedReplace, $rsOne['data_need_replace']);
					
					if($isAsynchronousJavascriptLoadingStatus) {
						$textAppendToBody .= OptimizeSpeed::parse_load_html_scripts_by_tag(array(
							'url' => $rsOne['file_url']
							,'load_by' => 'js_data'//js_data,div_tag 
							,'type' => 'js'
						));
					} else {
						$textAppendToBody .= '<script language="javascript" type="text/javascript" src="'.$rsOne['file_url'].'" ></script>';
					}
					
				} else {
					$isProcessSuccessStatus = false;
				}
				
				unset($rsOne);
			}
			
			unset($allJavascriptsNeedCombine);
			
			if(false === $isProcessSuccessStatus) {//not replace when has 1 error
				$arrayDataTextNeedReplace = array();
			}
			
		}
		
		if(!empty($arrayDataTextNeedReplace)) {
			$text = str_replace(array_keys($arrayDataTextNeedReplace), array_values($arrayDataTextNeedReplace), $text);
			unset($arrayDataTextNeedReplace);
		}
		
		if($textAppendToBody) {
			$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToBody,$text);
		}
		
		if(!empty($patternsEscaped)) {
			$text = str_replace(array_values($patternsEscaped),array_keys($patternsEscaped),$text); 
		}
		unset($patternsEscaped);
		
		PepVN_Data::$cacheObject->set_cache($keyCache, $text);
		
		return $text;
	}
}
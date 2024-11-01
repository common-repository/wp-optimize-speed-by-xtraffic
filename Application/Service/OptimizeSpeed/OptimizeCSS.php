<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WpPepVN\System
	, WPOptimizeByxTraffic\Application\Service\CSSFixer
	, WPOptimizeByxTraffic\Application\Service\TempDataAndCacheFile
;

class OptimizeCSS
{
	public $di = false;
	
	private $_tempData = array();
	
	private $_folderStorePath = false;
	private $_folderStoreUrl = false;
	
	private $patternsIgnoredCSS_Uri = array(
		'/wp-admin/'
	);
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$this->_folderStorePath = WP_CONTENT_PEPVN_DIR . 'cache' . DIRECTORY_SEPARATOR . 'static-files' . DIRECTORY_SEPARATOR;
		$this->_folderStoreUrl = WP_CONTENT_PEPVN_URL . 'cache/static-files/';
		
		if(!is_dir($this->_folderStorePath)) {
			System::mkdir($this->_folderStorePath);
		}
		
	}
	
	public function get_all_css($text) 
	{
	
		$resultData = array();
		
		preg_match_all('#((<(link)[^><]*/?>.*?(</link>)?)|(<(style)[^><]*>.*?</style>))#is',$text,$matched1);
		
		if(isset($matched1[0]) && !PepVN_Data::isEmptyArray($matched1[0])) {
			$matched1 = $matched1[0];
			foreach($matched1 as $key1 => $value1) {
				unset($matched1[$key1]);
				
				if($value1) {
					
					if(preg_match('#<link[^><]*/?>.*(</link>)?#is',$value1)) {//is link css
						if(preg_match('#type=(\'|")text/css\1#i',$value1)) {
							$resultData[] = $value1;
							/*
							//if(!preg_match('#<link[^><]*xtraffic-exclude[^><]* /?>.*(</link>)?#is',$value1)) {
								$resultData[] = $value1;
							}
							
							if($hook->has_action('after_get_css_item')) {
								$hook->do_action('after_get_css_item', $value1);
							}
							*/
						}
					} else {//preg_match('#<(style)[^><]*>.*?</\1>#is',$text,$matched1);
						$resultData[] = $value1;
						/*
						if(!preg_match('#<(style)[^><]*xtraffic-exclude[^><]* /?>.*?(</style>)?#is',$value1)) {
							$resultData[] = $value1;
						}
						
						if($hook->has_action('after_get_css_item')) {
							$hook->do_action('after_get_css_item', $value1);
						}
						*/
						
					}
				}
				
				unset($value1);
			}
		}
		
		unset($matched1);
		
		return $resultData;
	}
	
	public function get_info_css($text) 
	{
		$resultData = array(
			'type' => ''
		);
		
		if(preg_match('#<link([^><]*)/?>.*?(</link>)?#is', $text, $matched1)) {
			
			$resultData['type'] = 'link';
			
			if(isset($matched1[1]) && $matched1[1]) {
				
				$matched1 = trim($matched1[1]);
				
				if(preg_match('#\s*href=(\'|")([^\'\"]+)\1#i',$matched1,$matched2)) {
					
					if(isset($matched2[2]) && $matched2[2]) {
						$resultData['href'] = trim($matched2[2]);
					}
				}
				
				if(preg_match('#\s*media=(\'|")([^\'\"]+)\1#i',$matched1,$matched2)) {
					
					if(isset($matched2[2]) && $matched2[2]) {
						$resultData['media'] = trim($matched2[2]);
					}
				}
			}
			
		} else if(preg_match('#<style([^><]*)>(.*?)</style>#is',$text,$matched1)) {
			
			$resultData['type'] = 'inline';
			
			$resultData['code'] = '';
			
			if(isset($matched1[1]) && $matched1[1]) {
				
				$matched1[1] = trim($matched1[1]);
				
				if(preg_match('#\s*media=(\'|")([^\'\"]+)\1#i',$matched1[1],$matched2)) {
					
					if(isset($matched2[2]) && $matched2[2]) {
						$resultData['media'] = trim($matched2[2]);
					}
				}
			}
			
			if(isset($matched1[2]) && $matched1[2]) {
				$resultData['code'] = $matched1[2];
			}
			
			unset($matched1);
		}
		
		
		return $resultData;
	}
	
	public function process_each_item($value1, $options)
	{
		$resultData = array(
			'data_processed' => false
		);
		
		$isCombineCSSStatus = false;
		if(isset($options['optimize_css_combine_css_enable']) && ('on' === $options['optimize_css_combine_css_enable'])) {	
			$isCombineCSSStatus = true;
		}
		
		$isMinifyCSSStatus = false;
		if(isset($options['optimize_css_minify_css_enable']) && ('on' === $options['optimize_css_minify_css_enable'])) {
			$isMinifyCSSStatus = true;
		}
		
		$isAsynchronousCSSLoadingStatus = false;
		if(isset($options['optimize_css_asynchronous_css_loading_enable']) && ('on' === $options['optimize_css_asynchronous_css_loading_enable'])) {
			$isAsynchronousCSSLoadingStatus = true;
		}
		
		unset($options);
		
		$optimizeCDN = $this->di->getShared('optimizeCDN');
		
		if(isset($value1['href'])) {
			
			$remote = $this->di->getShared('remote');
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($value1['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $remote->get($value1['href'],array(
					'cache_timeout' => WP_PEPVN_CACHE_TIMEOUT_NORMAL
				));
				
				if(false !== $resultData['data_processed']) {
					
					$cssFixer = new CSSFixer();
					
					$resultData['data_processed'] = $cssFixer->fix(array(
						'css_url' => $value1['href']
						, 'css_content' => $resultData['data_processed']
						, 'minify_status' => $isMinifyCSSStatus
					));
					
					unset($cssFixer);
					
					$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
					
					PepVN_Data::$cacheObject->set_cache($value1['key_processed'],$resultData['data_processed']);
				}
				
			}
			
		} else if(isset($value1['code'])) {
			
			$resultData['data_processed'] = PepVN_Data::$cacheObject->get_cache($value1['key_processed']);
			
			if(null === $resultData['data_processed']) {
				
				$resultData['data_processed'] = $value1['code'];
				
				unset($value1['code']);
				
				$cssFixer = new CSSFixer();
				
				$resultData['data_processed'] = $cssFixer->fix(array(
					'css_url' => PepVN_Data::$defaultParams['urlFullRequest']
					, 'css_content' => $resultData['data_processed']
					, 'minify_status' => $isMinifyCSSStatus
				));
				
				unset($cssFixer);
				
				$resultData['data_processed'] = $optimizeCDN->process($resultData['data_processed']);
				
				PepVN_Data::$cacheObject->set_cache($value1['key_processed'],trim($resultData['data_processed']));
				
			}
			
		}
		
		return $resultData;
	}
	
	private function _process_combine($rsGetAllCSS, $options)
	{
		$classMethodKey = crc32(__CLASS__ . '_' . __METHOD__);
		
		$resultData = array(
			'data_need_replace' => array()
		);
		
		if(!is_dir($this->_folderStorePath)) {
			System::mkdir($this->_folderStorePath);
		}
		
		$isCombineCSSStatus = false;
		if(isset($options['optimize_css_combine_css_enable']) && ('on' === $options['optimize_css_combine_css_enable'])) {	
			$isCombineCSSStatus = true;
		}
		
		$isMinifyCSSStatus = false;
		if(isset($options['optimize_css_minify_css_enable']) && ('on' === $options['optimize_css_minify_css_enable'])) {
			$isMinifyCSSStatus = true;
		}
		
		$isAsynchronousCSSLoadingStatus = false;
		if(isset($options['optimize_css_asynchronous_css_loading_enable']) && ('on' === $options['optimize_css_asynchronous_css_loading_enable'])) {
			$isAsynchronousCSSLoadingStatus = true;
		}
		
		$tmp = array();
		
		foreach($rsGetAllCSS as $key1 => $value1) {
			$tmp[] = $value1['key'];
			unset($key1, $value1);
		}
		
		$fileCombinedStorePath = $this->_folderStorePath . Utils::hashKey(array($classMethodKey,'processed',$tmp)) . '.css';
		
		unset($tmp);
		
		$isProcessSuccessStatus = true;
		
		$rsOne = OptimizeSpeed::checkFileProcess($fileCombinedStorePath);
		
		if($rsOne['need_process']) {
			
			if(false !== file_put_contents($fileCombinedStorePath,'')) {
				
				$fileHandle = fopen($fileCombinedStorePath,'w+');
				
				if($fileHandle) {
					
					foreach($rsGetAllCSS as $key1 => $value1) {
						
						unset($rsGetAllCSS[$key1]);
						
						$rsTwo = $this->process_each_item($value1, $options);
						
						if(
							isset($rsTwo['data_processed'])
							&& (false !== $rsTwo['data_processed'])
							&& (null !== $rsTwo['data_processed'])
						) {
							
							if($value1['media']) {
								$rsTwo['data_processed'] = ' @media '.$value1['media'].' {'.$rsTwo['data_processed'].'}';
							}
							
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
			
			unset($rsGetAllCSS);
			
		} else if($rsOne['file_valid']) {
			
			touch($fileCombinedStorePath);
			
			foreach($rsGetAllCSS as $key1 => $value1) {
				unset($rsGetAllCSS[$key1]);
				$resultData['data_need_replace'][$value1['old']] = '';
				unset($value1);
			}
			
			unset($rsGetAllCSS);
			
		} else {
			$isProcessSuccessStatus = false;
			unset($rsGetAllCSS);
		}
		
		if(true === $isProcessSuccessStatus) {
			$resultData['file_url'] = str_replace($this->_folderStorePath,$this->_folderStoreUrl,$fileCombinedStorePath);
		} else {
			$resultData['data_need_replace'] = array();
		}
		
		return $resultData;
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
		
		$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		$fullDomainNamePregQuote = Utils::preg_quote($fullDomainName); 
		
		$arrayDataTextNeedReplace = array();
		
		$textAppendToBody = '';
		$textAppendToHead = '';
		
		$patternsIgnoredCSS_Uri = $this->patternsIgnoredCSS_Uri;
		
		//cleanPregPatternsArray
		if(isset($options['optimize_css_exclude_url']) && $options['optimize_css_exclude_url']) {
			$tmp = $options['optimize_css_exclude_url'];
			$tmp = PepVN_Data::cleanPregPatternsArray($tmp);
			if(!PepVN_Data::isEmptyArray($tmp)) {
				$patternsIgnoredCSS_Uri = array_merge($patternsIgnoredCSS_Uri, $tmp);
			}
		}
		
		$patternsIgnoredCSS_Uri = implode('|',$patternsIgnoredCSS_Uri);
		$patternsIgnoredCSS_Uri = trim($patternsIgnoredCSS_Uri);
		
		$isCombineCSSStatus = false;
		if(isset($options['optimize_css_combine_css_enable']) && ('on' === $options['optimize_css_combine_css_enable'])) {	
			$isCombineCSSStatus = true;
		}
		
		$isMinifyCSSStatus = false;
		if(isset($options['optimize_css_minify_css_enable']) && ('on' === $options['optimize_css_minify_css_enable'])) {
			$isMinifyCSSStatus = true;
		}
		
		$isAsynchronousCSSLoadingStatus = false;
		if(isset($options['optimize_css_asynchronous_css_loading_enable']) && ('on' === $options['optimize_css_asynchronous_css_loading_enable'])) {
			$isAsynchronousCSSLoadingStatus = true;
		}
		
		$isExcludeInlineCSSStatus = false;
		if(isset($options['optimize_css_exclude_inline_css_enable']) && ('on' === $options['optimize_css_exclude_inline_css_enable'])) {
			$isExcludeInlineCSSStatus = true;
		}
		
		$isExcludeExternalCSSStatus = false;
		if(isset($options['optimize_css_exclude_external_css_enable']) && ('on' === $options['optimize_css_exclude_external_css_enable'])) {
			$isExcludeExternalCSSStatus = true;
		}
		
		$patternsEscaped = array();
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'iframe');
		
		$text = $rsOne['content'];
		if(!empty($rsOne['patterns'])) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		unset($rsOne);
		
		$rsGetAllCss = $this->get_all_css($text);
		
		if(!PepVN_Data::isEmptyArray($rsGetAllCss)) {
			
			$rsGetAllCss1 = array();
			
			foreach($rsGetAllCss as $key1 => $value1) {
				
				unset($rsGetAllCss[$key1]);
				
				$infoCss = $this->get_info_css($value1);
				
				if('link' === $infoCss['type']) {
					
					if(isset($infoCss['href']) && $infoCss['href']) {
						
						$isProcessStatus1 = true;
						
						if(true === $isProcessStatus1) {
							if($patternsIgnoredCSS_Uri) {
								if(preg_match('#('.$patternsIgnoredCSS_Uri.')#i',$value1)) {
									$isProcessStatus1 = false;
								}
							}
						}
						
						if(true === $isProcessStatus1) {
							if($isExcludeExternalCSSStatus) {
								if(!preg_match('#^(https?)?:?(//)?'.$fullDomainNamePregQuote.'#i',$infoCss['href'])) {
									$isProcessStatus1 = false;
								}
							}
						}
						
						if(true === $isProcessStatus1) {
							if(preg_match('#<link[^><]*xtraffic-exclude[^><]*/?>.*(</link>)?#is',$value1)) {
								$isProcessStatus1 = false;
							}
						}
						
						$mediaType = '';
						
						if(isset($infoCss['media']) && $infoCss['media']) {
							$mediaType = $infoCss['media'];
						}
						
						$rsGetAllCss1[$key1] = array(
							'href' => $infoCss['href']
							, 'key' => Utils::hashKey(array($classMethodKey,$infoCss))
							, 'media' => $mediaType
							, 'old' => $value1
						);
						
						if(preg_match('#<link[^><]*xtraffic-keep-original[^><]*/?>.*(</link>)?#is',$value1)) {
							$rsGetAllCss1[$key1]['keepOriginal'] = true;
							$isProcessStatus1 = false;
						}
						
						if(preg_match('#<link[^><]*xtraffic-not-async[^><]*/?>.*(</link>)?#is',$value1)) {
							$rsGetAllCss1[$key1]['notAsync'] = true;
							$isProcessStatus1 = false;
						}
						
						if(false === $isProcessStatus1) {
							$rsGetAllCss1[$key1]['exclude'] = true;
						}
					}
					
				} else if('inline' === $infoCss['type']) {
				
					$isProcessStatus1 = true;
					
					if(true === $isProcessStatus1) {
						if($isExcludeInlineCSSStatus) {
							$isProcessStatus1 = false;
						}
					}
					
					if(true === $isProcessStatus1) {
						if(preg_match('#<(style)[^><]*xtraffic-exclude[^><]*/?>.*?(</style>)?#is',$value1)) {
							$isProcessStatus1 = false;
						}
					}
					
					$mediaType = '';
					
					if(isset($infoCss['media']) && $infoCss['media']) {
						$mediaType = $infoCss['media'];
					}
					
					$rsGetAllCss1[$key1] = array(
						'code' => $infoCss['code']
						, 'key' => Utils::hashKey(array($classMethodKey,$infoCss))
						, 'media' => $mediaType
						, 'old' => $value1
					);
					
					if(preg_match('#<(style)[^><]*xtraffic-keep-original[^><]*/?>.*?(</style>)?#is',$value1)) {
						$rsGetAllCss1[$key1]['keepOriginal'] = true;
						$isProcessStatus1 = false;
					}
					
					if(preg_match('#<(style)[^><]*xtraffic-not-async[^><]*/?>.*?(</style>)?#is',$value1)) {
						$rsGetAllCss1[$key1]['notAsync'] = true;
						$isProcessStatus1 = false;
					}
					
					if(false === $isProcessStatus1) {
						$rsGetAllCss1[$key1]['exclude'] = true;
					}
				}
				
				if(isset($rsGetAllCss1[$key1]['key'])) {
					$rsGetAllCss1[$key1]['key_processed'] = Utils::hashKey(array($classMethodKey,'processed',$rsGetAllCss1[$key1]['key']));
				}
				
				unset($value1);
			}
			
			$rsGetAllCss = $rsGetAllCss1;
			unset($rsGetAllCss1);
			
			$isProcessSuccessStatus = true;
			
			$allCSSNeedCombine = array();
			
			foreach($rsGetAllCss as $key1 => $value1) {
				
				if($isCombineCSSStatus) {	//combine
					
					if(isset($value1['exclude'])) {
						
						if(!empty($allCSSNeedCombine)) {
							
							$rsOne = $this->_process_combine($allCSSNeedCombine, $options);
							
							if(isset($rsOne['file_url']) && $rsOne['file_url']) {
								$arrayDataTextNeedReplace = array_merge($arrayDataTextNeedReplace, $rsOne['data_need_replace']);
								
								if($isAsynchronousCSSLoadingStatus) {
									
									if(isset($value1['notAsync'])) {
										$tmp = false;
									} else {
										
										$tmp = array(
											'load_by' => 'js_data'//js_data,div_tag
											,'type' => 'css'
											,'media' => $value1['media']
										);
										
										if(isset($value1['href'])) {
											$tmp['url'] = $value1['href'];
										} else if(isset($value1['code'])) {
											$tmp['code'] = $value1['code'];
										}
										
									}
									
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag(array(
										'url' => $rsOne['file_url']
										,'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'css'
										,'media' => ''
									)) . ( $tmp ? OptimizeSpeed::parse_load_html_scripts_by_tag($tmp) : $value1['old']);
									
									unset($tmp);
									
								} else {
									$arrayDataTextNeedReplace[$value1['old']] = '<link rel="stylesheet" href="'.$rsOne['file_url'].'" type="text/css" />'.$value1['old'];
								}
								
							} else {
								$isProcessSuccessStatus = false;
								break;
							}
							
							unset($rsOne);
							
						} else {	//allCSSNeedCombine empty
							
							if($isAsynchronousCSSLoadingStatus) {
								
								if(isset($value1['notAsync'])) {
									$tmp = false;
								} else {
									$tmp = array(
										'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'css'
										,'media' => $value1['media']
									);
									
									if(isset($value1['href'])) {
										$tmp['url'] = $value1['href'];
									} else if(isset($value1['code'])) {
										//$tmp['code'] = $value1['code'];
										$tmp = false;
									}
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								}
								
								unset($tmp);
								
							}
							
						}
						
						$allCSSNeedCombine = array();
						
					} else {
						$allCSSNeedCombine[$key1] = $value1;
					}
					
				} else {	//not combine
					
					if($isMinifyCSSStatus) {	//minify
						
						if(isset($value1['exclude'])) {	//exclude (not minify)
							
							if($isAsynchronousCSSLoadingStatus) {//only process when load async
								
								if(isset($value1['notAsync'])) {
									$tmp = false;
								} else {
									$tmp = array(
										'load_by' => 'js_data'//js_data,div_tag
										,'type' => 'css'
										,'media' => $value1['media']
									);
									
									if(isset($value1['href'])) {
										$tmp['url'] = $value1['href'];
									} else if(isset($value1['code'])) {
										//$tmp['code'] = $value1['code'];
										$tmp = false;
									}
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								}
								
								unset($tmp);
								
							}
							
						} else { // not exclude (minify)
							
							$valueProcessedCode = false;
							$fileStoreUrl = false;
							
							if(isset($value1['href'])) {
								
								$fileStorePath = $this->_folderStorePath . $value1['key_processed'].'.css';
								
								$rsOne = OptimizeSpeed::checkFileProcess($fileStorePath);
								
								if($rsOne['need_process']) {
									
									if(false !== file_put_contents($fileStorePath,'')) {
										
										$rsTwo = $this->process_each_item($value1, $options);
										
										if(
											isset($rsTwo['data_processed'])
											&& (false !== $rsTwo['data_processed'])
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
								
								$rsTwo = $this->process_each_item($value1, $options);
								
								if(
									isset($rsTwo['data_processed'])
									&& (false !== $rsTwo['data_processed'])
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
							
							if($isAsynchronousCSSLoadingStatus) {
								
								$tmp = array(
									'load_by' => 'js_data'//js_data,div_tag
									,'type' => 'css'
									,'media' => $value1['media']
								);
								
								if($fileStoreUrl) {
									$tmp['url'] = $fileStoreUrl;
								} else if($valueProcessedCode) {
									//$tmp['code'] = $valueProcessedCode;
									$tmp = false;
									$arrayDataTextNeedReplace[$value1['old']] = $valueProcessedCode;
								}
								
								if($tmp) {
									$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
								}
								
								unset($tmp);
								
							} else {// not async
								if($fileStoreUrl) {
									$arrayDataTextNeedReplace[$value1['old']] = '<link rel="stylesheet" href="'.$fileStoreUrl.'" type="text/css" '.($value1['media'] ? 'media="'.$value1['media'].'" ' : '').'/>';
								} else if($valueProcessedCode) {
									$arrayDataTextNeedReplace[$value1['old']] = '<style>'.$valueProcessedCode.'</style>';
								}
							}
							
							unset($fileStoreUrl,$valueProcessedCode);
						}
						
					} else {	//not minify
						
						if($isAsynchronousCSSLoadingStatus) {	//async
							
							$tmp = array(
								'load_by' => 'js_data'//js_data,div_tag
								,'type' => 'css'
								,'media' => $value1['media']
							);
							
							if(isset($value1['code'])) {
								//$tmp['code'] = $value1['code'];
								$tmp = false;
							} else if(isset($value1['href'])) {
								$tmp['url'] = $value1['href'];
							}
							
							if($tmp) {
								$arrayDataTextNeedReplace[$value1['old']] = OptimizeSpeed::parse_load_html_scripts_by_tag($tmp);
							}
							
							unset($tmp);
							
						}
					}
				}
				
			}
			
			if(!empty($allCSSNeedCombine)) {
				
				$rsOne = $this->_process_combine($allCSSNeedCombine, $options);
				
				if(isset($rsOne['file_url']) && $rsOne['file_url']) {
					
					$arrayDataTextNeedReplace = array_merge($arrayDataTextNeedReplace, $rsOne['data_need_replace']);
					
					if($isAsynchronousCSSLoadingStatus) {
						$textAppendToHead .= OptimizeSpeed::parse_load_html_scripts_by_tag(array(
							'url' => $rsOne['file_url']
							,'load_by' => 'js_data'//js_data,div_tag 
							,'type' => 'css'
						));
					} else {
						$textAppendToHead .= '<link rel="stylesheet" href="'.$rsOne['file_url'].'" type="text/css" />';
					}
					
				} else {
					$isProcessSuccessStatus = false;
				}
				
				unset($rsOne);
			}
			
			unset($allCSSNeedCombine);
			
			if(false === $isProcessSuccessStatus) {//not replace when has even 1 error
				$arrayDataTextNeedReplace = array();
			}
			
		}
		
		if(!empty($arrayDataTextNeedReplace)) {
			$text = str_replace(array_keys($arrayDataTextNeedReplace), array_values($arrayDataTextNeedReplace), $text);
		}
		unset($arrayDataTextNeedReplace);
		
		if($textAppendToHead) {
			$text = PepVN_Data::appendTextToTagHeadOfHtml($textAppendToHead,$text);
		}
		unset($textAppendToHead);
		
		if($textAppendToBody) {
			$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToBody,$text);
		}
		unset($textAppendToBody);
		
		if(!empty($patternsEscaped)) {
			$text = str_replace(array_values($patternsEscaped),array_keys($patternsEscaped),$text); 
		}
		unset($patternsEscaped);
		
		PepVN_Data::$cacheObject->set_cache($keyCache, $text);
		
		return $text;
	}
}

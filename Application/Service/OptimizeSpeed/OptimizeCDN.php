<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\Hash
	,WpPepVN\DependencyInjection
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	,WPOptimizeByxTraffic\Application\Service\PepVN_Data
;

class OptimizeCDN
{
	public $di = false;
	
	private static $_tempData = array();
	
	private static $_patternFilesTypeAllow = false;
	
	protected $_cdnDomains = array(
		'total' => 0
		,'data' => array()
	);
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$options = OptimizeSpeed::getOption();
		
		$tmp = explode(',',$options['cdn_domain']);
		
		if(!empty($tmp)) {
			$this->_cdnDomains['data'] = array_values($tmp);
			$this->_cdnDomains['data'][] = $this->_cdnDomains['data'][0];
			
			$this->_cdnDomains['total'] = count($this->_cdnDomains['data']);
			$this->_cdnDomains['total'] = (int)$this->_cdnDomains['total'];
		}
		
		//add_filter( 'wp_get_attachment_url', array($this,'wp_add_filter_wp_get_attachment_url'), WP_PEPVN_PRIORITY_LAST, 2 );
	}
	
	public function wp_add_filter_wp_get_attachment_url($url, $posID)
	{
		$url = $this->get_cdn_link($url);
		
		return $url;
	}
	
	public function getPatternFilesTypeAllow()
	{
		if(false === self::$_patternFilesTypeAllow) {
			//https://support.cloudflare.com/hc/en-us/articles/200172516-Which-file-extensions-does-CloudFlare-cache-for-static-content-
			self::$_patternFilesTypeAllow = array(
				//images
				'jpg'
				,'jpeg'
				,'gif'
				,'png'
				,'ico'
				,'svg'
				,'bmp'
				,'pict'
				,'tif'
				,'tiff'
				,'webp'
				
				//design (vector)
				,'eps'
				,'svg'
				,'svgz'
				
				//js & css
				,'css'
				,'js'
				,'ejs'		//http://www.embeddedjs.com/
				
				//font
				,'ttf'
				,'woff2'
				,'woff'
				,'eot'
				,'otf'
				
				//audio
				,'wav'
				,'ogg'
				,'mp3'
				,'wma'
				,'mid'
				,'midi'
				,'rm'
				,'ram'
				,'aac'
				,'m4a'
				,'pls'	//http://fileinfo.com/extension/pls
				,'midi'
				,'mid'
				
				
				//video
				,'mpg'
				,'mpeg'
				,'avi'
				,'wmv'
				,'mov'
				,'rm'
				,'ram'
				,'ogg'
				,'webm'
				,'mp4'
				,'m4v'
				,'ogv'
				,'flv'
				
				//flash
				,'swf'
				,'flv'
				
				//document
				,'pdf'
				,'csv'
				,'doc'
				,'ppt'
				,'docx'
				,'xlsx'
				,'xls'
				,'pptx'
				,'ps'
				
				//others
				,'class'
				,'jar'
				
				
			);
			
			self::$_patternFilesTypeAllow = array_unique(self::$_patternFilesTypeAllow);
			self::$_patternFilesTypeAllow = implode('|',self::$_patternFilesTypeAllow);
		}
		
		return self::$_patternFilesTypeAllow;
	}
	
	public function is_cdn_enable() 
	{
		$k = 'is_cdn_enable';
		
		if(!isset(self::$_tempData[$k])) {
			
			$status = false;
			
			$options = OptimizeSpeed::getOption();
			
			if(isset($options['cdn_enable']) && ('on' === $options['cdn_enable'])) {
				if(isset($options['cdn_domain']) && ($options['cdn_domain'])) {
					$options['cdn_domain'] = trim($options['cdn_domain']);
					if($options['cdn_domain']) {
						$status = true;
					}
				}
			}
			
			self::$_tempData[$k] = $status;
		}
		
		return self::$_tempData[$k];
	}
	
	private function _get_cdn_domain($input_link) 
	{
		$input_link_crc32 = crc32($input_link);
		$input_link_crc32 = (int)$input_link_crc32;
		$input_link_crc32 = abs($input_link_crc32);
		
		$k = Hash::crc32b('gtcndm_'.$input_link_crc32);
		
		if(!isset(self::$_tempData[$k])) {
			
			$cdn_domain = '';
			
			if($this->_cdnDomains['total']>0) {
				
				if($this->_cdnDomains['total']>1) {
					
					$index = $input_link_crc32;
					
					$totalIndex = $this->_cdnDomains['total'] - 1;
					
					$numberDivision = $this->_cdnDomains['total'];
					
					while($index > $totalIndex) {
						
						$index = $index / $numberDivision;
						
						$numberDivision++;
					}
					
					$index = (int)$index;
					
					$cdn_domain = $this->_cdnDomains['data'][$index];
					
				} else {
					$cdn_domain = $this->_cdnDomains['data'][0];
				}
			}
			
			$cdn_domain = trim($cdn_domain);
			
			self::$_tempData[$k] = $cdn_domain;
		}
		
		return self::$_tempData[$k];
	}
	
	public function is_link_allow($input_link) 
	{
		$k = Hash::crc32b('is_link_allow_'.$input_link);
		
		if(!isset(self::$_tempData[$k])) {
			
			$status = true;
			
			$options = OptimizeSpeed::getOption();
			
			if(isset($options['cdn_exclude_url']) && ($options['cdn_exclude_url'])) {
				$tmp = OptimizeSpeed::parsePattern($options['cdn_exclude_url']);
				if($tmp) {
					if(preg_match('#('.$tmp.')#i',$input_link)) {
						$status = false;
					}
				}
			}
			
			if(true === $status) {
				$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
				if(!preg_match('#^(https?:)?(//)?'.Utils::preg_quote($fullDomainName).'[^\'\"\(\)]+\.('.$this->getPatternFilesTypeAllow().')\??#i',$input_link)) {
					$status = false;
				}
				
			}
			
			self::$_tempData[$k] = $status;
		}
		
		return self::$_tempData[$k];
	}
    
	public function get_cdn_link($input_link, $scheme = '', $strictStatus = true) 
	{
		$currentProtocol = '';
		
		if($scheme) {
			if(0 === strpos($scheme, 'https')) {
				$currentProtocol = 'https://';
			} else if(0 === strpos($scheme,'http')) {
				$currentProtocol = 'http://';
			}
		}
		
		if(!$currentProtocol) {
			$currentProtocol = 'http://';
			if(Utils::is_ssl()) {
				$currentProtocol = 'https://';
			}
		}
		
		$input_link = PepVN_Data::removeProtocolUrl($input_link);
		
		if(!$this->is_cdn_enable()) {
			return $currentProtocol.$input_link;
		}
		
		if($strictStatus) {
			if(!$this->is_link_allow($input_link)) {
				return $currentProtocol.$input_link;
			}
		}
		
		$cdn_domain = $this->_get_cdn_domain($input_link);
		
		$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		
		return $currentProtocol.preg_replace('#^'.PepVN_Data::preg_quote($fullDomainName).'#i',$cdn_domain,$input_link,1);
		
	}
	
	
	/*
	*	input_type (string) : html | css | js
	*/
	public function process($text) 
	{
		if(!$this->is_cdn_enable()) {
			return $text;
		}
		
		$keyCacheProcessMain = array(
			__METHOD__
			,$text
			,'process_main'
		);
		
		$keyCacheProcessMain = Utils::hashKey($keyCacheProcessMain);
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCacheProcessMain); 
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		
		$allTargetElements = array();
		$arrayDataTextNeedReplace = array();
		
		preg_match_all('#(\'|\"|\(|\))?((https?:)?//'.Utils::preg_quote($fullDomainName).'[^\'\"\(\)]+\.('.$this->getPatternFilesTypeAllow().')\??[^\'\"\(\)]*?)(\1)?#is',$text,$matched1);
		
		if(isset($matched1[2]) && $matched1[2] && (!PepVN_Data::isEmptyArray($matched1[2]))) {
			$allTargetElements = array_merge($allTargetElements, $matched1[2]);
		}
		
		unset($matched1);
		
		$allTargetElements = array_unique($allTargetElements);
		
		if(!empty($allTargetElements)) {
			
			foreach($allTargetElements as $key1 => $value1) {
				
				unset($allTargetElements[$key1]);
				
				if($value1) {
					$tmp = $value1;
					$tmp2 = $this->get_cdn_link($tmp);
					$tmp = PepVN_Data::removeProtocolUrl($tmp);
					$tmp2 = PepVN_Data::removeProtocolUrl($tmp2);
					
					if($tmp !== $tmp2) {
						$tmp = '//'.$tmp;
						$tmp2 = '//'.$tmp2;
						$arrayDataTextNeedReplace[$tmp] = $tmp2;
						
					}
					unset($tmp,$tmp2);
				}
				
				unset($value1);
			}
		}
		
		if(!empty($arrayDataTextNeedReplace)) {
			$text = str_replace(array_keys($arrayDataTextNeedReplace),array_values($arrayDataTextNeedReplace),$text);
		}
		unset($arrayDataTextNeedReplace);
		
		$text = trim($text); 
		
		PepVN_Data::$cacheObject->set_cache($keyCacheProcessMain,$text);
		
		return $text;
		
	}
	
}
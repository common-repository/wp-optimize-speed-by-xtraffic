<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed;

use WpPepVN\Utils
	,WpPepVN\DependencyInjection
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeByxTraffic\Application\Service\StaticVar as ServiceStaticVar
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
;

class PreResolveDns
{
	public $di = false;
	
	private $_tempData = array();
	
	private $_staticVarObject = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		$tmp = array(
			'domains' => array()
		);
		$this->_staticVarObject = new ServiceStaticVar(md5('WPOptimizeSpeedByxTraffic/Application/Service/OptimizeSpeed/PreResolveDns/construct'), $tmp);
	}
    
	public function statisticsDomains($text,$type)
	{
		
		$staticVarData = $this->_staticVarObject->get();
		
		$matched1 = array();
		
		if('html' === $type) {
			preg_match_all('#<(link|script|img|iframe|style)[^>]+>#is',$text,$matched1);
			$matched1 = $matched1[0];
		} elseif('css' === $type) {
			$matched1[] = $text;
		}
		
		foreach($matched1 as $key1 => $value1) {
			unset($matched1[$key1]);
			preg_match('#(background:url\(|\"|\')((https?:)?//[^\)\'\"\s \t]+)#is',$value1,$matched2);
			if(isset($matched2[2]) && $matched2[2]) {
				$matched2 = trim(PepVN_Data::removeProtocolUrl($matched2[2]));
				if($matched2) {
					$matched2 = Utils::parse_url('http://'.$matched2);
					if(isset($matched2['host']) && $matched2['host']) {
						$host = $matched2['host'];
						if(PepVN_Data::$defaultParams['fullDomainName'] !== $host) {
							if(!isset($staticVarData['domains'][$host])) {
								$staticVarData['domains'][$host] = 0;
							}
							$staticVarData['domains'][$host]++;
						}
						
					}
				}
			}
			unset($key1,$value1);
		}
		
		$tmp = $staticVarData['domains'];
		
		arsort($tmp);
		
		$tmp = array_slice($tmp, 0, 1000, true);
		
		$staticVarData['domains'] = $tmp;
		
		unset($tmp);
		
		$this->_staticVarObject->save($staticVarData,'m');
		
	}
	
	
	public function appendDNSPrefetch($text)
	{
		$textAppendToHead = '';
		
		$limitItems = 20;
		
		$staticVarData = $this->_staticVarObject->get();
		
		$staticVarData = $staticVarData['domains'];
		
		arsort($staticVarData);
		
		$staticVarData = array_slice($staticVarData, 0, $limitItems, true);
		
		foreach($staticVarData as $key1 => $value1) {
			unset($staticVarData[$key1]);
			if(PepVN_Data::$defaultParams['fullDomainName'] !== $key1) {
				$textAppendToHead .= '<link rel="dns-prefetch" href="//'.$key1.'" />';
			}
			unset($key1,$value1);
		}
		
		$text = PepVN_Data::appendTextToTagHeadOfHtml($textAppendToHead,$text);
		
		return $text;
	}
}
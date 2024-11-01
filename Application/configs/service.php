<?php 

use WPOptimizeSpeedByxTraffic\Application\Service\CacheRequestUri
	, WPOptimizeSpeedByxTraffic\Application\Service\WpRegisterStyleScript
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCDN
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeCSS
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\OptimizeGooglePageSpeed
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\PreResolveDns
	, WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed\CloudFlare
;

$tmp = array(
	'wpExtend'
	,'remote'
	,'cache'
	,'device'
	,'cacheManager'
	,'session'
	,'queue'
);

$tmp = array_unique($tmp);
foreach($tmp as $value) {
	if($value) {
		$di->set($value, $wpOptimizeByxTraffic->di->getShared($value), true);
	}
}
unset($tmp);

$optimizeSpeed = new OptimizeSpeed($di);
$di->set('optimizeSpeed', $optimizeSpeed, true);

$optimizeCDN = new OptimizeCDN($di);
$di->set('optimizeCDN', $optimizeCDN, true);

$optimizeCSS = new OptimizeCSS($di);
$di->set('optimizeCSS', $optimizeCSS, true);

$cacheRequestUri = new CacheRequestUri($di);
$di->set('cacheRequestUri', $cacheRequestUri, true);

$optimizeGooglePageSpeed = new OptimizeGooglePageSpeed($di);
$di->set('optimizeGooglePageSpeed', $optimizeGooglePageSpeed, true);

$preResolveDns = new PreResolveDns($di);
$di->set('preResolveDns', $preResolveDns, true);

$cloudFlare = new CloudFlare($di);
$di->set('cloudFlare', $cloudFlare, true);

$wpRegisterStyleScript = new WpRegisterStyleScript();
$di->set('wpRegisterStyleScript', $wpRegisterStyleScript, true);

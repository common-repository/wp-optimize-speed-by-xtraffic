<?php 

$tmp = array(
	'adminNotice'
);
$tmp = array_unique($tmp);
foreach($tmp as $value) {
	if($value) {
		$di->set($value, $wpOptimizeByxTraffic->di->getShared($value), true);
	}
}
unset($tmp);

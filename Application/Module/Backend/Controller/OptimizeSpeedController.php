<?php
namespace WPOptimizeSpeedByxTraffic\Application\Module\Backend\Controller;

use WPOptimizeSpeedByxTraffic\Application\Module\Backend\Form\OptimizeSpeedOptionsForm
	,WPOptimizeSpeedByxTraffic\Application\Service\OptimizeSpeed
	,WPOptimizeByxTraffic\Application\Service\PepVN_Data
	,WpPepVN\System
	,WpPepVN\Utils
	,WpPepVN\DependencyInjection
;

class OptimizeSpeedController extends ControllerBase
{
    
	public function __construct() 
    {
		parent::__construct();
	}
	
	public function init(DependencyInjection $di)
	{
		parent::init($di);
		
	}
	
	public function indexAction() 
    {
		global $wpOptimizeSpeedByxTraffic;
		//$this->view->wp_nonce = wp_create_nonce(self::PLUGIN_SLUG);
		
		$optimizeSpeed = $wpOptimizeSpeedByxTraffic->di->getShared('optimizeSpeed');
		
		$adminNotice = $this->di->getShared('adminNotice');
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		$translate = $this->view->translate;
		
		$adminNotice->add_notice(
			'<p>'.$translate->_('Although we have tried and optimal features "Optimize Javascript" & "Optimize CSS" operate effectively on many websites, they make your website load faster and have higher scores on the measure tools.').'</p>'
			. '<p>'.$translate->_('But there are some exceptions make website\'s layout is broken or not running properly like before. If your website is in the unfortunate case, you just simply turn off only 2 features "Optimize Javascript" & "Optimize CSS" and experience other features, because they operate independently of each other.').'</p>'
		,'info');
		
		$options = OptimizeSpeed::getOption();
		$bindPostData = $options;
		if(true === $this->request->isPost()) {
			$bindPostData = $this->request->getAllPostData();
		}
		
		$this->view->form = new OptimizeSpeedOptionsForm((object)$bindPostData, array(
            'fields' => OptimizeSpeed::getDefaultOption()
        ));
		$this->view->form->setDI($this->di);
		
		$webServerSoftwareName = System::getWebServerSoftwareName();
		
    	// Check if request has made with POST
        if(true === $this->request->isPost()) {
			
            // Access POST data
            $submitButton = $this->request->getPost('submitButton');
			
            if($submitButton) {
				
				$formElementsName = array_keys($this->view->form->formElements);
				$formElementsName = array_unique($formElementsName);
				
				$this->view->form->bind($this->request->getAllPostData(),null,$formElementsName);
				
				if (!$this->view->form->isValid()) {
					$messages = $this->view->form->getMessages();
					foreach ($messages as $message) {
						$this->view->adminNotice->add_notice((string)$message, 'error');
					}
					unset($messages);
				} else {
					
					$arrFieldsNumber = array(
						'optimize_cache_prebuild_cache_number_pages_each_process' => '1'
						,'optimize_cache_cachetimeout' => '21600'
					);
					
					$optionsData = array();
					foreach($formElementsName as $name) {
						$optionsData[$name] = $this->view->form->getValueFiltered($name);
						if(isset($arrFieldsNumber[$name])) {
							$optionsData[$name] = abs((int)$optionsData[$name]);
						}
					}
					
					if($optionsData['optimize_cache_cachetimeout'] < 300) {
						$optionsData['optimize_cache_cachetimeout'] = 300;
					}
					
					$allPostData = $this->request->getAllPostData();
					if(isset($allPostData['optimize_cache_database_cache_methods']) && $allPostData['optimize_cache_database_cache_methods']) {
						$allPostData['optimize_cache_database_cache_methods'] = (array)$allPostData['optimize_cache_database_cache_methods'];
						$optionsData['optimize_cache_database_cache_methods'] = $allPostData['optimize_cache_database_cache_methods'];
					}
					
					if(isset($allPostData['optimize_cache_object_cache_methods']) && $allPostData['optimize_cache_object_cache_methods']) {
						$allPostData['optimize_cache_object_cache_methods'] = (array)$allPostData['optimize_cache_object_cache_methods'];
						$optionsData['optimize_cache_object_cache_methods'] = $allPostData['optimize_cache_object_cache_methods'];
					}
					
					$tmp = (array)$optionsData['cdn_domain'];
					$tmp = implode(';',$tmp);
					$tmp = preg_replace('#[\,\;]+#',';',$tmp);
					$tmp = explode(';',$tmp);
					$tmp = PepVN_Data::cleanArray($tmp);
					
					$tmp1 = array();
					if(!empty($tmp)) {
						foreach($tmp as $key1 => $value1) {
							unset($tmp[$key1]);
							$value1 = 'http://'.PepVN_Data::removeProtocolUrl($value1);
							$value1 = Utils::parse_url($value1);
							if(isset($value1['host']) && $value1['host']) {
								$tmp1[] = trim($value1['host']);
							}
						}
					}
					
					$optionsData['cdn_domain'] = implode(',',$tmp1);
					$optionsData['cdn_domain'] = trim($optionsData['cdn_domain']);
					
					OptimizeSpeed::updateOption($optionsData);
					
					$isNeedSetServerConfigs = false;
					
					$arrayFieldsChangeNeedSetServerConfigs = array(
						'optimize_cache_enable'
						,'optimize_cache_browser_cache_enable'
						,'optimize_cache_mobile_device_cache_enable'
						,'optimize_cache_url_get_query_cache_enable'
						,'optimize_cache_exclude_url'
						,'optimize_cache_exclude_cookie'
					);
					
					foreach($arrayFieldsChangeNeedSetServerConfigs as $name) {
						
						if(isset($options[$name]) && !isset($optionsData[$name])) {
							$isNeedSetServerConfigs = true;
						} else if(!isset($options[$name]) && isset($optionsData[$name])) {
							$isNeedSetServerConfigs = true;
						} else if(isset($options[$name]) && isset($optionsData[$name])) {
							if($options[$name] !== $optionsData[$name]) {
								$isNeedSetServerConfigs = true;
							}
						}
						
						if($isNeedSetServerConfigs) {
							
							$optimizeSpeed->set_server_configs();
							
							if('nginx' === $webServerSoftwareName) {
								$adminNotice->add_notice('<p>'.$translate->_('You need to restart the service nginx command "sudo service nginx restart" for the changes to take effect'),'info');
							}
							
							break;
						}
					}
					
					$this->_addNoticeSavedSuccess();
					
					$this->_doAfterUpdateOptions();
					
					$optimizeSpeed->set_advanced_cache_configs();
					
				}
			}
		}
		
		$this->view->webServerSoftwareName = $webServerSoftwareName;
		$this->view->fullDomain = $this->request->getFullDomain();
		$this->view->getABSPATH =  $wpExtend->getABSPATH();
		
		$this->view->isHasAPCStatus =  System::hasAPC();
		
		$this->view->isHasMemcacheStatus =  false;
		if(System::hasMemcached() || System::hasMemcache()) {
			$this->view->isHasMemcacheStatus =  true;
		}
		
		$this->view->bindPostData = $bindPostData;
		
		$this->view->optimizeSpeed = $optimizeSpeed;
		
	}
	
}
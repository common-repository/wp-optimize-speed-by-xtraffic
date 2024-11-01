<?php 
namespace WPOptimizeSpeedByxTraffic\Application\Module\Backend\Form;

use WpPepVN\Validation
    ,WpPepVN\Validation\Validator\Email as ValidatorEmail
    ,WpPepVN\Validation\Validator\PresenceOf as ValidatorPresenceOf
    ,WpPepVN\Validation\Validator\StringLength as ValidatorStringLength
    ,WpPepVN\Validation\Validator\Regex as ValidatorRegex
    
    ,WpPepVN\Form\Element\Text as FormElementText
	,WpPepVN\Form\Element\Check as FormElementCheck
	,WpPepVN\Form\Element\TextArea as FormElementTextArea
    ,WpPepVN\Form\Element\Select as FormElementSelect
    ,WpPepVN\Form\Element\Password as  FormElementPassword
	
	,WpPepVN\Form\Form as FormForm
;


class OptimizeSpeedOptionsForm extends FormForm
{

	/**
     * Forms initializer
     *
     * @param Property $property
     */
	 
	public $formElements = array();
	
    public function initialize($optionEntity = null, $options)
    {

		
		//Set the same form as entity
        if(!isset($options['fields'])) {
            $options['fields'] = array();
        }
        
		$arrayFieldsNames = array();
		if(!empty($options['fields'])) {
			$arrayFieldsNames = array_keys($options['fields']);
			$arrayFieldsNames = array_flip($arrayFieldsNames);
		}
		
		//Create check elements
		$arrFields = array(
		
			//Optimize Cache
			'optimize_cache_enable' => ''
			,'optimize_cache_front_page_cache_enable' => ''
			,'optimize_cache_feed_page_cache_enable' => ''
			,'optimize_cache_browser_cache_enable' => ''
			,'optimize_cache_database_cache_enable' => ''
			,'optimize_cache_object_cache_enable' => ''
			,'optimize_cache_ssl_request_cache_enable' => ''
			,'optimize_cache_mobile_device_cache_enable' => ''
			,'optimize_cache_url_get_query_cache_enable' => ''
			,'optimize_cache_logged_users_cache_enable' => ''
			,'optimize_cache_prebuild_cache_enable' => ''
			
			//Optimize Javascript
			,'optimize_javascript_enable' => ''
			,'optimize_javascript_combine_javascript_enable' => ''
			,'optimize_javascript_minify_javascript_enable' => ''
			,'optimize_javascript_move_bottom_enable' => ''
			,'optimize_javascript_asynchronous_javascript_loading_enable' => ''
			,'optimize_javascript_exclude_external_javascript_enable' => ''
			,'optimize_javascript_exclude_inline_javascript_enable' => ''
			
			
			//Optimize CSS (Style)
			,'optimize_css_enable' => ''
			,'optimize_css_combine_css_enable' => ''
			,'optimize_css_minify_css_enable' => ''
			,'optimize_css_asynchronous_css_loading_enable' => ''
			,'optimize_css_exclude_external_css_enable' => ''
			,'optimize_css_exclude_inline_css_enable' => ''
			
			//Optimize HTML
			,'optimize_html_enable' => ''
			,'optimize_html_minify_html_enable' => ''
			
			,'cdn_enable' => ''
			
			,'learn_improve_google_pagespeed_enable' => ''
			,'image_lazyload_enable' => ''
		);
		
		foreach($arrFields as $key => $value) {
			unset($arrFields[$key]);
			//begin input
			$nameElement = $key;
			if(empty($options['fields']) || isset($arrayFieldsNames)) {
			
				$this->formElements[$nameElement] = $nameElement;
				$titleElement = '';
				$inputElement = new FormElementCheck($nameElement);

				$inputElement->setAttribute('value','on');
				
				if('optimize_cache_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_cache_enable_container');
				} else if('optimize_cache_database_cache_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_cache_database_cache_enable_container');
				} else if('optimize_cache_object_cache_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_cache_object_cache_enable_container');
				} else if('optimize_cache_prebuild_cache_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_cache_prebuild_cache_enable_container');
				} else if('optimize_javascript_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_javascript_enable_container');
				} else if('optimize_css_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_css_enable_container');
				} else if('optimize_html_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#optimize_html_enable_container');
				} else if('cdn_enable' === $nameElement) {
					$inputElement->setAttribute('class','wppepvn_toggle_show_hide_trigger');
					$inputElement->setAttribute('data-target','#cdn_enable_container');
				}
				
				$arrayFilters = array('striptags','trim','string');
				foreach($arrayFilters as $filerName) {
					$inputElement->addFilter($filerName);
				}
				
				$this->add($inputElement);
			}
			//end input
		}
		
		
		
		
		//Create text (number) elements
		$arrFields = array(
			'optimize_cache_prebuild_cache_number_pages_each_process' => '1'
			,'optimize_cache_cachetimeout' => '21600'
		);
		
		foreach($arrFields as $key => $value) {
			unset($arrFields[$key]);
			//begin input
			$nameElement = $key;
			if(empty($options['fields']) || isset($options['fields'][$nameElement])) {
			
				$this->formElements[$nameElement] = $nameElement;
				$titleElement = '';
				$inputElement = new FormElementText($nameElement);
				
				$arrayFilters = array('striptags','trim','int');
				foreach($arrayFilters as $filerName) {
					$inputElement->addFilter($filerName);
				}
				$inputElement->setAttribute('style','width:80px;');
				
				if(isset($options['fields'][$nameElement]) && (false !== $options['fields'][$nameElement])) {
					$inputElement->setDefault($options['fields'][$nameElement]);
				} else {
					$inputElement->setDefault($value);
				}
				
				$this->add($inputElement);
			}
			//end input
		}
        
		
		
		//Create text (string) elements
		$arrFields = array(
			'optimize_cache_exclude_url' => ''
			,'optimize_cache_exclude_cookie' => ''
			,'optimize_javascript_exclude_url' => ''
			,'optimize_css_exclude_url' => ''
			,'cdn_domain' => ''
			,'cdn_exclude_url' => ''
			,'cdn_cloudflare_email' => ''
			,'cdn_cloudflare_api_key' => ''
		);
		
		foreach($arrFields as $key => $value) {
			unset($arrFields[$key]);
			//begin input
			$nameElement = $key;
			if(empty($options['fields']) || isset($options['fields'][$nameElement])) {
			
				$this->formElements[$nameElement] = $nameElement;
				$titleElement = '';
				$inputElement = new FormElementText($nameElement);
				
				$arrayFilters = array('striptags','trim','string');
				foreach($arrayFilters as $filerName) {
					$inputElement->addFilter($filerName);
				}
				
				$inputElement->setAttribute('style','width:100%;');
				
				$this->add($inputElement);
			}
			//end input
		}
        
		
		
		//Create textarea (string) elements
		$arrFields = array(
			'memcache_servers' => ''
		);
		
		foreach($arrFields as $key => $value) {
			unset($arrFields[$key]);
			//begin input
			$nameElement = $key;
			if(empty($options['fields']) || isset($options['fields'][$nameElement])) {
			
				$this->formElements[$nameElement] = $nameElement;
				$titleElement = '';
				$inputElement = new FormElementTextArea($nameElement);
				
				$arrayFilters = array('striptags','trim','string');
				foreach($arrayFilters as $filerName) {
					$inputElement->addFilter($filerName);
				}
				
				$inputElement->setAttribute('style','width: 100%; min-height: 100px; height: 100px;');
				$inputElement->setAttribute('class','wppepvn_expand_on_focus');
				
				$this->add($inputElement);
			}
			//end input
		}
        
		
    }
	
	
}
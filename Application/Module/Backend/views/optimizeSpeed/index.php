
<?php if('nginx' === $webServerSoftwareName) : ?>

<div class="update-nag row">
	<p><b><?php echo WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAME; ?></b> : To achieve the highest performance with the plugin on your Nginx server, you should follow the instructions below (if you have not already done) :  <button class="button-primary wppepvn_toggle_show_hide_trigger" data-target="#optimize_nginx_config_server_guide_container">Show me</button></p>
	
	<div id="optimize_nginx_config_server_guide_container" class="wppepvn_toggle_show_hide_container" style="">
		<ul>
			<li>
				<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>Step 1</u></b> : Find and remove <i style="color: red;font-weight: 900;">red block below</i> (if exists) in your config "<i>server {...}</i>" block (at file .conf) :</h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;">server {
	listen   80; 
	## Your website name goes here.
	server_name <?php echo $fullDomain; ?>;
	root <?php echo $getABSPATH ?>;
	index index.php;
	...
	<b style="color: red;font-weight: 900;"><i>location / {
		...
	}</i></b>
	...
}</pre>
			</li>
			
			<li>
				<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>Step 2</u></b> : Add <i style="color: blue;font-weight: 900;">blue line below</i> into your config "<i>server {...}</i>" block (at file .conf) :</h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;">server {
	listen   80; 
	## Your website name goes here.
	server_name <?php echo $fullDomain; ?>;
	root <?php echo $getABSPATH ?>;
	index index.php;
	...
	<b style="color: blue;font-weight: 900;"><i>include <?php echo $getABSPATH ?>xtraffic-nginx.conf;</i></b>
	...
}</pre>
			</li>
			
			<li>
				<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>Step 3</u></b> : Restart your Nginx through SSH command : </h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;"># sudo service nginx restart</pre>
			</li>
		</ul>
	</div>
</div>
	
<?php endif; ?>

<?php if('apache' === $webServerSoftwareName) : ?>

<?php 
$serverConfigsData = $optimizeSpeed->set_server_configs(array(
	'return_server_configs' => true
));

$apacheNotices = array();
$iNumber1 = 0;
foreach($serverConfigsData as $key1 => $value1) {
	if(isset($value1['FILE_PATH']) && $value1['FILE_PATH']) {
		
		if(is_file($value1['FILE_PATH']) && is_writable($value1['FILE_PATH'])) {
			
		} else if(isset($value1['htaccess']) && $value1['htaccess']) {
			$iNumber1++;
			$apacheNotices[] = '
<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>Step '.$iNumber1.'</u></b> : Add content below into your config file "<i style="color:blue;">'.$value1['FILE_PATH'].'</i>" : </h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%; max-height: 300px;overflow: auto;margin-bottom: 3%;">'.htmlentities(trim($value1['htaccess']), ENT_QUOTES | ENT_XHTML, 'UTF-8').'</pre>
			';
		}
	}
	
}

unset($serverConfigsData);

?>

<?php if(!empty($apacheNotices)) : ?>
<div class="update-nag row" style="max-width: 100%;">
	<p><b><?php echo WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAME; ?></b> : To achieve the highest performance with the plugin on your Apache server, you should follow the instructions below (if you have not already done) :  <button class="button-primary wppepvn_toggle_show_hide_trigger" data-target="#optimize_apache_config_server_guide_container">Show me</button></p>
	
	<div id="optimize_apache_config_server_guide_container" class="wppepvn_toggle_show_hide_container" style="">
		<ul>
<?php 
foreach($apacheNotices as $key1 => $value1) {
	echo '<li>',$value1,'</li>';
}
unset($apacheNotices);
?>
			
		</ul>
	</div>
</div>
<?php endif; ?>

	
<?php endif; ?>

<div class="row" style="margin-bottom:2%;">
	<h2 style="margin:0;"><?php echo WP_OPTIMIZE_SPEED_BY_XTRAFFIC_PLUGIN_NAME; ?></h2>
</div>


<div class="row">

	<form class="" id="optimize_speed_settings_form" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>" >
	
		<div>

		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#optimize-cache" aria-controls="optimize-cache" role="tab" data-toggle="tab">Optimize Cache</a>
			</li>
			<li role="presentation">
				<a href="#optimize-javascript" aria-controls="optimize-javascript" role="tab" data-toggle="tab">Optimize Javascript</a>
			</li>
			<li role="presentation">
				<a href="#optimize-css" aria-controls="optimize-css" role="tab" data-toggle="tab">Optimize CSS</a>
			</li>
			<li role="presentation">
				<a href="#optimize-html" aria-controls="optimize-html" role="tab" data-toggle="tab">Optimize HTML</a>
			</li>
			
			<li role="presentation">
				<a href="#optimize-cdn" aria-controls="cdn" role="tab" data-toggle="tab">CDN</a>
			</li>
			
			<li role="presentation" style="display:none;">
				<a href="#optimize-others" aria-controls="others" role="tab" data-toggle="tab">Others</a>
			</li>
			
			<li role="presentation" style="display:none;" id="memcache-servers-button">
				<a href="#memcache-servers" aria-controls="cdn" role="tab" data-toggle="tab">Memcache</a>
			</li>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content">
			
			<div role="tabpanel" class="tab-pane active" id="optimize-cache">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Optimize Cache</h3>
					
					<div>
						
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('optimize_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Optimize Cache ( Recommended )'); ?>
									</h4>
								</label>
							</div>
						</div>
						
						<div id="optimize_cache_enable_container" class="wppepvn_toggle_show_hide_container">
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_front_page_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache Front Page ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Front Page include : Home page, category page, tag page, author page, date page, archives page,...'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_feed_page_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache Feed (RSS/Atom) Page ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_browser_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Browser Cache( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_database_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Database Cache ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This feature helps to increase website speed and reduce the query to the database.'); ?></i></p>
								
								<div id="optimize_cache_database_cache_enable_container" class="wppepvn_toggle_show_hide_container">
									
									<?php if($isHasAPCStatus) : ?>
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" name="optimize_cache_database_cache_methods[apc]" value="apc" <?php 
														echo (isset($bindPostData['optimize_cache_database_cache_methods']['apc']) ? ' checked ' : '');
													?> />&nbsp;<?php $translate->e('Use APC'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i></i></p>
									</div>
									<?php endif; ?>
									
									<?php if($isHasMemcacheStatus) : ?>
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" name="optimize_cache_database_cache_methods[memcache]" value="memcache" <?php 
														echo (isset($bindPostData['optimize_cache_database_cache_methods']['memcache']) ? ' checked ' : '');
													?> class="wppepvn_toggle_show_hide_trigger" data-target="#memcache-servers-button" />&nbsp;<?php $translate->e('Use Memcache'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i></i></p>
									</div>
									<?php endif; ?>
									
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" disabled checked />&nbsp;<?php $translate->e('Use File (Default)'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i><?php $translate->e('Plugin use Multi-Cache with the priority order of speed "APC > Memcache > File", help your website fast and stable operation of the most.'); ?></i></p>
									</div>
								</div>
								
							</div>
							
							
							<div style="margin-left:5%; margin-bottom: 20px;<?php //echo (WP_PEPVN_DEBUG ? '' : 'display:none;'); ?>">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_object_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Object Cache ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This feature helps to increase website speed.'); ?></i></p>
								
								<div id="optimize_cache_object_cache_enable_container" class="wppepvn_toggle_show_hide_container">
									
									<?php if($isHasAPCStatus) : ?>
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" name="optimize_cache_object_cache_methods[apc]" value="apc" <?php 
														echo (isset($bindPostData['optimize_cache_object_cache_methods']['apc']) ? ' checked ' : '');
													?> />&nbsp;<?php $translate->e('Use APC'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i></i></p>
									</div>
									<?php endif; ?>
									
									<?php if($isHasMemcacheStatus) : ?>
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" name="optimize_cache_object_cache_methods[memcache]" value="memcache" <?php 
														echo (isset($bindPostData['optimize_cache_object_cache_methods']['memcache']) ? ' checked ' : '');
													?> class="wppepvn_toggle_show_hide_trigger" data-target="#memcache-servers-button" />&nbsp;<?php $translate->e('Use Memcache'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i></i></p>
									</div>
									<?php endif; ?>
									
									<div style="margin-left:5%; margin-bottom: 0px;">
										<div class="checkbox" style="margin-bottom: 5px;">
											<label>
												<h4 style="margin: 0;">
													<input type="checkbox" disabled checked />&nbsp;<?php $translate->e('Use File (Default)'); ?>
												</h4>
											</label>
										</div>
										<p style="margin-left:25px;"><i><?php $translate->e('Plugin use Multi-Cache with the priority order of speed "APC > Memcache > File", help your website fast and stable operation of the most.'); ?></i></p>
									</div>
								</div>
								
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_ssl_request_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache SSL (https) Requests'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_mobile_device_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache For Mobile Device'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px; color:red; margin-bottom: 0;"><i><?php $translate->e('Warning: Don\'t turn on this option if you use one of these plugins: WP Touch, WP Mobile Detector, wiziApp, and WordPress Mobile Pack.'); ?></i></p>
								<p style="margin-left:25px; color:blue;"><i><?php $translate->e('If you are using wordpress theme responsive, you should enable this feature.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_url_get_query_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache URIs with GET query string variables'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Ex : "/?s=query..." at the end of a url.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_logged_users_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Cache For Logged Users'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_cache_prebuild_cache_enable'); ?>&nbsp;<?php $translate->e('Enable Prebuild Cache ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;margin-bottom:10px;"><i><?php $translate->e('Prebuild cache help your site load faster by creating cache of pages is the most visited.'); ?></i></p>
								
								<div id="optimize_cache_prebuild_cache_enable_container" class="wppepvn_toggle_show_hide_container">
									<div style="margin-left:5%; margin-bottom: 20px;">
										<label>
											<h4 style="margin: 0;">
												<?php $translate->e('Maximum number of pages is prebuilt each process'); ?> : <?php echo $form->render('optimize_cache_prebuild_cache_number_pages_each_process'); ?> <?php $translate->e('pages'); ?>
											</h4>
										</label>
										<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('This number depends on the performance of the server, if your server is fast then you should set this number higher and vice versa.'); ?></i></p>
										
									</div>
								</div>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Cache Timeout'); ?> : <?php echo $form->render('optimize_cache_cachetimeout'); ?> <?php $translate->e('seconds'); ?>
									</h4>
								</label>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('How long should cached pages remain fresh? You should set this value from 21600 seconds (6 hours) to 86400 seconds (24 hours). Minimum value is 300 seconds (5 minutes).'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Exclude url (Contained in url, separate them by comma)'); ?> :
									</h4>
								</label>
								<?php echo $form->render('optimize_cache_exclude_url'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('Plugin will ignore these urls.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Exclude cookie (Cookie name or combine Cookie name with cookie value, separate them by comma)'); ?> :
									</h4>
								</label>
								<?php echo $form->render('optimize_cache_exclude_cookie'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('Plugin will ignore these request cookie.'); ?></i></p>
							</div>
							
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-cache -->
			
			
			
			<div role="tabpanel" class="tab-pane" id="optimize-javascript">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Optimize Javascript</h3>
					
					<div>
					
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('optimize_javascript_enable'); ?>&nbsp;<?php $translate->e('Enable Optimize Javascript'); ?>
									</h4>
								</label>
							</div>
							<p style="margin-left:25px; color:red; margin-bottom: 0;"><i><?php $translate->e('Warning : This option will help your site load faster. However, in some cases, web layout will be error. If an error occurs, you should disable this option.'); ?></i></p>
						</div>
						
						<div id="optimize_javascript_enable_container" class="wppepvn_toggle_show_hide_container">
						
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_combine_javascript_enable'); ?>&nbsp;<?php $translate->e('Enable Combine Javascript'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This option will combine all files into one file javascript.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_minify_javascript_enable'); ?>&nbsp;<?php $translate->e('Enable Minify Javascript'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This option will help you reduce javascript code size smaller by removing redundant characters.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_move_bottom_enable'); ?>&nbsp;<?php $translate->e('Enable Move All Javascripts To Bottom'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_asynchronous_javascript_loading_enable'); ?>&nbsp;<?php $translate->e('Enable Asynchronous Javascript Loading'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_exclude_external_javascript_enable'); ?>&nbsp;<?php $translate->e('Exclude External Javascript File'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Plugin will ignore all external javascript files ( Which not scripts in your self-hosted ). You should not enable this feature unless an error occurs.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_javascript_exclude_inline_javascript_enable'); ?>&nbsp;<?php $translate->e('Exclude Inline Javascript Code'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Plugin will ignore all javascript code in your html. You should enable this feature unless an error occurs.'); ?></i></p>
								
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Exclude (Contained in url, separate them by comma)'); ?> :
									</h4>
								</label>
								<?php echo $form->render('optimize_javascript_exclude_url'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('Plugin will ignore these javascript files urls'); ?></i></p>
							</div>
						
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-javascript -->
			
			
			<div role="tabpanel" class="tab-pane" id="optimize-css">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Optimize CSS (Style)</h3>
					
					<div>
					
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('optimize_css_enable'); ?>&nbsp;<?php $translate->e('Enable Optimize CSS'); ?>
									</h4>
								</label>
							</div>
							<p style="margin-left:25px; color:red; margin-bottom: 0;"><i><?php $translate->e('Warning : This option will help your site load faster. However, in some cases, web layout will be error. If an error occurs, you should disable this option.'); ?></i></p>
						</div>
						
						<div id="optimize_css_enable_container" class="wppepvn_toggle_show_hide_container">
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_css_combine_css_enable'); ?>&nbsp;<?php $translate->e('Enable Combine CSS'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This option will combine all files into one file css.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_css_minify_css_enable'); ?>&nbsp;<?php $translate->e('Enable Minify CSS'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This option will help you reduce javascript code size smaller by removing redundant characters.'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_css_asynchronous_css_loading_enable'); ?>&nbsp;<?php $translate->e('Enable Asynchronous CSS Loading'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_css_exclude_external_css_enable'); ?>&nbsp;<?php $translate->e('Exclude External CSS Files'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Plugin will ignore all external css files ( Which not css file in your self-hosted ).'); ?></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_css_exclude_inline_css_enable'); ?>&nbsp;<?php $translate->e('Exclude Inline CSS Code'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('Plugin will ignore all CSS code in your html.'); ?></i></p>
								
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Exclude (Contained in url, separate them by comma).'); ?> :
									</h4>
								</label>
								<?php echo $form->render('optimize_css_exclude_url'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('Plugin will ignore these CSS files urls.'); ?></i></p>
							</div>
							
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-css -->
			
			
			
			<div role="tabpanel" class="tab-pane" id="optimize-html">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Optimize HTML</h3>
					
					<div>
						
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('optimize_html_enable'); ?>&nbsp;<?php $translate->e('Enable Optimize HTML (Recommended)'); ?>
									</h4>
								</label>
							</div>
						</div>
						
						<div id="optimize_html_enable_container" class="wppepvn_toggle_show_hide_container">
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<div class="checkbox" style="margin-bottom: 5px;">
									<label>
										<h4 style="margin: 0;">
											<?php echo $form->render('optimize_html_minify_html_enable'); ?>&nbsp;<?php $translate->e('Enable Minify HTML ( Recommended )'); ?>
										</h4>
									</label>
								</div>
								<p style="margin-left:25px;"><i><?php $translate->e('This option will help you reduce html code size smaller by removing redundant characters.'); ?></i></p>
							</div>
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-html -->
			
			
			<div role="tabpanel" class="tab-pane" id="optimize-cdn">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">CDN (Content Delivery Network)</h3>
					
					<div>
					
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('cdn_enable'); ?>&nbsp;<?php $translate->e('Enable CDN'); ?>
									</h4>
								</label>
							</div>
						</div>
						
						<div id="cdn_enable_container" class="wppepvn_toggle_show_hide_container">
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('CNAME (CDN)'); ?> :
									</h4>
								</label>
								<?php echo $form->render('cdn_domain'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i></i></p>
							</div>
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								<label>
									<h4 style="margin: 0;">
										<?php $translate->e('Exclude (Contained in url, separate them by comma)'); ?> :
									</h4>
								</label>
								<?php echo $form->render('cdn_exclude_url'); ?>
								<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('Plugin will ignore these urls.'); ?></i></p>
							</div>
							
							
							<hr />
							
							<div style="margin-left:5%; margin-bottom: 20px;">
								
								<h4 style="margin: 0;margin-bottom: 20px;">
									<?php $translate->e('CloudFlare.com'); ?> :
								</h4>
								
								<div style="margin-left:5%; margin-bottom: 20px;">
									<label>
										<h4 style="margin: 0;">
											<?php $translate->e('Email address'); ?> :
										</h4>
									</label>
									<?php echo $form->render('cdn_cloudflare_email'); ?>
									<p style="margin-left:0px;margin-bottom:0;"><i></i></p>
								</div>
								
								
								<div style="margin-left:5%; margin-bottom: 20px;">
									<label>
										<h4 style="margin: 0;">
											<?php $translate->e('API Key'); ?> :
										</h4>
									</label>
									<?php echo $form->render('cdn_cloudflare_api_key'); ?>
									<p style="margin-left:0px;margin-bottom:0;"><i></i></p>
								</div>
							</div>
							
							
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-cdn -->
			
			<div role="tabpanel" class="tab-pane" id="optimize-others">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Others</h3>
					
					<div>
					
						<div class="" style="margin-bottom: 20px;">
							<div class="checkbox">
								<label>
									<h4 style="margin: 0;">
										<?php echo $form->render('learn_improve_google_pagespeed_enable'); ?>&nbsp;<?php $translate->e('Enable auto learn to improve Google PageSpeed Insights'); ?>
									</h4>
								</label>
							</div>
							<p style="margin-left:0px;margin-bottom:0;"><i><?php $translate->e('This feature will automatically learn to improve your website\'s Google PageSpeed Insight over time.'); ?></i></p>
						</div>
						
					</div>
					
				</div>
				
			</div><!-- #optimize-others -->
			
			<div role="tabpanel" class="tab-pane" id="memcache-servers">
				
				<div style="padding-top: 2%; padding-bottom: 2%;">
					
					<h3 style="margin-bottom: 20px;">Memcache Servers</h3>
					
					<div>
						<div style="margin-left:5%; margin-bottom: 20px;">
							<label>
								<h4 style="margin: 0;">
									<?php $translate->e('Servers List'); ?> :
								</h4>
							</label>
							<?php echo $form->render('memcache_servers'); ?>
							<p style="margin-left:0px;margin-bottom:0;"><i>Use a new line for each servers. You must enter the following form <b>MEMCACHE_SERVER_IP:MEMCACHE_SERVER_PORT</b>. Ex : 127.0.0.1:11211</i></p>
						</div>
					</div>
					
				</div>
				
			</div><!-- #memcache-servers -->
			
			
		  </div>

		</div>

		
		<div class="form-group">
			<div class="col-sm-12">
				<input type="submit" name="submitButton" class="btn btn-primary" value="<?php $translate->e('Update Options'); ?>" />
			</div>
		</div>
	</form>

</div>
<?php

if(!class_exists("wpdcsso_updater")) {
	
	class wpdcsso_updater
	{
		
		public $current_version;
		public $update_base;
		public $plugin_slug;
		public $slug;
		
		function __construct($current_version, $update_base, $plugin_slug) {
			$this->current_version = $current_version;
			$this->update_base = $update_base;
			$this->plugin_slug = $plugin_slug;
			
			list ($t1, $t2) = explode('/', $plugin_slug);
			$this->slug = str_replace('.php', '', $t2);
			
			// define the alternative API for updating checking
			add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));
			
			// Define the alternative response for information checking
			add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
		}
		
		public function check_update($transient) {
			
			if(empty($transient->checked)) {
				return $transient;
			}
			
			$remote_info = $this->get_remote_info();
			if(!empty($remote_info)) {
				if(version_compare($this->current_version, $remote_info->new_version, '<')) {
					$obj = new stdClass();
					$obj->slug = $this->slug;
					$obj->new_version = $remote_info->new_version;
					$obj->url = $remote_info->info_page;
					$obj->package = $remote_info->download_link;
					$transient->response[$this->plugin_slug] = $obj;
				}
			}
			
			return $transient;
			
		}
		
		public function check_info($false, $action, $arg) {
			if($arg->slug === $this->slug) {
				$remote_info = $this->get_remote_info();
				return $remote_info;
			}
			return false;
		}
		
		function get_remote_info() {
			$request = wp_remote_get($this->update_base . '/latestversion');
			if(!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				$result = json_decode($request['body']);
				
				// Convert sections to PHP array
				$newsections = array();
				if($result->sections) {
					foreach($result->sections as $section) {
						foreach($section as $key => $value) {
							$newsections[$key] = $value;
						}
					}
					$result->sections = $newsections;
				}
				
				return $result;
			}
			return NULL;
		}
		
		
	}  // end class
	
}  // end if classexists

?>
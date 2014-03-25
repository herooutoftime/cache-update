<?php
/**
 * CacheUpdate class file for CacheUpdate extra
 *
 * Copyright 2014 by Andreas Bilz 
 * Created on 03-24-2014
 *
 * CacheUpdate is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * CacheUpdate is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * CacheUpdate; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package cacheupdate
 */


 class CacheUpdate {
    /** @var $modx modX */
    public $modx;
    /** @var $props array */
    public $props;

    function __construct(&$modx, $config = array()) {
        $this->modx =& $modx;
        
        // Switch for contexts
		$cxt_dir = $this->modx->context->key . '/';
		$basePath = $this->modx->getOption('cacheupdate.core_path',$config,$this->modx->getOption('core_path').'components/cacheupdate/');

        $this->props =& $config;
        $this->props = array_merge(array(
        	'basePath' => $basePath,
        	'vendorPath' => $basePath . 'vendor/',
        	'cache_dir' => $this->modx->getOption('cacheupdate.cache_dir', null, $this->modx->getOption('core_path').'cache/blfpc/production/' . $cxt_dir),
        	'cache_elements' => $this->modx->getOption('cacheupdate.elements', null, false),
        	), $this->props
        );
        require_once $this->props['vendorPath'] . 'jslikehtmlelement/jslikehtmlelement.class.php';
    }

    /**
	 * Update specific content sections
	 * @param boolean $cron If true this method will iterate all specified files via cron
	 */
	public function runCron($cron = false)
	{
		$cnt = array();
		if(!$this->props['cache_elements'])
			return false;

		// Get the elements & related static files from settings in array style
		// See MODx system setting 'cacheupdate.elements' for further information
		$lines  = explode(PHP_EOL, $this->props['cache_elements']);
		foreach ($lines as $line) {
			$line = explode('==', $line);
			$contents[array_shift($line)] = array_shift($line);
		}
		
		$this->error = array();
		// Check static files for last modified date
		$this->log[] = 'Static files and last modified time';
		foreach($contents as $element => $file) {
			if(!file_exists($this->modx->getOption('assets_path') . 'elements/static/' . $file))
				continue;
			$mtimes[$file] = filemtime($this->modx->getOption('assets_path') . 'elements/static/' . $file);
			$this->log[] = "\t" . $file . ': ' . date('r', $mtimes[$file]);
		}
		$cnt['found_static'] = count($mtimes);
		// Set the latest timestamp as comparison value
        $max_static = max($mtimes);

		$cache_files = array();
		// Cron task
		if($cron) {
			$cache_files = array();
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->props['cache_dir']), RecursiveIteratorIterator::SELF_FIRST);
			foreach($objects as $file){
				// Ignore directories
				if($file->isDir())
					continue;
				// Only include cache files which modified date is older than the earliest static file update
				if(filemtime($file) >= $max_static)
					continue;
			    $cache_files[] = $file->getPathname();
			}
		}
		// If no relevant cache files found: Stop it
        if(empty($cache_files))
        	return;
        
        $cnt['found_files'] = count($cache_files);

		$this->log[] = 'Found files: ' . $cnt['found_files'];
		$cnt_files = 0;
		$cnt_replace = 0;
		foreach ($cache_files as $cache_file) {
			// Find the content in DOM
			// Load HTML file and extension to get JS-like innerHTML function
			$dom = new DOMDocument();
			$dom->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
			// @$dom->loadHTML('<html></html>');
			@$dom->loadHTMLFile($cache_file);

			// $this->log[] = str_repeat('-', 50);
			$this->log[] = "$cache_file will be udpated";
			$mtime_cache = filemtime($cache_file);
			foreach($contents as $element => $file) {
				// Not a static resource -> bail out
				if(!file_exists($this->modx->getOption('assets_path') . 'elements/static/' . $file)) {
					$this->info[$cache_file][] = 'Static file not found ## ' . $file . ' ## in ' . $this->modx->getOption('assets_path') . 'elements/static/';
					continue;
				}
				
				// Only update cache files older than static file
				$mtime_static = filemtime($this->modx->getOption('assets_path') . 'elements/static/' . $file);
				if($mtime_cache > $mtime_static)
					continue;
				
				$this->log[] = 'Static file is newer (' . date('r', $mtime_static) . ') than cache file (' . date('r', $mtime_cache) . ')';
				/**
				 * @todo Increase flexibility: Enable class-based ('.menu') replacement
				 */
				$el = $dom->getElementById(str_replace('#', '', $element));
				if(!$el || empty($el))
					$this->error[$cache_file][] = 'Element ## ' . $element . ' ## was not found in DOM. Please make sure the specified element exists';

				if(count($this->error[$cache_file]) > 0)
					continue;

				$el = $dom->getElementById(str_replace('#', '', $element));
				$el->innerHTML = file_get_contents($this->modx->getOption('assets_path') . 'elements/static/' . $file);
				$this->log[] = "$element ($file) was updated";
				$cnt_replace++;
			}
			// Log errors and infos
			if(count($this->error[$cache_file]) > 0) {
				$this->log[] = 'Errors:';
				foreach($this->error[$cache_file] as $error) {
					$this->log[] = "\t" . $error;
				}
			}
			if(count($this->info[$cache_file]) > 0) {
				$this->log[] = 'Information:';
				foreach($this->info[$cache_file] as $info) {
					$this->log[] = "\t" . $info;
				}
			}
			// Save the refreshed content if no error happened
		    if(!empty($this->error[$cache_file]))
		    	continue;

	    	if($size = $dom->saveHTMLFile($cache_file))
	    		$this->log[] = "$cache_file was rewritten (Size: $size)";
		    $cnt_files++;
		}
		$cnt['worked_files'] = $cnt_files;
		$cnt['replacements'] = $cnt_replace;
		foreach($cnt as $key => $value) {
			$count_log[] = str_replace('_', ' ', $key) . ': ' . $value;
		}
		$this->log[] = implode(', ', $count_log);
		// Store success
		// Store count
		// Log replacement actions
		$this->log(array(
				'logs' => $this->log,
				'log_dir' => 'contentupdate',
				'log_name' => 'contentupdate',
			)
		);
		if(!empty($this->error))
			return false;
		return true;
	}

	/**
	 * Custom log method
	 * @since  2014-03-24	Added $options to make this more flexible when logging in this class
	 */
	public function log($options)
	{
		$log = $options['logs'];
		$log_dir = $this->modx->getOption('log_dir', $options, 'priority');
		$log_name = $this->modx->getOption('log_name', $options, $this->props['priority']);
		$log_prefix = $this->modx->getOption('log_prefix', $options, null);
		$log_level = $this->modx->getOption('log_prefix', $options, MODX_LOG_LEVEL_INFO);

		$this->modx->setLogLevel($log_level);
		$log_target = array(
		    'target'=>'FILE',
		    'options' => array(
		        'filename' => $log_dir . '/' . date('Y-m-d') . '/' . $log_name .'.log'
		    )
		);
		$this->modx->log($log_level, str_repeat('=', 75), $log_target);
		foreach($log as $l) {
			$this->modx->log($log_level, $log_prefix . $l, $log_target);
		}
		$this->modx->setLogLevel(MODX_LOG_LEVEL_ERROR);
	}
}
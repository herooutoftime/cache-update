<?php
/**
 * Cron script to generate cache resources in background
 * 
 * @package CacheUpdate
 */
// Init MODx

if(file_exists(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . '/config.core.php')) {
	require_once dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))) . '/config.core.php';
} else {
	require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}

// require_once '/var/www/development/config.core.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');

$cacheUpdateCorePath = $modx->getOption('cacheupdate.core_path',null,$modx->getOption('core_path').'components/cacheupdate/');
require_once($cacheUpdateCorePath . 'model/cacheupdate/cacheupdate.class.php');
$cache = new CacheUpdate($modx, array('cli' => 1));

// Run updateContent as cron-task = true
$cache->runCron(true);
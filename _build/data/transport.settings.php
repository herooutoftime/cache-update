<?php
/**
 * systemSettings transport file for CacheUpdate extra
 *
 * Copyright 2014 by Andreas Bilz 
 * Created on 03-25-2014
 *
 * @package cacheupdate
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $systemSettings */


$systemSettings = array();

$systemSettings[1] = $modx->newObject('modSystemSetting');
$systemSettings[1]->fromArray(array (
  'key' => 'cacheupdate.cache_dir',
  'value' => '{core_path}cache/xfpc/',
  'xtype' => 'textfield',
  'namespace' => 'cacheupdate',
  'area' => '',
  'name' => 'Cache directory',
  'description' => 'In which cache directory are the relevant cache files to update?',
), '', true, true);
$systemSettings[2] = $modx->newObject('modSystemSetting');
$systemSettings[2]->fromArray(array (
  'key' => 'cacheupdate.elements',
  'value' => '#menu==mainmenu.tpl.html
#header==header.tpl.html',
  'xtype' => 'textarea',
  'namespace' => 'cacheupdate',
  'area' => '',
  'name' => 'Update elements',
  'description' => 'A array like string to set the HTML ID and the corresponding static file (stored in assets/elements/static/)',
), '', true, true);
return $systemSettings;

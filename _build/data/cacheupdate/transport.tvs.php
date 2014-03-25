<?php
/**
 * templateVars transport file for CacheUpdate extra
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
/* @var xPDOObject[] $templateVars */


$templateVars = array();

$templateVars[1] = $modx->newObject('modTemplateVar');
$templateVars[1]->fromArray(array (
  'id' => 1,
  'property_preprocess' => false,
  'type' => 'listbox',
  'name' => 'tvCacheStaticFile',
  'caption' => 'Static File',
  'description' => 'Which static file should be rendered if this resource gets saved?',
  'elements' => 'No static file==0||Mainmenu==mainmenu.tpl.html||Footer==footer.tpl.html',
  'rank' => 0,
  'display' => 'default',
  'default_text' => '@INHERIT',
  'properties' => 
  array (
  ),
  'input_properties' => 
  array (
    'allowBlank' => 'true',
    'listWidth' => '',
    'title' => 'Choose static file',
    'typeAhead' => 'false',
    'typeAheadDelay' => '250',
    'forceSelection' => 'false',
    'listEmptyText' => '',
  ),
  'output_properties' => 
  array (
  ),
), '', true, true);
$templateVars[2] = $modx->newObject('modTemplateVar');
$templateVars[2]->fromArray(array (
  'id' => 2,
  'property_preprocess' => false,
  'type' => 'listbox',
  'name' => 'tvCacheResource',
  'caption' => 'Cache Resource',
  'description' => 'Which resource should be triggered to rerender the correct static file?',
  'elements' => '@SELECT pagetitle, id FROM modx_site_content WHERE parent=6526',
  'rank' => 0,
  'display' => 'default',
  'default_text' => '@INHERIT',
  'properties' => 
  array (
  ),
  'input_properties' => 
  array (
    'allowBlank' => 'true',
    'listWidth' => '',
    'title' => '',
    'typeAhead' => 'false',
    'typeAheadDelay' => '250',
    'forceSelection' => 'false',
    'listEmptyText' => '',
  ),
  'output_properties' => 
  array (
  ),
), '', true, true);
return $templateVars;

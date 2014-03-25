<?php
/**
 * Plugin: Updates the main menu
 * @todo Make more flexible
 */
$event = $modx->event->name;
switch($event) {
    case 'OnDocFormSave':
        $cache_static = $resource->getTVValue('tvCacheStaticFile');
        $cache_preview = $resource->getTVValue('tvCacheResource');

        // $modx->log(MODX_LOG_LEVEL_ERROR, 'tvCacheResourceID ' . $resource->get('id'));
        // $modx->log(MODX_LOG_LEVEL_ERROR, 'tvCacheStaticFile ' . $resource->getTVValue('tvCacheStaticFile'));
        // $modx->log(MODX_LOG_LEVEL_ERROR, 'tvCacheResource ' . $resource->getTVValue('tvCacheResource'));
        
        if(empty($cache_static) || empty($cache_preview))
            return true;

        $ch = curl_init();
        $url = $modx->makeUrl($cache_preview, 'web', array('nocache' => time()), 'full');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec ($ch);
        if(empty($result))
            return true;
        
        if(!file_exists($modx->getOption('assets_path') . 'elements/static/' . $cache_static))
            $modx->log(MODX_LOG_LEVEL_ERROR, 'Static file will be created');
        if(file_put_contents($modx->getOption('assets_path') . 'elements/static/' . $cache_static, $result))
        	$modx->log(MODX_LOG_LEVEL_ERROR, 'Static file was updated');
        
        curl_close($ch);
        break;
}
return true;
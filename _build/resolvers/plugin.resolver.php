<?php
/**
* Resolver to connect plugins to system events for CacheUpdate extra
*
* Copyright 2014 by Andreas Bilz 
* Created on 03-25-2014
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
* @package cacheupdate
* @subpackage build
*/
/* @var $object xPDOObject */
/* @var $pluginObj modPlugin */
/* @var $mpe modPluginEvent */
/* @var xPDOObject $object */
/* @var array $options */
/* @var $modx modX */
/* @var $pluginObj modPlugin */
/* @var $pluginEvent modPluginEvent */
/* @var $newEvents array */

if (!function_exists('checkFields')) {
    function checkFields($required, $objectFields) {

        global $modx;
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field])) {
                $modx->log(MODX::LOG_LEVEL_ERROR, '[Plugin Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
}


$newEvents = array (
            );


if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            foreach($newEvents as $k => $fields) {

                $event = $modx->getObject('modEvent', array('name' => $fields['name']));
                if (!$event) {
                    $event = $modx->newObject('modEvent');
                    if ($event) {
                        $event->fromArray($fields, "", true, true);
                        $event->save();
                    }
                }
            }

            $intersects = array (
                0 =>  array (
                  'pluginid' => 'cacheupdate',
                  'event' => 'OnDocFormSave',
                  'priority' => '0',
                  'propertyset' => '0',
                ),
            );

            if (is_array($intersects)) {
                foreach ($intersects as $k => $fields) {
                    /* make sure we have all fields */
                    if (!checkFields('pluginid,event,priority,propertyset', $fields)) {
                        continue;
                    }
                    $event = $modx->getObject('modEvent', array('name' => $fields['event']));

                    $plugin = $modx->getObject('modPlugin', array('name' => $fields['pluginid']));
                    $propertySetObj = null;
                    if (!empty($fields['propertyset'])) {
                        $propertySetObj = $modx->getObject('modPropertySet',
                            array('name' => $fields['propertyset']));
                    }
                    if (!$plugin || !$event) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Plugin and/or Event ' .
                            $fields['plugin'] . ' - ' . $fields['event']);
                        continue;
                    }
                    $pluginEvent = $modx->getObject('modPluginEvent', array('pluginid'=>$plugin->get('id'),'event' => $fields['event']) );
                    
                    if (!$pluginEvent) {
                        $pluginEvent = $modx->newObject('modPluginEvent');
                    }
                    if ($pluginEvent) {
                        $pluginEvent->set('event', $fields['event']);
                        $pluginEvent->set('pluginid', (integer) $plugin->get('id'));
                        $pluginEvent->set('priority', (integer) $fields['priority']);
                        if ($propertySetObj) {
                            $pluginEvent->set('propertyset', (integer) $propertySetObj->get('id'));
                        } else {
                            $pluginEvent->set('propertyset', 0);
                        }

                    }
                    if (! $pluginEvent->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error saving pluginEvent for ' .
                            $fields['plugin'] . ' - ' . $fields['event']);
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            foreach($newEvents as $k => $fields) {
                $event = $modx->getObject('modEvent', array('name' => $fields['name']));
                if ($event) {
                    $event->remove();
                }
            }
            break;
    }
}

return true;
<?php
/* ********************************************************************************
 * The content of this file is subject to the VTiger Premium ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('data/CRMEntity.php');
require_once('data/Tracker.php');
require_once 'vtlib/Vtiger/Module.php';

class VTEStore extends CRMEntity {
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    function vtlib_handler($modulename, $event_type) {
        if($event_type == 'module.postinstall') {
            self::addWidgetTo();
            self::iniData();
            self::resetValid();
        } else if($event_type == 'module.disabled') {
            // TODO Handle actions when this module is disabled.
            self::removeWidgetTo();
            self::removeEventHandle();
        } else if($event_type == 'module.enabled') {
            // TODO Handle actions when this module is enabled.
            self::addWidgetTo();
        } else if($event_type == 'module.preuninstall') {
            // TODO Handle actions when this module is about to be deleted.
            self::removeWidgetTo();
            self::removeEventHandle();
        } else if($event_type == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
        } else if($event_type == 'module.postupdate') {
            self::removeWidgetTo();
            self::removeEventHandle();
            self::addWidgetTo();
            self::iniData();
            self::resetValid();
        }
    }

    static function iniData() {
        global $adb,$root_directory,$current_user,$site_URL, $vtiger_current_version;

        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder= "layouts/vlayout";
        }else{
            $template_folder= "layouts/v7";
        }
        copy($root_directory.$template_folder.'/modules/VTEStore/resources/images/VTEStore.png',$root_directory.$template_folder.'/skins/images/VTEStore.png');

        $sql = "SELECT * FROM vtestore_system_info WHERE `name`=?";
        $res = $adb->pquery($sql,array('VTPremiumHeader'));
        if($adb->num_rows($res)==0){
            $VTPremiumHeaderData=array();
            $VTPremiumHeaderData['customerid']='';
            $VTPremiumHeaderData['username']='';
            $VTPremiumHeaderData['email']='';
            $VTPremiumHeaderData['vtiger_url']=$site_URL;
            $VTPremiumHeaderData['subsxcription_status']='';
            $VTPremiumHeaderData['expiration_date']='';
            $VTPremiumHeaderData['remain_date']='';
            $VTPremiumHeaderData['version']='1.0.0';
            $VTPremiumHeaderData['is_latest_version']=0;
            $VTPremiumHeaderData['subscription_id']='';
            $VTPremiumHeaderData['chargify_customer_id']='';
            $VTPremiumHeaderData['billing_portal_url']='';
            $VTPremiumHeaderData['customer_status']='';
            $VTPremiumHeaderData['user_installed']=$current_user->id;
            $sql="INSERT INTO vtestore_system_info(`name`,`value`,`description`) VALUES (?,?,?)";
            $adb->pquery($sql,array('VTPremiumHeader',json_encode($VTPremiumHeaderData),'Data for header icon'));
        }
    }

    /**
     * Add header script to other module.
     * @return unknown_type
     */
     
    static function addWidgetTo() {
        global $adb,$vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        $module = Vtiger_Module::getInstance("VTEStore");
        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder= "layouts/vlayout";
        }else{
            $template_folder= "layouts/v7";
        }
        if($module) {
            $module->addLink('HEADERSCRIPT', 'VTEStoreJs', $template_folder.'/modules/VTEStore/resources/VTEStore.js');
        }
        $max_id=$adb->getUniqueID('vtiger_settings_field');
        $blockid=4;
        $res=$adb->pquery("SELECT blockid FROM `vtiger_settings_blocks` WHERE label='LBL_OTHER_SETTINGS'", array());
        if($adb->num_rows($res)>0){
            while ($row=$adb->fetch_row($res)){
                $blockid=$row['blockid'];
            }
        }
        $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)",array($max_id,$blockid,'Extension Pack', 'Settings area for Extension Pack', 'index.php?module=VTEStore&parent=Settings&view=Settings&reset=1',$max_id));
    }

    static function removeWidgetTo() {
        global $adb,$vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        $module = Vtiger_Module::getInstance("VTEStore");
        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder= "layouts/vlayout";
        }else{
            $template_folder= "layouts/v7";
        }
        if($module) {
            $module->deleteLink('HEADERSCRIPT', 'VTEStoreJs', $template_folder.'/modules/VTEStore/resources/VTEStore.js');
        }
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?",array('VTE Store'));
        $adb->pquery("DELETE FROM vtiger_settings_blocks WHERE `label` = ?",array('VTE Store'));
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?",array('VTiger Premium'));
        $adb->pquery("DELETE FROM vtiger_settings_blocks WHERE `label` = ?",array('VTiger Premium'));
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?",array('Extension Pack'));
        $adb->pquery("DELETE FROM vtiger_settings_blocks WHERE `label` = ?",array('Extension Pack'));
    }

    static function resetValid() {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;",array('VTEStore'));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);",array('VTEStore','0'));
    }

    //Create handle
    static function addEventHandle(){
        global $adb;
        $em = new VTEventsManager($adb);
        $em->registerHandler('vtiger.entity.aftersave', 'modules/VTEStore/VTEStoreHandler.php', 'VTEStoreHandler');
    }

    // Remove handle
    static function removeEventHandle(){
        global $adb;
        $em = new VTEventsManager($adb);
        $em->unregisterHandler('VTEStoreHandler');
    }
}
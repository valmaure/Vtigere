<?php
/* ********************************************************************************
 * The content of this file is subject to the VTiger Premium ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

include_once 'vtlib/Vtiger/Module.php';
class VTEStore_Uninstall_View extends Settings_Vtiger_Index_View {

    function process (Vtiger_Request $request) {
        global $adb,$vtiger_current_version;

        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder= "layouts/vlayout";
        }else{
            $template_folder= "layouts/v7";
        }

        echo '<div class="container-fluid">
                <div class="widget_header row-fluid">
                    <h3>VTiger Premium</h3>
                </div>
                <hr>';
        // Uninstall module
        $module = Vtiger_Module::getInstance('VTEStore');
        if ($module) $module->delete();
        // drop tables
        $sql = "DROP TABLE vtestore_user, vtestore_system_info, vtestore_other_setting";
        $result = $adb->pquery($sql,array());
        echo "&nbsp;&nbsp;- Delete VTiger Premium tables";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";
        echo '<br>';
        // remove directory
        if(version_compare($vtiger_current_version, '7.0.0', '>=')) {
            $res_template = $this->delete_folder('layouts/vlayout/modules/VTEStore');
        }

        $res_template = $this->delete_folder($template_folder.'/modules/VTEStore');
        echo "&nbsp;&nbsp;- Delete VTiger Premium template folder";
        if($res_template) echo " - DONE"; else echo " - <b>ERROR</b>";
        echo '<br>';

        $res_module = $this->delete_folder('modules/VTEStore');
        echo "&nbsp;&nbsp;- Delete VTiger Premium module folder";
        if($res_module) echo " - DONE"; else echo " - <b>ERROR</b>";
        echo '<br>';
        // Remove module from other settings
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?",array('VTiger Premium'));
        echo "Module was Uninstalled.";
        echo '</div>';
    }

    function delete_folder($tmp_path){
        // check and set folder access
        if(!is_writeable($tmp_path) && is_dir($tmp_path)) {
            chmod($tmp_path,0777);
        }
        $handle = opendir($tmp_path);
        while($tmp=readdir($handle)) {
            if($tmp!='..' && $tmp!='.' && $tmp!=''){
                // check and set file access before delete file
                if(is_writeable($tmp_path.'/'.$tmp) && is_file($tmp_path.'/'.$tmp)) {
                    unlink($tmp_path.'/'.$tmp);
                } elseif(!is_writeable($tmp_path.'/'.$tmp) && is_file($tmp_path.'/'.$tmp)){
                    chmod($tmp_path.'/'.$tmp,0666);
                    unlink($tmp_path.'/'.$tmp);
                }

                // check and set folder access before delete folder
                if(is_writeable($tmp_path.'/'.$tmp) && is_dir($tmp_path.'/'.$tmp)) {
                    $this->delete_folder($tmp_path.'/'.$tmp);
                } elseif(!is_writeable($tmp_path.'/'.$tmp) && is_dir($tmp_path.'/'.$tmp)){
                    chmod($tmp_path.'/'.$tmp,0777);
                    $this->delete_folder($tmp_path.'/'.$tmp);
                }
            }
        }
        closedir($handle);
        rmdir($tmp_path);
        if(!is_dir($tmp_path)) {
            return true;
        } else {
            return false;
        }
    }
}
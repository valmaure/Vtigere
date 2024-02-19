<?php

class VTEStore_VTEModule_Model extends Vtiger_Base_Model {
	protected $apiUrl = null;
	protected $siteURL = null;
	protected $message = null;

	public function __construct() {
		parent::__construct();

		global $site_URL;
		if (empty($site_URL)) {
            throw new Exception('Invalid configuration.');
        }
        $this->siteURL = $site_URL;
	}

    public function upgradeVTEStoreModule($moduleInfo){
        global $client, $site_URL,$root_directory,$vtiger_current_version;
        $db=PearDatabase::getInstance();
        $dataInput=json_encode($moduleInfo);

        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder= "layouts/vlayout";
        }else{
            $template_folder= "layouts/v7";
        }

        // Get and install/upgrade module
        $moduleName=$moduleInfo['moduleName'];
        $extensionName=$moduleName;
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $phpVersion=$moduleInfo['phpVersion'];
        $uploadFileName=$uploadDir."/".$extensionName."-PHP".$phpVersion.".zip";
        if(version_compare($vtiger_current_version, '7.0.0', '<')) {
            $fileUrl="http://license.vtexperts.com/stable_zip/$extensionName/".$extensionName."_VT6_PHP".$phpVersion.".zip";
        }else{
            $fileUrl="http://license.vtexperts.com/stable_zip/$extensionName/".$extensionName."_VT7_PHP".$phpVersion.".zip";
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$fileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);

        if(trim($file_content)==''){
            $file_content=file_get_contents($fileUrl);
        }

        if (stripos($file_content, '<title>403 Forbidden</title>') !== false){
            $error='1';
            $message = "<br><h5 style='padding: 10px'>Unable to connect to license server. We've temporarily suspended your account. This can be caused by multiple accounts registered on the same Vtiger instance, unauthorized/duplicate or test accounts.<br>Please contact us at help@vtexperts.com to get this corrected. Be sure to include your Vtiger URL and email registered (if available).<br>Thank you.</h5>";
        } else {
            $downloaded_file = fopen($uploadFileName, 'w');
            fwrite($downloaded_file, $file_content);
            fclose($downloaded_file);
            checkFileAccess($uploadFileName);


            $package = new Vtiger_Package();
            $error = '0';
            if ($package->checkZip($uploadFileName)) {
                // Delete Module folder before install
                if (version_compare($vtiger_current_version, '7.0.0', '>=')) {
                    $this->delete_folder("layouts/vlayout/modules/$moduleName");
                    $this->delete_folder("test/templates_c/v7", 1);
                } else {
                    $this->delete_folder("test/templates_c/vlayout", 1);
                }
                $this->delete_folder($template_folder . "/modules/$moduleName");
                $this->delete_folder("modules/$moduleName");

                $package->update(Vtiger_Module::getInstance($moduleName), $uploadFileName);
                $db->pquery("UPDATE `vte_modules` SET `valid`='1' WHERE (`module`=?);", array($moduleName));
                $message = 'Upgraded module ' . $moduleName;

                // Update new version for vtestore_system_info
                $sql = "SELECT version FROM `vtiger_tab` WHERE `name`='VTEStore'";
                $res = $db->pquery($sql, array());
                $params = array();
                while ($row = $db->fetch_row($res)) {
                    $params['version'] = $row['version'];
                    $this->updateSystemInfo($params);
                }
            } else {
                $error = '1';
                $message = 'Cannot create zip file and install module ' . $moduleName;
            }

            // Remove install/upgrade file in VT after installed/Upgraded
            //checkFileAccessForDeletion($uploadFileName);
            unlink($uploadFileName);
        }

        $dataReturn['error'] = $error;
        $dataReturn['message'] = $message;

        return $dataReturn;
    }



    function getPhpVersion() {
        $phpVersion=phpversion();
        $phpVersion=substr($phpVersion,0,3);
        $phpVersion = str_replace('.','',$phpVersion);
        if($phpVersion==70){
            $phpVersion='56';
        }

        return $phpVersion;
    }

    function delete_folder($tmp_path, $ignoreCurrentDir=0){
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
        if($ignoreCurrentDir==0){
            rmdir($tmp_path);
        }

        if(!is_dir($tmp_path)) {
            return true;
        } else {
            return false;
        }
    }

    function getSystemInfo(){
        global $current_user, $adb, $vtiger_current_version;

        $sql = "SELECT * FROM vtestore_system_info WHERE `name`=?";
        $res = $adb->pquery($sql,array('VTPremiumHeader'));
        while($row=$adb->fetch_row($res)){
            $VTPremiumHeaderData=json_decode(html_entity_decode($row['value']));
        }
        if($VTPremiumHeaderData->user_installed==$current_user->id){
            $VTPremiumHeaderData->showHeaderIcon=1;
        }else{
            $VTPremiumHeaderData->showHeaderIcon=0;
        }

        $VTPremiumHeaderData->vtversion=$vtiger_current_version;

        return $VTPremiumHeaderData;
    }

    function updateSystemInfo($params){
        global $adb;

        $sql = "SELECT * FROM vtestore_system_info WHERE `name`=?";
        $res = $adb->pquery($sql,array('VTPremiumHeader'));
        while($row=$adb->fetch_row($res)){
            $VTPremiumHeaderData=json_decode(html_entity_decode($row['value']));
        }

        foreach($params as $param=>$val){
            $VTPremiumHeaderData->$param=$val;
        }

        $headerData=json_encode($VTPremiumHeaderData);
        $sql="UPDATE vtestore_system_info SET `value`=? WHERE `name`=?";
        $adb->pquery($sql,array($headerData,'VTPremiumHeader'));
    }
}

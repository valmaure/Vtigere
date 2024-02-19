<?php

chdir(dirname(__FILE__). '/../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php'; 

global $current_user, $adb, $root_directory, $log, $site_URL;
$current_user = Users::getActiveAdminUser(); 
$currentusername = $current_user->first_name.' '.$current_user->last_name;
$data = json_decode(file_get_contents("php://input"));

$expiryDate = $_REQUEST['expirydate'];

$licenseExpiryDate = Settings_CTWhatsApp_SaveLicense_Action::encrypt_decrypt($expiryDate,'e');

$adb->pquery("UPDATE vtiger_ctwhatsapp_botlicense set expirydate = ?", array($licenseExpiryDate));

?>
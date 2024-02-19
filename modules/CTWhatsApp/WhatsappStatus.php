<?php
/*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

chdir(dirname(__FILE__). '/../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php'; 

global $current_user, $adb, $root_directory, $log, $site_URL;
$current_user = Users::getActiveAdminUser();

$data = json_decode(file_get_contents("php://input"));

$getWhatsappStatus = $data->value;
$getLowerStatus = strtolower($data->value);
$whatsappNumber = $data->number;
$userid = $_REQUEST['userid'];

$msgid = $data->key->id;
$status = $data->update->status;

CTWhatsApp_Module_Model::updateWhatsappScanNumber($getWhatsappStatus, $getLowerStatus, $whatsappNumber, $userid, $msgid, $status);

?>

<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';
require_once('data/Tracker.php');
require_once 'vtlib/Vtiger/Module.php';
require_once('modules/com_vtiger_workflow/include.inc');

class CTWhatsApp extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_ctwhatsapp'; 
	var $table_index= 'ctwhatsappid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ctwhatsappcf', 'ctwhatsappid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ctwhatsapp', 'vtiger_ctwhatsappcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ctwhatsapp' => 'ctwhatsappid',
		'vtiger_ctwhatsappcf'=>'ctwhatsappid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Whatsapp No' => Array('ctwhatsapp', 'whatsapp_no'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Whatsapp No' => 'whatsapp_no',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'whatsapp_no';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Whatsapp No' => Array('ctwhatsapp', 'whatsapp_no'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Whatsapp No' => 'whatsapp_no',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('whatsapp_no');

	// For Alphabetical search
	var $def_basicsearch_col = 'whatsapp_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'whatsapp_no';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('whatsapp_no','assigned_user_id');

	var $default_order_by = 'whatsapp_no';
	var $default_sort_order='ASC';

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
			$this->updateSettings();
			$this->installWorkflow();
			$this->relatedTab();
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			$this->installWorkflow();
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			$this->installWorkflow();
			// TODO Handle actions after this module is updated.
			$this->updateSettings();
		}
 	}
 	
 	static function relatedTab(){
		require_once("modules/CTWhatsApp/WhatsappRelatedModule.php");
	}

	static function sendsms($message, $tonumbers, $ownerid = false,$relatedid, $sendMessagewpnumber) {
		global $current_user, $adb, $site_URL, $root_directory;
		if($ownerid === false) {
			if(isset($current_user) && !empty($current_user)) {
				$ownerid = $current_user->id;
			} else {
				$ownerid = 1;
			}
		}

		$currenUserID = $_SESSION['authenticated_user_id'];
		
		$currentdate = date('Y-m-d');
		$date_var = date("Y-m-d H:i:s");
		$getLicenseDetail = CTWhatsApp_Record_Model::getWhatsAppLicenseDetail();
		$expiryDate = $getLicenseDetail['expiryDate'];
		$licenseKey = $getLicenseDetail['licenseKey'];
		$date = Settings_CTWhatsApp_ConfigurationDetail_View::encrypt_decrypt($expiryDate, $action='d');

		$getWhatsappAccount = CTWhatsApp_Record_Model::getWhatsappAccountDetail($licenseKey);
		$oneDayMessages = CTWhatsApp_Record_Model::getOneDaysMessages();

		if(is_numeric($message)){
			$getWhatsappTemplateData = CTWhatsApp_Record_Model::getWhatsAppTemplatesData($message);
			$templatesID = $getWhatsappTemplateData['templatesID'];
			$messageBody = $getWhatsappTemplateData['message'];
			$templateMsg = $getWhatsappTemplateData['templateMsg'];
			$wptemplateImage = $getWhatsappTemplateData['wptemplateImage'];
			if($wptemplateImage == ''){
				$getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsapptemplates 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid 
					WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid = ?", array($message));
				$templatesID = $adb->query_result($getWhatsappTemplateQuery, 0, 'ctwhatsapptemplatesid');
				$messageBody = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_text');
				$templateMsg = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_msg');
				$wptemplateImage = $adb->query_result($getWhatsappTemplateQuery, 0, 'storedname');
			}
		}else{
			$messageBody = $message;
		}
		
		foreach($tonumbers as $tonumbersKey => $tonumbersValue) {
			if($tonumbersValue){
				$relatedToData = CTWhatsApp_Record_Model::getRelatedToId(substr($tonumbersValue,-9));
				$moduleRecordID = $relatedToData['relatedTo'];	
				$displayname = $relatedToData['displayname'];	
			}

			if($tonumbersValue){
				$getnumberImportant = CTWhatsApp_Record_Model::getWhatsappNumberImportant($tonumbersValue);
			}

			$setype = VtigerCRMObject::getSEType($moduleRecordID);
			if($setype == 'CTWhatsApp'){
				$recordModelWhatsApp = Vtiger_Record_Model::getInstanceById($moduleRecordID, 'CTWhatsApp');
				$moduleRecordID = $recordModelWhatsApp->get('whatsapp_contactid');

				if($moduleRecordID == ''){
					$ehatsappRecordNumber = preg_replace('/[^A-Za-z0-9]/', '', $tonumbersValue);
					$relatedToData = CTWhatsApp_Record_Model::getRelatedToId($ehatsappRecordNumber);
					$moduleRecordID = $relatedToData['relatedTo'];
					$displayname = $relatedToData['displayname'];
				}
			}

			if($sendMessagewpnumber){
				$getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappusers WHERE whatsappno = ?", array($sendMessagewpnumber));
				$whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
				if($whatsaAppRows == 0){
					$getConfigurationDatasQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration WHERE whatsappno = ?", array($sendMessagewpnumber));
					$whatsaAppRow = $adb->num_rows($getConfigurationDatasQuery);
					if($whatsaAppRow == 1){
						$api_url = $adb->query_result($getConfigurationDatasQuery, 0, 'api_url');
						$auth_token = $adb->query_result($getConfigurationDatasQuery, 0, 'auth_token');
						$customfield1 = $adb->query_result($getConfigurationDatasQuery, 0, 'customfield1');
						$whatsappScanNo = $adb->query_result($getConfigurationDatasQuery, 0, 'whatsappno');
						$whatsappStatus = $adb->query_result($getConfigurationDatasQuery, 0, 'whatsappstatus');
						$configureUserid = $adb->query_result($getConfigurationDatasQuery, 0, 'configureUserid');
					}
				}else{
					$api_url = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
					$auth_token = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
					$customfield1 = $adb->query_result($getConfigurationDataQuery, 0, 'customfield1');
					$whatsappScanNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappno');
					$whatsappStatus = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappstatus');
					$configureUserid = $adb->query_result($getConfigurationDataQuery, 0, 'configureUserid');
				}
			}else{
				if($moduleRecordID){
					$moduleRecordModel = Vtiger_Record_Model::getInstanceById($moduleRecordID, $setype);
					$assignuserID = $moduleRecordModel->get('assigned_user_id');
					
	    			$userScanWhatsappData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($assignuserID);
					$api_url = $userScanWhatsappData['api_url'];
					$auth_token = $userScanWhatsappData['authtoken'];
					$customfield1 = $userScanWhatsappData['customfield1'];
					$whatsappScanNo =$userScanWhatsappData['whatsappno'];
					$configureUserid =$userScanWhatsappData['configureUserid'];
					$whatsappStatus =$userScanWhatsappData['whatsappstatus'];
				
					if($auth_token == ''){
						$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationDataWithId();
						$api_url = $configurationData['api_url'];
						$auth_token = $configurationData['authtoken'];
						$customfield1 = $configurationData['customfield1'];
						$whatsappScanNo =$configurationData['whatsappno'];
						$configureUserid =$configurationData['configureUserid'];
						$whatsappStatus =$configurationData['whatsappstatus'];
					}
				}
			}


			if (strpos($tonumbersValue, '@g.us') !== false) {
				$tonumbersValue = $tonumbersValue;
			}else{
				$mobileno = preg_replace('/[^A-Za-z0-9]/', '', $tonumbersValue);
				$tonumbersValue = CTWhatsApp_Module_Model::getMobileNumber($mobileno, $customfield1);
			}

			$bodydata = str_replace('\r\n',' ',html_entity_decode($messageBody));
			if($setype == 'CTWhatsApp'){
				$msgbody = getMergedDescription($bodydata,$_REQUEST['currentid'],$setype);
			}else{
				$sourceModuleId = explode('x', $relatedid);
				if($sourceModuleId[1]){
					$sourceModuleSetype = VtigerCRMObject::getSEType($sourceModuleId[1]);
					$url = $site_URL."/index.php?module=".$sourceModuleSetype."&view=Detail&record=".$sourceModuleId[1];
					$url_html = $url;
					$textmessage = str_replace('CRM Detail View URL', $url_html, $messageBody);
					
					$bodydata = str_replace('\r\n',' ',html_entity_decode($textmessage));
					$msgbody = getMergedDescription($bodydata,$sourceModuleId[1],$sourceModuleSetype);
				}else{
					if($moduleRecordID){
						$msgbody = getMergedDescription($bodydata,$moduleRecordID,$setype);
					}else{
						$msgbody = $bodydata;
					}
				}
			}
			
			if($messageBody){
				$attachmentData = CTWhatsApp_Record_Model::getAttachmentData($message);
				$imageId = $attachmentData['imageId'];
				$imagePath = $attachmentData['imagePath'];
				$imageName = $attachmentData['imageName'];
				$attachmentPath = $root_directory.$imagePath.$imageId.'_'.$imageName;
				
				$whatsappFolderPath = "/modules/CTWhatsApp/CTWhatsAppStorage/";
				$year  = date('Y');
				$month = date('F');
				$day   = date('j');
				$week  = '';
				if (!is_dir($root_directory.$whatsappFolderPath)) {
					//create new folder
					mkdir($root_directory.$whatsappFolderPath);
					chmod($root_directory.$whatsappFolderPath, 0777);
				}

				if (!is_dir($root_directory.$whatsappFolderPath . $year)) {
					//create new folder
					mkdir($root_directory.$whatsappFolderPath . $year);
					chmod($root_directory.$whatsappFolderPath . $year, 0777);
				}

				if (!is_dir($root_directory.$whatsappFolderPath . $year . "/" . $month)) {
					//create new folder
					mkdir($root_directory.$whatsappFolderPath . "$year/$month/");
					chmod($root_directory.$whatsappFolderPath . "$year/$month/", 0777);
				}

				if ($day > 0 && $day <= 7)
					$week = 'week1';
				elseif ($day > 7 && $day <= 14)
					$week = 'week2';
				elseif ($day > 14 && $day <= 21)
					$week = 'week3';
				elseif ($day > 21 && $day <= 28)
					$week = 'week4';
				else
					$week = 'week5';	
					
				if (!is_dir($root_directory.$whatsappFolderPath . $year . "/" . $month . "/" . $week)) {
						//create new folder
						mkdir($root_directory.$whatsappFolderPath . "$year/$month/$week/");
						chmod($root_directory.$whatsappFolderPath . "$year/$month/$week/", 0777);
				}
				$newfile = $root_directory.$whatsappFolderPath . "$year/$month/$week/".$imageName;
				copy($attachmentPath, $newfile);
				$newfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$imageName;
				
				if($wptemplateImage){
					$url = $api_url.'/sendfileurl';
					$postfields = array(
						'number' => $tonumbersValue,
						'url' => $newfileURL,
						'filetype' => 'imageMessage',
						'caption' => htmlspecialchars_decode($msgbody, ENT_QUOTES)
					);
				}else{
					$url = $api_url.'/send';
					$postfields = array(
						"number" => $tonumbersValue,
					    "msg" => $msgbody
					);
				}
				if($postfields['caption']){
					$count = 2;
				}else{
					$count = 1;
				}
				if($whatsappStatus == 1){
					if(strtotime($date) > strtotime($currentdate)){
						$checkNumberWhatsAppQuery = CTWhatsApp_Record_Model::getWhatsAppLogData($tonumbersValue, $moduleRecordID, $whatsappScanNo);
        				$numberactive = $checkNumberWhatsAppQuery['numberactive'];
        				if($numberactive != 1){
							$whatsappactiveURL = $api_url.'/isRegisteredUser';
					        $postfieldWhatsAppnumber = array('number' => $tonumbersValue);
					        $whatsAppActiveInActive = CTWhatsApp_WhatsappChat_View::callCURL($whatsappactiveURL, $postfieldWhatsAppnumber, $auth_token);
					        $whatsAppNumberActiveInActive = $whatsAppActiveInActive['value'];
        				}

						$whatsappLogQuery = CTWhatsApp_Record_Model::getWhatsAppLogData($tonumbersValue, $moduleRecordID, $whatsappScanNo);
						$whatsapplogRows = $whatsappLogQuery['rows'];
						if($whatsapplogRows == 0){
							$recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppLog');
							$recordModel->set('whatsapplog_sendername', $current_user->first_name.' '.$current_user->last_name);
							$recordModel->set('messagelog_type', 'Send');
							if($i == 0){
								$recordModel->set('messagelog_body', $msgbody);
							}else{
								$recordModel->set('messagelog_body', $newfileURL);
							}
							if (strpos($tonumbersValue, '@g.us') !== false) {
								$recordModel->set('whatsapplog_msgid', $tonumbersValue);
								$recordModel->set('whatsapplog_contactid', '');
								$recordModel->set('whatsapplog_withccode', 'Groups');
							}else{
								$recordModel->set('whatsapplog_msgid', $val['key']['id']);
								$recordModel->set('whatsapplog_contactid', $moduleRecordID);
								$recordModel->set('whatsapplog_withccode', $tonumbersValue);
							}

							if($displayname){
								$recordModel->set('whatsapplog_displayname', $displayname);
							}else{
								$recordModel->set('whatsapplog_displayname', $tonumbersValue);
							}
							$recordModel->set('whatsapplog_unreadread', 'Unread');
							$recordModel->set('whatsapplog_your_number', $whatsappScanNo);
							$recordModel->set('whatsapplog_important', $getnumberImportant);
							$recordModel->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
							$recordModel->set('assigned_user_id', $ownerid);
							$requestParam = $whatsappactiveURL.' ';
							$requestParam .= json_encode($postfieldWhatsAppnumber);
							$recordModel->set('whatsapplog_request', $requestParam);
							$recordModel->set('whatsapplog_response', json_encode($whatsAppActiveInActive));
							if($whatsAppNumberActiveInActive == 1){
		                        $recordModel->set('whatsapp_numberactive', 1);   
		                    }else{
		                        $recordModel->set('whatsapp_numberactive', 0);
		                    }
							if($getWhatsappAccount->type == 'free' && $oneDayMessages < '100'){
								$recordModel->save();
							}else if($getWhatsappAccount->type == 'premium'){
								$recordModel->save();
							}
							$whatsAppLogId = $recordModel->getId();
						}else{
		                    $whatsapplogid = $whatsappLogQuery['whatsapplogid'];
		                    $recordModel = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppLog');
		                    $recordModel->set('mode', 'edit');
		                    $recordModel->set('id', $whatsapplogid);
		                    $recordModel->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
	                    	$recordModel->set('messagelog_body', $msgbody);
	                    	if($whatsAppNumberActiveInActive == 1){
		                        $recordModel->set('whatsapp_numberactive', 1);   
		                    }else{
		                        $recordModel->set('whatsapp_numberactive', 0);
		                    }
		                    $recordModel->set('assigned_user_id', $ownerid);
		                    $recordModel->save();

		                    $whatsAppLogId = $recordModel->getId();
		                }
		                
						for ($i=0; $i < $count; $i++) { 
							$recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
							$recordModel->set('whatsapp_sendername', $current_user->first_name.' '.$current_user->last_name);
							//$recordModel->set('whatsapp_chatid', $val['message']);
							$recordModel->set('message_type', 'Send');
							if($i == 0){
								$recordModel->set('message_body', $msgbody);
							}else{
								$recordModel->set('message_body', $newfileURL);
							}
							if (strpos($tonumbersValue, '@g.us') !== false) {
								$recordModel->set('whatsapp_contactid', '');
								$recordModel->set('whatsapp_withccode', 'Groups');
							}else{
								$recordModel->set('whatsapp_contactid', $moduleRecordID);
								$recordModel->set('whatsapp_withccode', $tonumbersValue);
							}

							if($displayname){
								$recordModel->set('whatsapp_displayname', $displayname);
							}else{
								$recordModel->set('whatsapp_displayname', $tonumbersValue);
							}
							$recordModel->set('whatsapp_unreadread', 'Unread');
							$recordModel->set('whatsapp_fromno', $whatsappScanNo);
							$recordModel->set('your_number', $whatsappScanNo);
							$recordModel->set('whatsapp_important', $getnumberImportant);
							$recordModel->set('whatsapp_datetime', $adb->formatDate($date_var, true));
							$recordModel->set('assigned_user_id', $ownerid);
							$requestParam = $url.' ';
							$requestParam .= json_encode($postfields);
							$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationDataWithId();
							$whatsapplog = $configurationData['whatsapplog'];
							if($whatsapplog == 1){
								$recordModel->set('whatsapp_request', $requestParam);
							}

							if($getWhatsappAccount->type == 'free' && $oneDayMessages < '100'){
								$recordModel->save();
							}else if($getWhatsappAccount->type == 'premium'){
								$recordModel->save();
							}
							$whatsAppModuleId = $recordModel->getId();
						}
						$val = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $auth_token);	

						$updateWhatsAppLogMessageId = CTWhatsApp_Record_Model::updateWhatsAppMessageId('WhatsAppLog', $whatsAppLogId, $val, $whatsapplog, $tonumbersValue, $whatsappModule);

						$updateWhatsAppMessageId = CTWhatsApp_Record_Model::updateWhatsAppMessageId('CTWhatsApp', $whatsAppModuleId, $val, $whatsapplog, $tonumbersValue, $whatsappModule);
						
					}
				}
			}
		}
	}

	private function updateSettings() {
        $adb = PearDatabase::getInstance();
		
		$getentity =$adb->pquery("SELECT * FROM vtiger_ws_entity WHERE name = ?", array('CTWhatsApp'));
		if($adb->num_rows($getentity) != 1){
			$seq = $adb->pquery("SELECT * FROM vtiger_ws_entity_seq", array());
			$id = $adb->query_result($seq, 0, 'id');
			$seq = $id + 1;
			$adb->pquery("INSERT INTO vtiger_ws_entity (id, name, handler_path, handler_class, ismodule) VALUES ($seq, 'CTWhatsApp', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1')");
			$adb->pquery("UPDATE vtiger_ws_entity_seq SET id = ?",$seq);
		}

		$getCronTask = $adb->pquery("SELECT * FROM vtiger_cron_task ORDER BY id DESC");
		$idCronTask = $adb->query_result($getCronTask, 0, 'id');
		$seqCronTask = $idCronTask + 1;
		$adb->pquery("INSERT INTO vtiger_cron_task (id, name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ($seqCronTask, 'CTWhatsApp', 'cron/modules/CTWhatsApp/SendWhatsappMsg.service', '900', NULL, NULL, '0', 'CTWhatsApp', NULL, NULL)");

		$getCronTasks = $adb->pquery("SELECT * FROM vtiger_cron_task ORDER BY id DESC");
		$idCronTasks = $adb->query_result($getCronTasks, 0, 'id');
		$seqCronTasks = $idCronTasks + 1;
		$adb->pquery("INSERT INTO vtiger_cron_task (id, name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ($seqCronTasks, 'CTWhatsApp Message History	', 'cron/modules/CTWhatsApp/WhatsappMsgHistory.service', '900', NULL, NULL, '0', 'CTWhatsApp', NULL, NULL)");
		
        $linkto = 'index.php?module=CTWhatsApp&parent=Settings&view=WhatsAppUserList';
        $result1=$adb->pquery('SELECT 1 FROM vtiger_settings_field WHERE name=?',array('WhatsApp Configuration'));
        if($adb->num_rows($result1)){
            $adb->pquery('UPDATE vtiger_settings_field SET name=?, iconpath=?, description=?, linkto=? WHERE name=?',array('WhatsApp Configuration', '', '', $linkto, 'WhatsApp Configuration'));
        }else{
            $fieldid = $adb->getUniqueID('vtiger_settings_field');
            $blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
            $seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid));
            if ($adb->num_rows($seq_res) > 0) {
                    $cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
                    if ($cur_seq != null)   $seq = $cur_seq + 1;
            }
            $adb->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence) VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'WhatsApp Configuration' , '', '', $linkto, $seq));
        }

    }

    static function installWorkflow() {
        global $adb;
        $name='SendWhatsAppMsg';
        $dest1 = "modules/com_vtiger_workflow/tasks/".$name.".inc";
        $source1 = "modules/CTWhatsApp/workflow/".$name.".inc";

        if (file_exists($dest1)) {
            $file_exist1 = true;
        } else {
            if(copy($source1, $dest1)) {
                $file_exist1 = true;
            }
        }

        $dest2 = "layouts/v7/modules/Settings/Workflows/Tasks/".$name.".tpl";
        $source2 = "layouts/v7/modules/CTWhatsApp/taskforms/".$name.".tpl";

        if (file_exists($dest2)) {
            $file_exist2 = true;
        } else {
            if(copy($source2, $dest2)) {
                $file_exist2 = true;
            }
        }
		
        if ($file_exist1 && $file_exist2) {
            $sql1 = "SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?";
            $result1 = $adb->pquery($sql1,array($name));

            if ($adb->num_rows($result1) == 0) {
                // Add workflow task
                $taskType = array("name"=>"SendWhatsAppMsg", "label"=>"Send Message on WhatsApp", "classname"=>"SendWhatsAppMsg", "classpath"=>"modules/CTWhatsApp/workflow/SendWhatsAppMsg.inc", "templatepath"=>"modules/CTWhatsApp/taskforms/SendWhatsAppMsg.tpl", "modules"=>array('include' => array(), 'exclude'=>array()), "sourcemodule"=>'CTWhatsApp');
                VTTaskType::registerTaskType($taskType);
            }
        }
    }
}

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

class CTWhatsAppBusiness extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_ctwhatsappbusiness'; 
	var $table_index= 'ctwhatsappid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ctwhatsappbusinesscf', 'ctwhatsappid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ctwhatsappbusiness', 'vtiger_ctwhatsappbusinesscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ctwhatsappbusiness' => 'ctwhatsappid',
		'vtiger_ctwhatsappbusinesscf'=>'ctwhatsappid');

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
		require_once("modules/CTWhatsAppBusiness/WhatsappRelatedModule.php");
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
		$getLicenseDetail = CTWhatsAppBusiness_Record_Model::getWhatsAppLicenseDetail();
		$expiryDate = $getLicenseDetail['expiryDate'];
		$licenseKey = $getLicenseDetail['licenseKey'];
		$date = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::encrypt_decrypt($expiryDate, $action='d');

		$getWhatsappAccount = CTWhatsAppBusiness_Record_Model::getWhatsappAccountDetail($licenseKey);
		$oneDayMessages = CTWhatsAppBusiness_Record_Model::getOneDaysMessages();

		if(is_numeric($message)){
			$getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinesstemplates 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
                WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid = ?", array($message));
            $isTemplates = $adb->num_rows($getWhatsappTemplateQuery);

            if($isTemplates){
                $messageBody = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_text');
                $imageId = $adb->query_result($getWhatsappTemplateQuery, 0, 'attachmentsid');
                $imagePath = $adb->query_result($getWhatsappTemplateQuery, 0, 'path');
                $imageName = $adb->query_result($getWhatsappTemplateQuery, 0, 'name');
                $filetype = $adb->query_result($getWhatsappTemplateQuery, 0, 'type');
                $attachmentPath = $site_URL.$imagePath.$imageId.'_'.$imageName;
                $wptemplate_status = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_status');
                $wptemplate_language = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_language');
                $wptemplate_title = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_title');
           	}else{
           		$getWhatsappTemplateData = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinesstemplates 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                    WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid = ?", array($message));
                $templatesRows = $adb->num_rows($getWhatsappTemplateData);
                $messageBody = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_text');
				$wptemplate_title = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_title');
				$wptemplate_language = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_language');
				$wptemplate_status = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_status');
           	}
		}else{
			$messageBody = $message;
		}
		
		foreach($tonumbers as $tonumbersKey => $tonumbersValue) {
			if($tonumbersValue){
				$relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($tonumbersValue,-9));
				$moduleRecordID = $relatedToData['relatedTo'];	
				$displayname = $relatedToData['displayname'];	
			}

			if($tonumbersValue){
				$getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($tonumbersValue);
			}

			$setype = VtigerCRMObject::getSEType($moduleRecordID);
			if($setype == 'CTWhatsAppBusiness'){
				$recordModelWhatsApp = Vtiger_Record_Model::getInstanceById($moduleRecordID, 'CTWhatsAppBusiness');
				$moduleRecordID = $recordModelWhatsApp->get('whatsapp_contactid');

				if($moduleRecordID == ''){
					$ehatsappRecordNumber = preg_replace('/[^A-Za-z0-9]/', '', $tonumbersValue);
					$relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId($ehatsappRecordNumber);
					$moduleRecordID = $relatedToData['relatedTo'];
					$displayname = $relatedToData['displayname'];
				}
			}

			$getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($sendMessagewpnumber));
			$whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
			
			$api_url = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
			$auth_token = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
			$customfield1 = $adb->query_result($getConfigurationDataQuery, 0, 'customfield1');
			$whatsappScanNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappno');
			$whatsappBusinessNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_businessnumber');
			$whatsappStatus = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappstatus');
			$configureUserid = $adb->query_result($getConfigurationDataQuery, 0, 'configureUserid');
			$whatsappAppid = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_appid');
        	$whatsappAccountid = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_accountid');
		

			if (strpos($tonumbersValue, '@g.us') !== false) {
				$tonumbersValue = $tonumbersValue;
			}else{
				$mobileno = preg_replace('/[^A-Za-z0-9]/', '', $tonumbersValue);
				$tonumbersValue = CTWhatsAppBusiness_Module_Model::getMobileNumber($mobileno, $customfield1);
			}

			$bodydata = str_replace('\r\n',' ',html_entity_decode($messageBody));
			if($setype == 'CTWhatsAppBusiness'){
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

			if($imageId){
				if($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/png'){
                    $sendMessagetype = "image";
                }else{
                    $sendMessagetype = "document";
                }
                $sendMessageUrl = $api_url.$whatsappBusinessNo.'/messages';
                if($wptemplate_status == 1){
					$language = array("code" => $wptemplate_language);
					$postfields = [
	                           "messaging_product" => "whatsapp", 
	                           "recipient_type" => "individual", 
	                           "to" => $tonumbersValue, 
	                           "type" => "template", 
	                           "template" => [
	                                 "name" => $wptemplate_title, 
	                                 "language" => [
	                                    "code" => $wptemplate_language 
	                                 ], 
	                                 "components" => [
	                                       [
	                                          "type" => "header", 
	                                          "parameters" => [
	                                             [
	                                                "type" => "image", 
	                                                "image" => [
	                                                   "link" => $attachmentPath
	                                                ] 
	                                             ] 
	                                          ] 
	                                       ] 
	                                    ] 
	                              ] 
	                        ];
				}else{
	                $postfields = array('messaging_product' => "whatsapp",
                    'recipient_type' => "individual",
                    'to' => $mobileno,
                    'type' => $sendMessagetype,
                        $sendMessagetype => array('link' => $attachmentPath ,'caption' => htmlspecialchars_decode($msgbody, ENT_QUOTES)),
                    );
				}
			}else{
				$sendMessageUrl = $api_url.$whatsappBusinessNo.'/messages';
				if($wptemplate_status == 1){
					$language = array("code" => $wptemplate_language);
					$postfields = array('messaging_product' => "whatsapp",
			                                'to' => $tonumbersValue,
			                                'type' => "template",
			                                'template' => array('name' => $wptemplate_title, 
			                                                    'language' => $language),
			                                );
				}else{
					$postfields = array('messaging_product' => "whatsapp",
										'recipient_type' => "individual",
										'to' => $tonumbersValue,
										'type' => "text",
										'text' => array('preview_url' => false, 
														'body' => htmlspecialchars_decode($msgbody, ENT_QUOTES)),
										);
				}
			}

			if($whatsappStatus == 1){
				if(strtotime($date) > strtotime($currentdate)){
					$checkNumberWhatsAppQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppLogData($tonumbersValue, $moduleRecordID, $whatsappScanNo);
    				$checkNumberWhatsAppRows = $checkNumberWhatsAppQuery['rows'];
			        if($checkNumberWhatsAppRows == 0){
			            $sendmessageUrl = $api_url.$whatsappBusinessNo.'/messages';
						$language = array("code" => $wptemplate_language);
			            $postfieldsData = array('messaging_product' => "whatsapp",
			                                'to' => $tonumbersValue,
			                                'type' => "template",
			                                'template' => array('name' => $wptemplate_title, 
			                                                    'language' => $language),
			                                );
			            CTWhatsAppBusiness_WhatsappChat_View::callCURL($sendmessageUrl, $postfieldsData, $auth_token);
			        }

					$whatsappLogQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppLogData($tonumbersValue, $moduleRecordID, $whatsappScanNo);
					$whatsapplogRows = $whatsappLogQuery['rows'];
					if($whatsapplogRows == 0){
						$recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppBusinessLog');
						$recordModel->set('whatsapplog_sendername', $current_user->first_name.' '.$current_user->last_name);
						$recordModel->set('messagelog_type', 'Send');
						if($i == 0){
							$recordModel->set('messagelog_body', $msgbody);
						}else{
							$recordModel->set('messagelog_body', $newfileURL);
						}
						$recordModel->set('whatsapplog_contactid', $moduleRecordID);
						$recordModel->set('whatsapplog_withccode', $tonumbersValue);

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
						
						if($getWhatsappAccount->type == 'free' && $oneDayMessages < '100'){
							$recordModel->save();
						}else if($getWhatsappAccount->type == 'premium'){
							$recordModel->save();
						}
						$whatsAppLogId = $recordModel->getId();
					}else{
	                    $whatsapplogid = $whatsappLogQuery['whatsappbusinesslogid'];
	                    $recordModel = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppBusinessLog');
	                    $recordModel->set('mode', 'edit');
	                    $recordModel->set('id', $whatsapplogid);
	                    $recordModel->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
                    	$recordModel->set('messagelog_body', $msgbody);
                    	$recordModel->set('assigned_user_id', $ownerid);
	                    $recordModel->save();

	                    $whatsAppLogId = $recordModel->getId();
	                }
	                
					
					$recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
					$recordModel->set('whatsapp_sendername', $current_user->first_name.' '.$current_user->last_name);
					//$recordModel->set('whatsapp_chatid', $val['message']);
					$recordModel->set('message_type', 'Send');
					$recordModel->set('message_body', $msgbody);
					$recordModel->set('whatsapp_contactid', $moduleRecordID);
					$recordModel->set('whatsapp_withccode', $tonumbersValue);

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
					$requestParam = $sendmessageUrl.' ';
					$requestParam .= json_encode($postfields);
					$configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
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
					$val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($sendMessageUrl, $postfields, $auth_token);
					
					$updateWhatsAppLogMessageId = CTWhatsAppBusiness_Record_Model::updateWhatsAppMessageId('WhatsAppBusinessLog', $whatsAppLogId, $val, $whatsapplog, $tonumbersValue, $whatsappModule);

					$updateWhatsAppMessageId = CTWhatsAppBusiness_Record_Model::updateWhatsAppMessageId('CTWhatsAppBusiness', $whatsAppModuleId, $val, $whatsapplog, $tonumbersValue, $whatsappModule);
					
				}
			}
		
		}
	}

	private function updateSettings() {
        $adb = PearDatabase::getInstance();
		
		$getentity =$adb->pquery("SELECT * FROM vtiger_ws_entity WHERE name = ?", array('CTWhatsAppBusiness'));
		if($adb->num_rows($getentity) != 1){
			$seq = $adb->pquery("SELECT * FROM vtiger_ws_entity_seq", array());
			$id = $adb->query_result($seq, 0, 'id');
			$seq = $id + 1;
			$adb->pquery("INSERT INTO vtiger_ws_entity (id, name, handler_path, handler_class, ismodule) VALUES ($seq, 'CTWhatsAppBusiness', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1')");
			$adb->pquery("UPDATE vtiger_ws_entity_seq SET id = ?",$seq);
		}

		$getCronTask = $adb->pquery("SELECT * FROM vtiger_cron_task ORDER BY id DESC");
		$idCronTask = $adb->query_result($getCronTask, 0, 'id');
		$seqCronTask = $idCronTask + 1;
		$adb->pquery("INSERT INTO vtiger_cron_task (id, name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ($seqCronTask, 'CTWhatsAppBusiness', 'cron/modules/CTWhatsAppBusiness/SendWhatsappMsg.service', '900', NULL, NULL, '0', 'CTWhatsAppBusiness', NULL, NULL)");

		$getCronTasks = $adb->pquery("SELECT * FROM vtiger_cron_task ORDER BY id DESC");
		$idCronTasks = $adb->query_result($getCronTasks, 0, 'id');
		$seqCronTasks = $idCronTasks + 1;
		$adb->pquery("INSERT INTO vtiger_cron_task (id, name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ($seqCronTasks, 'CTWhatsAppBusiness Message History	', 'cron/modules/CTWhatsAppBusiness/WhatsappMsgHistory.service', '900', NULL, NULL, '0', 'CTWhatsAppBusiness', NULL, NULL)");
		
        $linkto = 'index.php?module=CTWhatsAppBusiness&parent=Settings&view=WhatsAppUserList';
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
        $name='SendWhatsAppMsgBusiness';
        $dest1 = "modules/com_vtiger_workflow/tasks/".$name.".inc";
        $source1 = "modules/CTWhatsAppBusiness/workflow/".$name.".inc";

        if (file_exists($dest1)) {
            $file_exist1 = true;
        } else {
            if(copy($source1, $dest1)) {
                $file_exist1 = true;
            }
        }

        $dest2 = "layouts/v7/modules/Settings/Workflows/Tasks/".$name.".tpl";
        $source2 = "layouts/v7/modules/CTWhatsAppBusiness/taskforms/".$name.".tpl";

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
                $taskType = array("name"=>"SendWhatsAppBusinessMsg", "label"=>"Send Message on Whatsapp Business", "classname"=>"SendWhatsAppMsg", "classpath"=>"modules/CTWhatsAppBusiness/workflow/SendWhatsAppMsgBusiness.inc", "templatepath"=>"modules/CTWhatsAppBusiness/taskforms/SendWhatsAppMsgBusiness.tpl", "modules"=>array('include' => array(), 'exclude'=>array()), "sourcemodule"=>'CTWhatsAppBusiness');
                VTTaskType::registerTaskType($taskType);
            }
        }
    }
}

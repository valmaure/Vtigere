<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/
header('Content-Type: text/html; charset=utf-8');
class CTWhatsApp_WhatsappChat_View extends Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('getWhatsappIcon');
		$this->exposeMethod('allWhatsAppMSG');
		$this->exposeMethod('getModulesData');
		$this->exposeMethod('sendMSGOnWhatsapp');
		$this->exposeMethod('getRecordMessageDetails');
		$this->exposeMethod('importantMessage');
		$this->exposeMethod('getNewUnreadMessages');
		$this->exposeMethod('getSearchRecord');
		$this->exposeMethod('saveComments');
		$this->exposeMethod('scanQRCodeInPopup');
		$this->exposeMethod('getUnreadMessages');
		$this->exposeMethod('updateMessageWithRecordID');
		$this->exposeMethod('getWhatsappTemplates');
		$this->exposeMethod('getWhatsappTemplatesData');
		$this->exposeMethod('getContactLoadHistory');
		$this->exposeMethod('saveContactLoadHistory');
		$this->exposeMethod('newNumberSendMessagePopup');
		$this->exposeMethod('sendNumberSendMessage');
		$this->exposeMethod('refreshAllMessages');
		$this->exposeMethod('autoResponderPopup');
		$this->exposeMethod('updateAutoResponderMessage');
		$this->exposeMethod('getWhatsAppGroup');
		$this->exposeMethod('getAllContactLoadHistory');
		$this->exposeMethod('saveAllContactLoadHistory');
		$this->exposeMethod('getAllContactLoadHistoryStatus');
		$this->exposeMethod('getWhatsappMessageInRelatedTab');
		$this->exposeMethod('getWhatsAppGroupMessages');
		$this->exposeMethod('readAllWhatsAppMessages');
		$this->exposeMethod('addGroupNumberInCRMPopup');
		$this->exposeMethod('addGroupNumberInCRM');
		$this->exposeMethod('getSourceModuleField');
		$this->exposeMethod('getNewLoadWhatsAppGroup');
		$this->exposeMethod('getWhatsAppGroupMembers');
		$this->exposeMethod('userBeepOn');
		$this->exposeMethod('userBeepOff');
	}

	function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        if(!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if(!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
        return;
    }

    function allWhatsAppMSG(Vtiger_Request $request) {
		global $adb, $current_user, $site_URL;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$isAdmin = $current_user->is_admin;
		$scanscanQRCodeURL = CTWhatsApp_Record_Model::getScanQRCodeURL();
		$currenUserID = $current_user->id;

		$mainUserDetail = Settings_CTWhatsApp_Record_Model::getMainUserDetails($currenUserID);
		$mainUserWhatsapprows = $mainUserDetail['rows'];
		$mainUserWhatsappStatus = $mainUserDetail['whatsappstatus'];

		$configuratioData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
		$whatsappStatus = $configuratioData['whatsappstatus'];
		$scanWhatsAppNumber = $configuratioData['whatsappno'];
		
		$multipleWhatsappNumber = CTWhatsApp_Record_Model::getAllConnectedWhatsappNumber($currenUserID);
		foreach ($multipleWhatsappNumber as $key => $value) {
			if($value['whatsappstatus'] == 2){
				$noInternetNumber = $value['whatsappno'];
				break;
			}
		}

		$allUserNumber = CTWhatsApp_Record_Model::getAllUserWhatsappNumber($currenUserID);

		$admminScanDetail = CTWhatsApp_Record_Model::getAdmminScanDetail();
		$whatsappno = $admminScanDetail['whatsappno'];
		$showunknownmsg = $admminScanDetail['showunknownmsg'];
		$api_url = $admminScanDetail['api_url'];
		$whatsappUserManagemnt = $admminScanDetail['whatsappUserManagemnt'];
		
		$whatsaappModule = CTWhatsApp_Record_Model::getWhatsappAllowModules();
		$totalAllowModule = count($whatsaappModule);

		$themeView = CTWhatsApp_Record_Model::getWhatsappTheme();

		$importantMessagesCounts = CTWhatsApp_Record_Model::getImportantMessagesCounts();
		$messagesCounts = CTWhatsApp_Record_Model::getNewMessagesCounts();
		$newMessagesCounts = $messagesCounts['allRows'];
		$newMessageCounts = $messagesCounts['rows'];

		$unknownMessagesCount = CTWhatsApp_Record_Model::getUnknownMessagesCounts();
		$unknownMessagesCounts = $unknownMessagesCount['allUnknownRows'];
		$unknownRows = $unknownMessagesCount['unknownRows'];

		$allMessagesCountData = CTWhatsApp_Record_Model::getAllMessagesCounts();
		$allMessagesCounts = $allMessagesCountData['allRows'];
		$allMessageCounts = $allMessagesCountData['rows'];

		$unreadGroupMessagesCount = CTWhatsApp_Record_Model::unreadGroupMessages();

		$oneDayaMessages = CTWhatsApp_Record_Model::getOneDaysMessages();

		$year  = date('Y');
		$month = date('F');
		$day   = date('j');
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

		$whatsappFolderPath = "modules/CTWhatsApp/CTWhatsAppStorage/";
		$storageURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/";

		$getLicenseDetail = CTWhatsApp_Record_Model::getWhatsAppLicenseDetail();
		$licenseKey = $getLicenseDetail['licenseKey'];
		$getWhatsappAccount = CTWhatsApp_Record_Model::getWhatsappAccountDetail($licenseKey);

		$wpTemplates = CTWhatsApp_Record_Model::getWhatsappTemplatesScannumber($WhatsappNumber);
		
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('WHATSAPPMODULES', $whatsaappModule);
		$viewer->assign('TOTALALLOWMODULE', $totalAllowModule);
		$viewer->assign('ALLMESSAGESCOUNTS', $allMessagesCounts);
		$viewer->assign('ALLMESSAGESCOUNT', $allMessageCounts);
		$viewer->assign('WHATSAPPSTATUS', $whatsappStatus);
		$viewer->assign('IMPORTANTMESSAGECOUNTS', $importantMessagesCounts);
		$viewer->assign('NEWMESSAGESCOUNTS', $newMessagesCounts);
		$viewer->assign('NEWMESSAGECOUNTS', $newMessageCounts);
		$viewer->assign('ALLUNKNOWNMESSAGECOUNTS', $unknownMessagesCounts);
		$viewer->assign('UNKNOWNMESSAGECOUNTS', $unknownRows);
		$viewer->assign('WHATSAPPSTORAGEURL', $storageURL);
		$viewer->assign('SCANQRCODE', $scanscanQRCodeURL);
		$viewer->assign('ISADMIN', $isAdmin);
		$viewer->assign('ROW', $num_row);
		$viewer->assign('SHOWUNKOWNMESSAGES', $showunknownmsg);
		$viewer->assign('ROWS', $row);
		$viewer->assign('ONEDAYMESSAGE', $oneDayaMessages);
		$viewer->assign('APIURL', $api_url);
		$viewer->assign('WHATSAPPMODULE', $getWhatsappAccount);
		$viewer->assign('WHATSAPPUSERMANAGEMENT', $whatsappUserManagemnt);
		$viewer->assign('WHATSAPPNUMBER', $scanWhatsAppNumber);
		$viewer->assign('MAINUSERSTAUS', $mainUserWhatsappStatus);
		$viewer->assign('MAINUSERSTAUSROWS', $mainUserWhatsapprows);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
		$viewer->assign('WHATSAPP_NUMBER', $whatsappno);
		$viewer->assign('NOINTERNETNUMBER', $noInternetNumber);
		$viewer->assign('ALLUSERNUMBER', $allUserNumber);
		$viewer->assign('GROUPUNREADCOUNT', $unreadGroupMessagesCount);
		$viewer->assign('RESPONSE_CUSTOMER', $request->get('customerResponse'));
		$viewer->assign('WHATSAPP_TEMPLATES', $wpTemplates);
		
		if($themeView == 'RTL'){
			echo $viewer->view('AllWhatsAppMSGRTL.tpl', $moduleName, true);
		}else{
			echo $viewer->view('AllWhatsAppMSG.tpl', $moduleName, true);
		}
	}

	function getWhatsappIcon(Vtiger_Request $request){
		$sourceModule = $request->get('sourceModule');
		$whatsappModuleData = CTWhatsApp_Record_Model::getWhatsappIcon($sourceModule);
		
		$response = new Vtiger_Response();
		$response->setResult($whatsappModuleData);
		$response->emit();
	}
	
	function getModulesData(Vtiger_Request $request) {
		$getModuleRecrods = CTWhatsApp_Record_Model::getModuleRecrods($request);
		echo $getModuleRecrods;
	}

	function sendMSGOnWhatsapp(Vtiger_Request $request){
		$sendIndividulMessageDate = CTWhatsApp_Record_Model::sendIndividulMessage($request);
		$currenDatTime = $sendIndividulMessageDate['currenDatTime'];
		$senderName = $sendIndividulMessageDate['senderName'];
		$whatsappid = $sendIndividulMessageDate['whatsappid'];
		$numberactive = $sendIndividulMessageDate['numberactive'];
		
		$response = new Vtiger_Response();
		$response->setResult(array('sendMessage' => true, 'currenDatTime' => $currenDatTime, 'whatsappid' => $whatsappid, 'numberactive' => $numberactive));
		$response->emit();
	}

	public function callCURL($url, $postfields, $auth_token){
        foreach($postfields as $key=>$value) {
			$fieldsString .= $key.'='.$value.'&';
		}
		rtrim($fieldsString, '&');
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 50,
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
			CURLOPT_POSTFIELDS => json_encode($postfields),
			CURLOPT_HTTPHEADER => array(
		    	'Authorization: '.$auth_token,
		    	'Content-Type: application/json'
		  	),
		));
		$result = curl_exec($curl);
		$response = json_decode($result, true);
		curl_close($curl);
		return $response;
	}

	public function getRecordMessageDetails(Vtiger_Request $request){
		global $adb, $site_URL, $current_user, $root_directory;
		$moduleName = $request->getModule();
		$recordId = $request->get('recordid');
		$whatsappModule = $request->get('whatsappmodule');
		$groupid = $request->get('groupid');
		$groupWhatsappNumber = $request->get('groupWhatsappNumber');
		$setype = VtigerCRMObject::getSEType($recordId);
		
		if($setype){
			$adb->pquery("UPDATE vtiger_whatsapplog SET whatsapplog_unreadread = 'Read' WHERE whatsapplog_contactid = ?", array($recordId));
			
			$recordData = CTWhatsApp_Record_Model::getModuleRecordData($recordId, $setype, $whatsappModule, $groupid, $groupWhatsappNumber);
		}else{
			if($groupid){
				$adb->pquery("UPDATE vtiger_whatsapplog SET whatsapplog_unreadread = 'Read' WHERE whatsapplog_msgid = ?", array($groupid));
			}else{
				$adb->pquery("UPDATE vtiger_whatsapplog SET whatsapplog_unreadread = 'Read' WHERE whatsapplog_withccode = ?", array($recordId));
			}

			$recordData = CTWhatsApp_Record_Model::getMessagesRecordData($recordId, $whatsappModule, $groupid, $groupWhatsappNumber);
		}

		$response = new Vtiger_Response();
		$response->setResult($recordData);
		$response->emit();
	}

	public function importantMessage(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$recordId = $request->get('recordId');
		$messagesImportant = $request->get('messagesImportant');
		$recordData = CTWhatsApp_Record_Model::setMessagesImportant($recordId, $messagesImportant);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}

	public function getNewUnreadMessages(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$recordId = $request->get('recordId');
		$individulMessage = $request->get('individulMessage');
		$lastMessageID = $request->get('lastMessageID');
		$getNewUnreadMessagesHTML = CTWhatsApp_Record_Model::getAllNewUnreadMessages($recordId, $moduleName, $individulMessage, $lastMessageID);

		$response = new Vtiger_Response();
		$response->setResult($getNewUnreadMessagesHTML);
		$response->emit();
	}

	public function getSearchRecord(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$searchRecords = CTWhatsApp_Record_Model::getModuleRecrods($request);
		echo $searchRecords;
	}

	public function saveComments(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$recordId = $request->get('recordId');
		$commentText = $request->get('commentText');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$recordModel = Vtiger_Record_Model::getCleanInstance('ModComments');
		$recordModel->set('mode', '');
		$recordModel->set('commentcontent', decode_html($commentText));
		$recordModel->set('related_to', $recordId);
		$recordModel->set('assigned_user_id', $currentUserModel->getId());
		$recordModel->set('userid', $currentUserModel->getId());
		$recordModel->save();

		$setype = VtigerCRMObject::getSEType($recordId);

		$pagingModel = new Vtiger_Paging_Model();
		$recentComments = ModComments_Record_Model::getRecentComments($recordId, $pagingModel);

		$comments = array();
		$commentHTML = '';
		foreach ($recentComments as $key => $value) {
			if($key < 2){
				$commentcontent = $recentComments[$key]->get('commentcontent');
				$createdtime = $recentComments[$key]->get('createdtime');
				$smownerid = $recentComments[$key]->get('smownerid');

				$commentHTML .= '<div class="comment1">
									<!-- <div class="pic"><img src="layouts/v7/modules/CTWhatsApp/image/pic4.png" /></div> -->
									<div class="pName">
										<div class="pText">
											<span>'.Vtiger_Functions::getUserName($smownerid).'</span>
											<span class="time">'.Vtiger_Util_Helper::formatDateDiffInStrings($createdtime).'</span>
											<p>'.decode_html($commentcontent).'</p>
										</div>
									</div>
								</div>';

			}
			if($key < 3){
				$moreCommentLink = "<span class='pull-right' style='color: blue;'><a href='index.php?module=".$setype."&relatedModule=ModComments&view=Detail&record=".$recordId."&mode=showRelatedList' target='_black'>".vtranslate('LBL_SHOW_MORE','Vtiger')."</a><span>";
			}
		}
		echo $commentHTML.$moreCommentLink;
	}

	/**
     * Function for scan QR Code
     */
    function scanQRCodeInPopup(Vtiger_Request $request) {
    	global $adb, $current_user, $site_URL;
        $moduleName = $request->getModule();
        $userID = $current_user->id;
        $whatsappbot = $request->get('whatsappbot');

        if($whatsappbot == 'yes'){
        	$scanQRCode = CTWhatsApp_Record_Model::createWhatsappUser($whatsappbot);
        }else{
        	$scanQRCode = CTWhatsApp_Record_Model::createWhatsappUser($userID);
        }

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SCANQRCODE', $scanQRCode['qrcodeurl']);
		$viewer->assign('AUTHTOKENKEY', $scanQRCode['authTokenKey']);
		$viewer->assign('SCANWHATSAPPNO', $scanQRCode['whatsappNo']);
		$viewer->assign('SCANMESSAGE', $scanQRCode['scanMessage']);
		$viewer->assign('APIURL', $scanQRCode['apiUrl']);
		if($whatsappbot == 'yes'){
    		echo $viewer->view('WhatsappBotScanQRCode.tpl', $moduleName, true);
    	}else{
    		echo $viewer->view('ScanQRCodeInPopup.tpl', $moduleName, true);
    	}
    }

    //Function for Unread Whatsapp Messages
    function getUnreadMessages(Vtiger_Request $request) {
    	global $adb, $current_user, $site_URL;
        $moduleName = $request->getModule();
        
        $unReadCount = CTWhatsApp_Record_Model::getUnreadMessagesCount();
        echo $unReadCount;
    }
    
    //Function for Update whatsapp message with create record
    function updateMessageWithRecordID(Vtiger_Request $request) {
        CTWhatsApp_Record_Model::updateWhatsappRecords($request);
    }

    /**
     * Function for get Whatsapp Template
     */
    function getWhatsappTemplates(Vtiger_Request $request) {
    	global $adb;
        $moduleName = $request->getModule();
        $more = $request->get('more');

        $wpTemplates = CTWhatsApp_Record_Model::getWhatsappTemplatesScannumber($more);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('WHATSAPPTEMPLATES', $wpTemplates);
    	echo $viewer->view('WPTemplates.tpl', $moduleName, true);
    }

    /**
     * Function for get Whatsapp Template Data
     */
    function getWhatsappTemplatesData(Vtiger_Request $request) {
    	global $adb, $site_URL;
        $moduleName = $request->getModule();
        $wptemplatesid = $request->get('wptemplatesid');
        $moduleRecordid = $request->get('moduleRecordid');
        $wpTemplateRecordModel = Vtiger_Record_Model::getInstanceById($wptemplatesid, 'CTWhatsAppTemplates');
        $msgbody = $wpTemplateRecordModel->get('wptemplate_text');
        $setype = VtigerCRMObject::getSEType($moduleRecordid);
        if($setype){
 	       $msgbody = getMergedDescription($msgbody,$moduleRecordid,$setype);
        }
       if($msgbody == ''){
       	  $imageData = CTWhatsApp_Record_Model::getImageDetails($wptemplatesid, 'CTWhatsAppTemplates');
       	  $msgbody = $site_URL.'/'.$imageData;
       }
        echo $msgbody;
    }

    /**
     * Function for Contact Load History Pop up
     */
    function getContactLoadHistory(Vtiger_Request $request) {
    	global $adb, $current_user;
    	$dateFormat = $current_user->date_format;
        $moduleName = $request->getModule();
        $allHistory = $request->get('allHistory');

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DATEFORMAT', $dateFormat);
		$viewer->assign('ALLHISTORY', $allHistory);
    	echo $viewer->view('ContactLoadHistory.tpl', $moduleName, true);
    }

    /**
     * Function for Contact all Load History Pop up
     */
    function getAllContactLoadHistory(Vtiger_Request $request) {
    	global $adb, $current_user;
    	$dateFormat = $current_user->date_format;
        $moduleName = $request->getModule();
        $currenUserID = $current_user->id;
        $multipleWhatsappNumber = CTWhatsApp_Record_Model::getAllConnectedWhatsappNumber($currenUserID);

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DATEFORMAT', $dateFormat);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
    	echo $viewer->view('ContactAllLoadHistory.tpl', $moduleName, true);
    }

    /**
     * Function for save Contact all Load History
     */
    function saveAllContactLoadHistory(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        
    	CTWhatsApp_Record_Model::saveWhatsAppHistoryData($request);
    }

    /**
     * Function for save Contact all Load History
     */
    function getAllContactLoadHistoryStatus(Vtiger_Request $request) {
        $moduleName = $request->getModule(); 
        
    	$historyDetails = CTWhatsApp_Record_Model::getWhatsApphistoryDetail($request);

    	$result = array('status' => $historyDetails['status'], 'startdate' => $historyDetails['startdate'], 'enddate' => $historyDetails['enddate']);
    	$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }

    /**
     * Function for save Contact Load History
     */
    function saveContactLoadHistory(Vtiger_Request $request) {
    	global $adb, $current_user;
        $moduleName = $request->getModule();
        $phone = $request->get('phone');
        $moduleRecordid = $request->get('module_recordid');
        $pastMessageNumber = $request->get('pastMessageNumber');
        $whatsappNumber = $request->get('whatsappNumber');
        $currenUserID = $current_user->id;

        $getConfigurationData = CTWhatsApp_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);
    	$api_url = $getConfigurationData['api_url'];
		$auth_token = $getConfigurationData['auth_token'];
		$customfield1 = $getConfigurationData['customfield1'];
		$whatsappScanNo = $getConfigurationData['whatsappScanNo'];
		$whatsappStatus = $getConfigurationData['whatsappStatus'];
		$configureUserid = $getConfigurationData['configureUserid'];
    	
    	$phoneno = preg_replace('/[^A-Za-z0-9]/', '', $phone);

		$getHistoryDetail = CTWhatsApp_Record_Model::getWhatsappHistory($phoneno, $whatsappNumber);
		$cursorRows = $getHistoryDetail['cursorRows'];
    	$url = $api_url.'/historypaging';

		if($cursorRows){
			$history_id = $getHistoryDetail['history_id'];
			$history_fromme = $getHistoryDetail['history_fromme'];
			$newremoteJid = $getHistoryDetail['remotjid'];
			if($historyFromme == 0){
				$historyFromme = '';
			}
			$postfields = array(
				"number" => $phoneno,
			    "count" => $pastMessageNumber,
			    "cursor" => array("before" => array("remoteJid" => $newremoteJid, "id" => $history_id,"fromMe" => $historyFromme))
 			);
		}else{
	        $postfields = array(
				"number" => $phoneno,
			    "count" => $pastMessageNumber
			);
		}

		$val = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
		$cursor = $val['cursor']['before']['id'];
        $history_fromme = $val['cursor']['before']['fromMe'];
        $remoteJid = $val['cursor']['before']['remoteJid'];
        
        if($val){
        	$Allmsgs = $val['Allmsgs'];
	        foreach ($Allmsgs as $key => $value) {
	        	$recordModel1 = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
	        	$fromMe = $value['key']['fromMe'];
	        	if($fromMe == 1){
	        		$type = "Send";
					$from = explode('@', $value['key']['remoteJid']);
        			$body = $value['message']['extendedTextMessage']['text'];
        			if($body == ''){
        				$body = $value['message']['conversation'];	
        			}
	        	}else{
	        		$type = "Recieved";
					$from = explode('@', $value['key']['remoteJid']);
					$body = $value['message']['conversation'];
	        	}
	        	$messageid = $value['key']['id'];
	        	$time = $value['messageTimestamp'];
	        	$getMessageDateTime = date("Y-m-d H:i:s",$time);
	        	$checkMessageId = CTWhatsApp_Record_Model::checkMessageId($messageid);
	        	if($checkMessageId == 0){
					$recordModel1->set('message_type', $type);
					$recordModel1->set('message_body', $body);
					$recordModel1->set('whatsapp_contactid', $moduleRecordid);
					if($moduleRecordid){
			            $setype = VtigerCRMObject::getSEType($moduleRecordid);
			            $recordModelData = Vtiger_Record_Model::getInstanceById($moduleRecordid, $setype);
			            $displayname = $recordModelData->get('label');
			            $recordModel1->set('whatsapp_displayname', $displayname);
			        }else{
			            $recordModel1->set('whatsapp_displayname', $sendsmsnumber);
			        }
					$recordModel1->set('whatsapp_unreadread', 'Read');
					$recordModel1->set('whatsapp_withccode', $phoneno);
					$recordModel1->set('whatsapp_sendername', $phoneno);
					$recordModel1->set('whatsapp_fromno', $whatsappScanNo);
					$recordModel1->set('whatsapp_datetime', $getMessageDateTime);
					$recordModel1->set('assigned_user_id', 1);
					$recordModel1->set('your_number', $whatsappScanNo);
					$recordModel1->set('msgid', $messageid);
		        	if($body != ''){
						$recordModel1->save();
					}
				}
	        }
			CTWhatsApp_Record_Model::insertWhatsappHistory($phoneno, $cursor, $history_fromme, $remoteJid, $whatsappScanNo);
		}
    }

    /**
     * Function for Send new message number popup
     */
    function newNumberSendMessagePopup(Vtiger_Request $request) {
    	global $adb, $current_user;
        $moduleName = $request->getModule();
        $currentUserID = $current_user->id;

        $configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($currentUserID);
        $countryCode = $configurationData['customfield1'];
        $whatsappstatus = $configurationData['whatsappstatus'];

        $multipleWhatsappNumber = CTWhatsApp_Record_Model::getAllConnectedWhatsappNumber($currentUserID);
		foreach ($multipleWhatsappNumber as $key => $value) {
			if($value['whatsappstatus'] == 2){
				$noInternetNumber = $value['whatsappno'];
				break;
			}
		}

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('COUNTRYCODE', $countryCode);
		$viewer->assign('WHATSAPPSTATUS', $whatsappstatus);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
		$viewer->assign('NOINTERNETNUMBER', $noInternetNumber);
    	echo $viewer->view('SendMessageNewNumber.tpl', $moduleName, true);
    }

    /**
     * Function for Send new message number
     */
    function sendNumberSendMessage(Vtiger_Request $request) {
    	global $adb, $current_user, $root_directory, $site_URL;
        $moduleName = $request->getModule();
        $newNumber = $request->get('newNumber');
        $newTextMessage = $request->get('newTextMessage');

        $base64imagedata = $request->get('base64imagedata');
		$filename = $request->get('filename');
		$filetype = $request->get('filetype');
		$multiWPNumber = $request->get('multiWPNumber');
		$currenUserID = $current_user->id;

        $configurationData = CTWhatsApp_Record_Model::getWhatsAppDetailWithMobileNo($multiWPNumber);
        $apiUrl = $configurationData['api_url'];
        $authtoken = $configurationData['auth_token'];
        $whatsappScanNo = $configurationData['whatsappScanNo'];
        $customfield1 = $configurationData['customfield1'];
        $whatsappstatus = $configurationData['whatsappStatus'];
        $configureUserid = $configurationData['configureUserid'];

        $mobileno = preg_replace('/[^A-Za-z0-9]/', '', $newNumber);
        $mobilenoLen = strlen($mobileno);
		if($mobilenoLen > 10 && $customfield1 !=''){
			$withoutcode = substr($mobileno,-10);
			$mobileno = $customfield1.$withoutcode;
		}else{
			$mobileno = $customfield1.$mobileno;
		}
		if($filename){
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

			$target_file = $root_directory.$whatsappFolderPath.$year.'/'.$month.'/'.$week.'/';

			list($type, $base64imagedata) = explode(';', $base64imagedata);
			list(, $base64imagedata)      = explode(',', $base64imagedata);
			$base64imagedata = base64_decode($base64imagedata);

			$filemove = file_put_contents($target_file.$filename,$base64imagedata);
			$newFilename = urlencode($filename);
			$newFilename = str_replace('+','%20',$newFilename);
		  	$newFilename = str_replace('_','%5F',$newFilename);
		  	$newFilename = str_replace('.','%2E',$newFilename);
			$newFilename = str_replace('-','%2D',$newFilename); 
			$newfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$newFilename;
			$newBodyfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$filename;

			if($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/png'){
				$url = $apiUrl.'/sendfileurl';
				$postfields = array(
					'number' => $mobileno,
					'url' => $newfileURL,
					'filetype' => 'imageMessage',
					'caption' => $newTextMessage
				);
			}else if($filetype == 'application/pdf'){
				$url = $apiUrl.'/sendfileurl';
				$postfields = array(
					'number' => $mobileno,
					'url' => $newfileURL,
					'mimetype' => 'application/pdf',
					'filetype' => 'documentMessage',
					'caption' => $newTextMessage
				);
			}else{
				$url = $apiUrl.'/sendfileurl';
				$postfields = array(
					'number' => $mobileno,
					'url' => $newfileURL,
					'mimetype' => $filetype,
					'filetype' => 'documentMessage',
					'caption' => $newTextMessage 
				);
			}

		}else{
	        $url = $apiUrl.'/send';
			$postfields = array(
				'number' => $mobileno,
				'msg' => $newTextMessage
			);
		}

		$checkNumberWhatsAppQuery = CTWhatsApp_Record_Model::getWhatsAppLogData($mobileno, $moduleRecordid, $whatsappScanNo);
		$numberactive = $checkNumberWhatsAppQuery['numberactive'];
		if($numberactive != 1){
			$whatsappactiveURL = $apiUrl.'/isRegisteredUser';
	        $postfieldWhatsAppnumber = array('number' => $mobileno);
	        $whatsAppActiveInActive = CTWhatsApp_WhatsappChat_View::callCURL($whatsappactiveURL, $postfieldWhatsAppnumber, $authtoken);
	        $numberactive = $whatsAppActiveInActive['value'];
		}

		if($whatsappstatus == 1){
			
				
			$date_var = date("Y-m-d H:i:s");
			$currentusername = $current_user->first_name.' '.$current_user->last_name;

			$moduleRecordid = '';
			$whatsappLogQuery = CTWhatsApp_Record_Model::getWhatsAppLogData($mobileno, $moduleRecordid, $whatsappScanNo);
            $whatsapplogRows = $whatsappLogQuery['rows'];
            if($whatsapplogRows == 0){
            	$recordModelWPLogs = Vtiger_Record_Model::getCleanInstance('WhatsAppLog');
            	$recordModelWPLogs->set('whatsapplog_sendername', $currentusername);
				$recordModelWPLogs->set('messagelog_type', 'Send');
				if($newfileURL){
					$recordModelWPLogs->set('messagelog_body', $newBodyfileURL);
				}else{
					$recordModelWPLogs->set('messagelog_body', $newTextMessage);
				}
				$recordModelWPLogs->set('whatsapplog_unreadread', 'Unread');
				$recordModelWPLogs->set('whatsapplog_withccode', $mobileno);
				$recordModelWPLogs->set('assigned_user_id', $configureUserid);
				$recordModelWPLogs->set('whatsapplog_msgid', $val['key']['id']);
				$recordModelWPLogs->set('whatsapplog_your_number', $whatsappScanNo);
				$recordModelWPLogs->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
				$requestParam = $whatsappactiveURL.' ';
				$requestParam .= json_encode($postfieldWhatsAppnumber);
				$recordModelWPLogs->set('whatsapplog_request', $requestParam);
				$recordModelWPLogs->set('whatsapplog_response', json_encode($whatsAppActiveInActive));
				if($numberactive == 1){
                    $recordModelWPLogs->set('whatsapp_numberactive', 1);   
                }else{
                    $recordModelWPLogs->set('whatsapp_numberactive', 0);
                }
				$recordModelWPLogs->save();

				$whatsAppLogId = $recordModelWPLogs->getId();
            }else{
            	$whatsapplogid = $whatsappLogQuery['whatsapplogid'];
                $recordModelWPLogs = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppLog');
                $recordModelWPLogs->set('mode', 'edit');
                $recordModelWPLogs->set('id', $whatsapplogid);
                $recordModelWPLogs->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
                if($fileURL){
                    $recordModelWPLogs->set('messagelog_body', $newBodyfileURL);
                }else{
                    $recordModelWPLogs->set('messagelog_body', $newTextMessage);
                }
                if($numberactive == 1){
                    $recordModelWPLogs->set('whatsapp_numberactive', 1);   
                }else{
                    $recordModelWPLogs->set('whatsapp_numberactive', 0);
                }
                $recordModelWPLogs->save();

                $whatsAppLogId = $recordModelWPLogs->getId();
            }

            if($filename){
				$recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
		    	$recordModel->set('whatsapp_sendername', $currentusername);
				$recordModel->set('message_type', 'Send');
				$recordModel->set('message_body', $newBodyfileURL);
				$recordModel->set('whatsapp_unreadread', 'Unread');
				$recordModel->set('whatsapp_withccode', $mobileno);
				$recordModel->set('whatsapp_fromno', $mobileno);
				$recordModel->set('assigned_user_id', $configureUserid);
				$recordModel->set('your_number', $whatsappScanNo);
				$recordModel->set('whatsapp_datetime', $adb->formatDate($date_var, true));
				$recordModel->save();
            }
            
            $recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
	    	$recordModel->set('whatsapp_sendername', $currentusername);
			$recordModel->set('message_type', 'Send');
			$recordModel->set('message_body', $newTextMessage);
			$recordModel->set('whatsapp_unreadread', 'Unread');
			$recordModel->set('whatsapp_withccode', $mobileno);
			$recordModel->set('whatsapp_fromno', $mobileno);
			$recordModel->set('assigned_user_id', $configureUserid);
			$recordModel->set('msgid', $val['key']['id']);
			$recordModel->set('your_number', $whatsappScanNo);
			$recordModel->set('whatsapp_datetime', $adb->formatDate($date_var, true));
			$recordModel->save();

			$whatsAppModuleId = $recordModel->getId();
			$val = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $authtoken);

			$updateWhatsAppLogMessageId = CTWhatsApp_Record_Model::updateWhatsAppMessageId('WhatsAppLog', $whatsAppLogId, $val, $whatsapplog, $tonumbersValue, $whatsappModule);

			$updateWhatsAppMessageId = CTWhatsApp_Record_Model::updateWhatsAppMessageId('CTWhatsApp', $whatsAppModuleId, $val, $whatsapplog, $mobileno, $whatsappModule);
        
			//echo "1";
			$result = array('numberactive' => $numberactive);

		}else{
			//echo "0";
			$result = array('numberactive' => $numberactive);
		}

    	$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();

    }

    /**
     * Function for save Contact Load History
     */
    function refreshAllMessages(Vtiger_Request $request) {
    	global $adb, $current_user;
        $moduleName = $request->getModule();

        $lastMessageDateTime = CTWhatsApp_Record_Model::getlastMessageDateTime();
        $tomorrow = date("Y-m-d H:i:s", strtotime( "+1 days"));
        $startdate = strtotime($lastMessageDateTime);
        $enddate = strtotime($tomorrow);

        $currenUserID = $current_user->id;
        $configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $apiUrl = $configurationData['api_url'];
        $authtoken = $configurationData['authtoken'];
        $whatsappScanNo = $configurationData['whatsappno'];
        
      	$url = $apiUrl.'/Allhistory';
      	$postfields = array(
		    "fromDate" => $startdate,
		    "toDate" => $enddate
		);
		
		$val = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $authtoken);
       	if($val['message']){

       	}else{
	    	foreach ($val as $key => $value) {
	        	$recordModelCTWhatsApp = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
				$body = $value['body'];
				if($body != ''){
	    			$messageid = $value['id']['id'];
	    			$fromTo = explode('@', $value['to']);
	    			$relatedTo = CTWhatsApp_Record_Model::getRelatedToId($fromTo[0]);
	    			$relatedToRecordid = $relatedTo['relatedTo'];
	    			$relatedTosmownerid = $relatedTo['smownerid'];
	    			$fromMe = $value['fromMe'];
		        	if($fromMe == 1){
		        		$type = "Send";
		        	}else{
		        		$type = "Recieved";
		        	}
		        	$time = $value['timestamp'];
		        	$getMessageDateTime = date("Y-m-d H:i:s",$time);

		        	$checkMessageId = CTWhatsApp_Record_Model::checkMessageId($messageid);
		        	if($checkMessageId == 0){
			        	$recordModelCTWhatsApp->set('message_type', $type);
						$recordModelCTWhatsApp->set('message_body', $body);
						$recordModelCTWhatsApp->set('whatsapp_contactid', $relatedToRecordid);
						if($relatedToRecordid){
				            $setype = VtigerCRMObject::getSEType($relatedToRecordid);
				            $recordModelData = Vtiger_Record_Model::getInstanceById($relatedToRecordid, $setype);
				            $displayname = $recordModelData->get('label');
				            $recordModel1->set('whatsapp_displayname', $displayname);
				        }else{
				            $recordModel1->set('whatsapp_displayname', $sendsmsnumber);
				        }
						$recordModelCTWhatsApp->set('whatsapp_unreadread', 'Read');
						$recordModelCTWhatsApp->set('whatsapp_withccode', $fromTo[0]);
						$recordModelCTWhatsApp->set('whatsapp_sendername', $fromTo[0]);
						$recordModelCTWhatsApp->set('whatsapp_fromno', $whatsappScanNo);
						$recordModelCTWhatsApp->set('whatsapp_datetime', $getMessageDateTime);
						$recordModelCTWhatsApp->set('assigned_user_id', $relatedTosmownerid);
						$recordModelCTWhatsApp->set('your_number', $whatsappScanNo);
						$recordModelCTWhatsApp->set('msgid', $messageid);
						$recordModelCTWhatsApp->save();
		        	}
		        }
	    	}
	    }
    }

    function autoResponderPopup(Vtiger_Request $request){
    	global $current_user;
    	$moduleName = $request->getModule();
    	$currenUserID = $current_user->id;
        $configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationDataWithId();
        $autoResponderText = $configurationData['autoResponderText'];

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('AUTOREPONDERTEXT', $autoResponderText);
    	echo $viewer->view('AutoResponderPopup.tpl', $moduleName, true);
    }

    function updateAutoResponderMessage(Vtiger_Request $request){
    	CTWhatsApp_Module_Model::autoResponderUpdate($request);
    }

    function getWhatsAppGroup(Vtiger_Request $request){
    	$getModuleRecrods = CTWhatsApp_Record_Model::getModuleRecrods($request);
		echo $getModuleRecrods;
    }

    function getWhatsappMessageInRelatedTab(Vtiger_Request $request) {
		global $adb, $site_URL, $current_user, $root_directory;
		$moduleName = $request->getModule();
		$recordId = $request->get('recordId');
		$setype = $request->get('sourceModule');
		$nextWhatsappRelatedMessage = $request->get('nextWhatsappRelatedMessage');
		$whatsappModule = '';
		$groupid = '';
		
		if($setype){
			$recordData = CTWhatsApp_Record_Model::getWhatsAppRelatedRecord($recordId, $setype, $whatsappModule, $groupid, $nextWhatsappRelatedMessage);
			$response = new Vtiger_Response();
			$response->setResult($recordData);
			$response->emit();
		}
    }

    function getWhatsAppGroupMessages(Vtiger_Request $request){
    	global $adb, $current_user;
		$userID = $current_user->id;
		$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($userID);
		$whatsappScanNo = $configurationData['whatsappno'];

		$inNumberQuery = CTWhatsApp_Record_Model::getInNumberQuery($userID);

		$whatsappModule = $request->get('whatsappModule');
		$moduleRecordid = $request->get('moduleRecordid');
		
		$loadGroupMessageNumber = $request->get('loadGroupMessageNumber');

    	$setype = VtigerCRMObject::getSEType($moduleRecordid);
		
		$unreadQuery = CTWhatsApp_Record_Model::unreadQuery(); 
		if($whatsappModule == "Groups"){
			$groupid = $request->get('groupid');
			$query = $adb->pquery("SELECT * FROM (
			    SELECT * FROM vtiger_ctwhatsapp 
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid 
				WHERE vtiger_crmentity.deleted = 0 AND msgid = ? ".$inNumberQuery." ORDER BY whatsapp_datetime DESC LIMIT ".$loadGroupMessageNumber.",25 ) wp_group ORDER BY ctwhatsappid ASC", array($groupid));
		}else{
			if($setype){
				$recordId = $request->get('moduleRecordid');
				$query = $adb->pquery("SELECT * FROM (
			    SELECT * FROM vtiger_ctwhatsapp 
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid 
				WHERE vtiger_crmentity.deleted = 0 AND whatsapp_contactid = ? ".$inNumberQuery." ORDER BY whatsapp_datetime DESC LIMIT ".$loadGroupMessageNumber.",25 ) wp_group ORDER BY ctwhatsappid ASC", array($recordId));
			}else{
				$groupid = $request->get('groupid');
				$groupid = preg_replace('/[^A-Za-z0-9]/', '', $groupid);
				$query = $adb->pquery("SELECT * FROM (
			    SELECT * FROM vtiger_ctwhatsapp 
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid 
				WHERE vtiger_crmentity.deleted = 0 AND whatsapp_withccode LIKE ".$groupid." ".$inNumberQuery." ORDER BY whatsapp_datetime DESC LIMIT ".$loadGroupMessageNumber.",25 ) wp_group ORDER BY ctwhatsappid ASC", array());
			}
		}
    	if($loadGroupMessageNumber){
    		$loadMessage = $loadGroupMessageNumber + 25;
    	}
    	
		$rows = $adb->num_rows($query);

		$totalSent = 0;
		$totalReceived = 0;
		$whatsappMessage = array();

		$whatsappMessageHTML = '';

		$imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
		$pdfExts = array("pdf");
		$fileExts = array("txt", "php", "zip", "csv", "https");
		$mp3Exts = array("mp3");
		$excelExts = array("xls");
		$wordlExts = array("docx", "doc");

		$themeView = CTWhatsApp_Record_Model::getWhatsappTheme();
        if($themeView == 'RTL'){
            $taskstyle = 'style="float: right;margin-left: 10px;width: 15px; !important;cursor: pointer;"';
            $menuicon = 'margin: 0px 25px 0px 0px;';
            $menuwidth = 'min-width: 90px !important;';
        }else{
            $taskstyle = 'style="float: left;margin-right: 0px;width: 15px; !important;cursor: pointer;"';
            $menuicon = 'margin: 0px 0px 0px 18px;';
            $menuwidth = 'min-width: 90px !important;';
        }

		for ($i=0; $i < $rows; $i++) { 
			$ctWhatsappId = $adb->query_result($query, $i, 'ctwhatsappid');
			$messageImportant = $adb->query_result($query, $i, 'whatsapp_important');
			$messageType = $adb->query_result($query, $i, 'message_type');
			$messageReadUnRead = $adb->query_result($query, $i, 'whatsapp_unreadread');
			$messageSenderame = $adb->query_result($query, $i, 'whatsapp_sendername');
			$isGroup = $adb->query_result($query, $i, 'whatsapp_withccode');
			$your_number = $adb->query_result($query, $i, 'your_number');
			$getNumberDetails = CTWhatsApp_Record_Model::getWhatsAppDetailWithMobileNo($your_number);
			$getNumberUsername = $getNumberDetails['username'];
			$quotemessage = $adb->query_result($query, $i, 'whatsapp_quotemessage');
			$msgid = $adb->query_result($query, $i, 'msgid');
			$documentBody = $adb->query_result($query, $i, 'message_body');
			$whatsapp_chatid = $adb->query_result($query, $i, 'whatsapp_chatid');
			$whatsapp_contactid = $adb->query_result($query, $i, 'whatsapp_contactid');

			if($ctWhatsappId){
				if($messageReadUnRead == "Unread" && $messageType == 'Recieved'){
					$recordModel = Vtiger_Record_Model::getInstanceById($ctWhatsappId, 'CTWhatsApp');
					$recordModel->set('mode', 'edit');
					$recordModel->set('id', $ctWhatsappId); 
					$recordModel->set('whatsapp_unreadread', 'Read');
					$recordModel->save();
				}
			}

			$messageBody = nl2br(preg_replace("#\*([^*]+)\*#", "<b>$1</b>", $adb->query_result($query, $i, 'message_body')));
			
			$urlExt = pathinfo($messageBody, PATHINFO_EXTENSION);
			if (in_array($urlExt, $imgExts)) {
			    $messageBody = '<image src="'.$messageBody.'" style="height: 60px !important;cursor: pointer;">';
			}else if(in_array($urlExt, $fileExts)){
				$messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsApp/image/fileicon.png"></a>';
			}else if(in_array($urlExt, $pdfExts)){
				$messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsApp/image/pdficon.png"></a>';
			}else if(in_array($urlExt, $excelExts)){
				$messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsApp/image/excelicon.png"></a>';
			}else if(in_array($urlExt, $wordlExts)){
				$messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsApp/image/wordicon.jpg"></a>';
			}else if(in_array($urlExt, $mp3Exts)){
				$messageBody = ' <audio controls>
								  	<source src="'.$messageBody.'" type="audio/ogg">
								  	<source src="'.$messageBody.'" type="audio/mpeg">
									Your browser does not support the audio element.
								</audio> ';
			}

			if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
				$replyMessageHTML = '';
				$copyMessage = '';
				$createTaskMessage = '';
				$whatsAppFileName = CTWhatsApp_Record_Model::getFilenameWhatsappMessage($documentBody);
				$notReplyWhatsapp = '0';
			}else{
				if($whatsappModule != "Groups"){
					$replyMessageHTML = '
					<span class="replyMessageBody" data-replymessage="'.$messageBody.'" data-replymessageid="'.$msgid.'">
	            		<img style="float: left;width: 15px; !important" src="layouts/v7/modules/CTWhatsApp/image/reply.png" title="'.vtranslate("LBL_REPLY", 'CTWhatsApp').'"><p>'.vtranslate("LBL_REPLY", 'CTWhatsApp').'</p>
	        		</span>';

	        		$copyMessage = '
					<span class="copyMessageBody" data-copymessage="'.$messageBody.'">
	            		<img style="float: left;width: 15px; !important" src="layouts/v7/modules/CTWhatsApp/image/copy.png" title="'.vtranslate("LBL_COPY", 'CTWhatsApp').'"><p>'.vtranslate("LBL_COPY", 'CTWhatsApp').'</p>
	        		</span>';
	        		if($whatsapp_chatid){
						$createTaskMessage = '
							<span style="float: left;width: 15px; !important;cursor: pointer;">
							    <a href="index.php?module=Calendar&view=Detail&record='.$whatsapp_chatid.'" target="_blank"><img class="taskid"  style="width: 15px;" src="layouts/v7/modules/CTWhatsApp/image/watch.jpg" title="'.vtranslate("LBL_VIEW", 'Vtiger').'"><p>'.vtranslate("LBL_VIEW", 'Vtiger').'</p></a>
							</span>';
					}else{
						$createTaskMessage = '
							<span class="taskMessageBody quickCreateTaskModule" data-task="yes" data-whatsappid="'.$ctWhatsappId.'"  data-url="index.php?module=Calendar&view=QuickCreateAjax&contact_id='.$whatsapp_contactid.'&description='.$messageBody.'" data-taskmessage="'.$messageBody.'">
							    <img style="float: left;width: 15px; !important;cursor: pointer;" src="layouts/v7/modules/CTWhatsApp/image/watch.jpg" title="'.vtranslate("LBL_CREATE", 'Vtiger').'"><p>'.vtranslate("LBL_CREATE", 'Vtiger').'</p>
							</span>';
					}
					$notReplyWhatsapp = '1';
	        	}
	        	$whatsAppFileName = '';
        	}

			$createdTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($query, $i, 'whatsapp_datetime'));
			$whatsappMessage[] = array('messageType' => $messageType, 'messageBody' => $messageBody, 'createdTime' => $createdTime);

			if($messageType == 'Send' || $messageType == 'Mass Message'){
				$totalSent = $totalSent + 1;
				$whatsappMessageHTML .= '<div class="sendChat">
											<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
											</div>
											<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
											</div>
											<div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">
												<div class="mainMessageDiv">';
												if($notReplyWhatsapp != '0'){
													$whatsappMessageHTML .= '<div class="dropdown" style="width: max-content !important;">
						                                                  <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="float: right !important;">
						                                                    <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
						                                                  </div>
						                                                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="'.$menuwidth.'">
						                                                  	<div class="dropdownInnerMenu">
						                                                    <a>';
						                                                      $whatsappMessageHTML .= $replyMessageHTML;
						                            $whatsappMessageHTML .= '</a>
						                                                    <a>';
						                                                      $whatsappMessageHTML .= $copyMessage;
						                            $whatsappMessageHTML .= '</a>
						                            						</div>
						                                                  </div>
						                                                </div>';
												}
												$whatsappMessageHTML .= '<div class="bubble send" data-whatsappid='.$ctWhatsappId.'>';
													if($quotemessage != ''){
														$whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';	
													}
													if($isGroup == 'Groups'){
														$whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
													}
													$whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p>
												</div>
												</div>
												<span class="chatTime"><b>'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
												if($messageReadUnRead == 'Read'){
													$whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsApp/image/read.png">';
												}else{
													$whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsApp/image/unread.png">';
												}
					$whatsappMessageHTML .= '</span></div>
										</div>';

			}else if($messageType == 'Recieved'){
				$totalReceived = $totalReceived + 1;
				$whatsappMessageHTML .= '<div class="replyChat">
											<div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">';
												$whatsappMessageHTML .= '<div class="bubble reply" data-whatsappid='.$ctWhatsappId.'>';
													if($quotemessage != ''){
														$whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';	
													}
													if($isGroup == 'Groups'){
														$whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
													}
													$whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p>
												</div>';

												if($notReplyWhatsapp != '0'){
													$whatsappMessageHTML .= '<div class="dropdown"  style="display: inline-block !important;width: max-content !important;'.$menuicon.'">
	                                                  <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="'.$menuwidth.'">
	                                                    <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
	                                                  </div>
	                                                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
	                                                  <div class="dropdownInnerMenu">';
							                        $whatsappMessageHTML .= '<a>'.$replyMessageHTML.'</a><a>'.$copyMessage.'</a><a>'.$createTaskMessage.'</a>'; 
	                                                if($setype){
														if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
															$whatsappMessageHTML .= '';
														}else{
															$whatsappMessageHTML .= '<a>
				                                            	<span class="editField" data-messagebody="'.$messageBody.'" style="cursor: pointer;">
				                                            		<img style="float: left;width: 15px; !important; margin: 0px 0px 0px 55px;" src="layouts/v7/modules/CTWhatsApp/image/editcontent.png" title="'.vtranslate("LBL_EDITFIELD", 'CTWhatsApp').' '.vtranslate($setype, $setype).'"><p>'.vtranslate("LBL_EDITFIELD", 'CTWhatsApp').' '.vtranslate($setype, $setype).'</p>
				                                            	</span></a>';
														}
													}
							                        $whatsappMessageHTML .= '</div></div></div>';
							                    }

												$whatsappMessageHTML .= '<span class="chatTime" style="width: 100%; !important"><b>'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
												if($messageReadUnRead == 'Read'){
													$whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsApp/image/read.png">';
												}else{
													$whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsApp/image/unread.png">';
												}
					$whatsappMessageHTML .= '</span></div>';
											$whatsappMessageHTML .= '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
											</div>

										</div>';
			}
			
		}
		$whatsappMessageHTML .= '';

		$result = array('whatsappMessageHTML' => $whatsappMessageHTML, 'nextLoadMessage' => $loadMessage, 'rows' => $rows);
    	$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.$moduleName.resources.CTWhatsaApp",
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances,$headerScriptInstances);
        return $headerScriptInstances;
    }

    public function readAllWhatsAppMessages(Vtiger_Request $request){
    	global $adb, $current_user;
		$userID = $current_user->id;
		$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($userID);
		$whatsappScanNo = $configurationData['whatsappno'];
		
		$adb->pquery("UPDATE vtiger_ctwhatsapp INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid SET vtiger_ctwhatsapp.whatsapp_unreadread = 'Read' WHERE vtiger_ctwhatsapp.your_number = ? AND vtiger_crmentity.deleted = 0", array($whatsappScanNo));
    }

    public function addGroupNumberInCRMPopup(Vtiger_Request $request){
    	$moduleName = $request->getModule();

    	$whatsaappModule = CTWhatsApp_Record_Model::getWhatsappAllowModules();

    	$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('WHATSAPPMODULES', $whatsaappModule);
		
		echo $viewer->view('AddGroupNumberInCRMPopup.tpl', $moduleName, true);
		
    }

    public function getSourceModuleField(Vtiger_Request $request){
    	$moduleName = $request->getModule();
    	$sourceModule = $request->get('sourceModule');

		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$fields = $moduleModel->getFields();
		$nameFieldsData = '<option value=""></option>';
		$phoneFieldsData = '<option value=""></option>';
		foreach ($fields as $key => $value) {
			$uitype = $value->uitype;
			if($uitype == '55' || $uitype == '255'){
				$fieldlabel = $value->label;
				$fieldname = $value->name;
				$nameFieldsData .= '<option value='.$fieldname.'>'.vtranslate($fieldlabel,$fieldlabel).'</option>';
			}
			if($uitype == '11'){
				$fieldlabel = $value->label;
				$fieldname = $value->name; 
				$phoneFieldsData .= '<option value='.$fieldname.'>'.vtranslate($fieldlabel,$fieldlabel).'</option>';
			}
		}
		$result = array('nameFieldsData' => $nameFieldsData, 'phoneFieldsData' => $phoneFieldsData);
    	$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }

    public function addGroupNumberInCRM(Vtiger_Request $request){
		global $adb, $current_user;
    	$groupid = $request->get('groupid');
    	$sourceModule = $request->get('sourceModule');
    	$copyNameTo = $request->get('copyNameTo');
    	$whatsappNumberTo = $request->get('whatsappNumberTo');

    	if($groupid){
			$userID = $current_user->id;
			$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($userID);
			$apiUrl = $configurationData['api_url'];
	        $authToken = $configurationData['authtoken'];
        	$url = $apiUrl.'/groupinfo';
            $postfields = array(
                'jid' => $groupid
            );

            $groupInfo = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $authToken);

            $moduleInstance = Vtiger_Module::getInstance($sourceModule);
            $baseTable = $moduleInstance->basetable;
            $baseTableid = $moduleInstance->basetableid;

            if($groupInfo['metadata']['participants']){
	            foreach ($groupInfo['metadata']['participants'] as $key => $value) {
	            	$memberid = explode('@', $value['id']);
	            	
	            	if($value['name']){
	                    $memberName = $value['name'];
	                }else{
	                    $memberName = '~';
	                }

	                $mobileno = substr($memberid[0],-10);

	                $query = $adb->pquery("SELECT * FROM ".$baseTable." 
	                	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ".$baseTable.".".$baseTableid." 
	                	WHERE vtiger_crmentity.deleted = 0 AND ".$whatsappNumberTo." LIKE '%".$mobileno."'", array());
	                $row = $adb->num_rows($query);
	                
	                if($row == 0){
		                $recordModel = Vtiger_Record_Model::getCleanInstance($sourceModule);
		            	$recordModel->set($whatsappNumberTo, $memberid[0]);
		            	$recordModel->set($copyNameTo, $memberName);
		            	$recordModel->set('assigned_user_id', $userID);
		            	$recordModel->save();

		            	$sourceModuleRecordid = $recordModel->getId();
		            	if($sourceModuleRecordid){
		            		$adb->pquery("UPDATE vtiger_ctwhatsapp SET vtiger_ctwhatsapp.whatsapp_contactid = ? WHERE vtiger_ctwhatsapp.whatsapp_chatid = ? AND vtiger_ctwhatsapp.msgid= ?", array($sourceModuleRecordid, $memberid[0], $groupid));
		            	}
	                }
	            }
            }
    	}
    }

    public function getNewLoadWhatsAppGroup(Vtiger_Request $request){
		global $adb, $current_user;
    	$groupWhatsappNumber = $request->get('groupWhatsappNumber');

    	$getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappusers WHERE whatsappno = ?", array($groupWhatsappNumber));
        $whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
        if($whatsaAppRows == 0){
            $getConfigurationDatasQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration WHERE whatsappno = ?", array($groupWhatsappNumber));
            $whatsaAppRow = $adb->num_rows($getConfigurationDatasQuery);
            if($whatsaAppRow == 1){
                $apiUrl = $adb->query_result($getConfigurationDatasQuery, 0, 'api_url');
                $authToken = $adb->query_result($getConfigurationDatasQuery, 0, 'auth_token');
            }
        }else{
            $apiUrl = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
            $authToken = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
        }

        $url = $apiUrl.'/chatlist';
        $postfields = array();

        if($groupWhatsappNumber){
            $getAllGroups = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $authToken);
        }

        $groupsData = array();
        foreach ($getAllGroups as $key => $value) {
            $jid = explode('@', $value['id']);
            if($jid[1] == 'g.us'){
                $recordId = '';
                $label = $value['name'];
                $isReadOnly = $value['isReadOnly'];
                $groupMember = count($value['metadata']['participants']);
                $labelExplode = explode(' ', $label);
                $profileImage = 'layouts/v7/modules/CTWhatsApp/image/groups.png';
                $groupid = $value['id'];
                $imagetag = 1;

                $query = $adb->pquery("SELECT * FROM vtiger_whatsapplog 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsapplog.whatsapplogid 
                    WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsapplog.whatsapplog_msgid = ?", array($value['id']));
                $groupRow = $adb->num_rows($query);
                
                if($groupRow == 0){
                    $recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppLog');
                    $recordModel->set('whatsapplog_sendername', $label);
                    $recordModel->set('messagelog_type', 'Recieved');
                    $recordModel->set('whatsapplog_chatid', $label);
                    $recordModel->set('whatsapplog_unreadread', 'Read');
                    $recordModel->set('whatsapplog_withccode', 'Groups');
                    $recordModel->set('whatsapplog_msgid', $value['id']);
                    $recordModel->set('whatsapplog_your_number', $groupWhatsappNumber);
                    $recordModel->set('whatsapplog_displayname', $value['id']);
                    $recordModel->set('whatsapplog_datetime', date('Y-m-d H:i:s'));
                    $recordModel->save();
                }else{
                	$whatsapplogid = $adb->query_result($query, 0, 'whatsapplogid');
                	$recordModel = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppLog');
                    $recordModel->set('id', $whatsapplogid);
                    $recordModel->set('mode', 'edit');
                    $recordModel->set('whatsapplog_sendername', $label);
                    $recordModel->set('whatsapplog_chatid', $label);
                    $recordModel->save();
                }

                foreach ($value['metadata']['participants'] as $key1 => $value1) {
	                $memberid = explode('@', $value1['id']);
	                $groupMembers = array('memberNo' => $memberid[0]);
	                if($value1['name']){
	                    $memberName = $value1['name'];
	                }else{
	                    $memberName = '~';
	                }

	                $existingMember = $adb->pquery("SELECT * FROM vtiger_ctwhatsapp 
	                	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid 
	                	WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapp.msgid = ? AND vtiger_ctwhatsapp.whatsapp_chatid = ?", array($groupid, $memberid[0]));
	                $rows = $adb->num_rows($existingMember);
	                if($rows == 0){
	                	$recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
	                    $recordModel->set('whatsapp_withccode', 'Groups');
	                    $recordModel->set('your_number', $groupWhatsappNumber);
	                    $recordModel->set('msgid', $groupid);
	                    $recordModel->set('whatsapp_displayname', $groupid);
	                    $recordModel->set('whatsapp_chatid', $memberid[0]);
	                    $recordModel->set('whatsapp_sendername', $memberName);
	                    $recordModel->save();
	                }

                }
            }
        }
    }

    public function getWhatsAppGroupMembers(Vtiger_Request $request){
		global $adb, $current_user;
		$userID = $current_user->id;
    	$groupId = $request->get('groupId');
    	$groupWhatsappNumber = $request->get('groupWhatsappNumber');

		$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($userID);
		$apiUrl = $configurationData['api_url'];
		$auth_token = $configurationData['authtoken'];
        
		$url = $apiUrl.'/groupinfo';
		$postfields = array(
			'jid' => $groupId
		);

		$response = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
		
		foreach ($response['metadata']['participants'] as $key => $value) {
			$customerNumber = explode('@', $value['id']);
			$customerName = $value['name'];

			$existingMember = $adb->pquery("SELECT * FROM vtiger_ctwhatsapp 
            	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid 
            	WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapp.msgid = ? AND vtiger_ctwhatsapp.whatsapp_chatid = ?", array($groupId, $customerNumber[0]));
            $rows = $adb->num_rows($existingMember);
            if($rows == 0){
				$recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsApp');
		        $recordModel->set('whatsapp_withccode', 'Groups');
		        $recordModel->set('your_number', $groupWhatsappNumber);
		        $recordModel->set('msgid', $groupId);
		        $recordModel->set('whatsapp_displayname', $groupId);
		        $recordModel->set('whatsapp_chatid', $customerNumber[0]);
		        $recordModel->set('whatsapp_sendername', $customerName);
		        $recordModel->save();
		    }else{
		    	$ctwhatsappid = $adb->query_result($existingMember, 0 ,'ctwhatsappid');
		    	$recordModel = Vtiger_Record_Model::getInstanceById($ctwhatsappid, 'CTWhatsApp');
                $recordModel->set('id', $ctwhatsappid);
                $recordModel->set('mode', 'edit');
                $recordModel->set('whatsapp_sendername', $customerName);
                $recordModel->save();
		    }
		}
    }

    public function userBeepOn(Vtiger_Request $request){
    	global $adb, $current_user;
		$userID = $current_user->id;
		$userBeepOn = CTWhatsApp_Record_Model::setUserBeepOn($userID);
    }

    public function userBeepOff(Vtiger_Request $request){
    	global $adb, $current_user;
		$userID = $current_user->id;
		$userBeepOff = CTWhatsApp_Record_Model::setUserBeepOff($userID);
    }
}





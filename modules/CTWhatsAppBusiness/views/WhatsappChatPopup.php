<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

class CTWhatsAppBusiness_WhatsappChatPopup_View extends Vtiger_IndexAjax_View {

	function __construct() {
		$this->exposeMethod('chatPopup');
		$this->exposeMethod('sentWhatsappMsg');
		$this->exposeMethod('allowAccessWhatsapp');
		$this->exposeMethod('checkNotificationCount');
	}

	function chatPopup(Vtiger_Request $request) { 
		global $adb, $site_URL, $current_user;
		$is_admin = $current_user->is_admin;
		$moduleName = $request->getModule();
		$currentDateTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date("Y-m-d h:i:sA"));
		$recordId = $request->get('recordid');
		$sourceModuleName = $request->get('sourcemodulename');
		$viewer = $this->getViewer($request);

		$currenUserID = $current_user->id;
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
		$whatsappStatus = $configurationData['whatsappstatus'];
		$themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();

		$admminScanDetail = CTWhatsAppBusiness_Record_Model::getAdmminScanDetail();
		$whatsappno = $admminScanDetail['whatsappno'];
		$whatsappUserManagemnt = $admminScanDetail['whatsappUserManagemnt'];

		$setype = VtigerCRMObject::getSEType($recordId);
		if($setype){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModuleName);
			$fullName = $recordModel->get('label');

			$profileImage = CTWhatsAppBusiness_Record_Model::getImageDetails($recordId, $setype);

			$allowModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($sourceModuleName);
			$phoneField = $allowModuleData['phoneField'];
			$mobilePhone = $recordModel->get($phoneField);

			$commentModuleEnable = CTWhatsAppBusiness_Record_Model::checkCommentModuleEnable($sourceModuleName);
			$whatsappMessages = CTWhatsAppBusiness_Record_Model::getIndividualMessages($recordId);
		}else{
			$fullName = $recordId;
			$whatsappMessages = CTWhatsAppBusiness_Record_Model::getIndividualMessages($recordId);
			$mobilePhone = $recordId;
			$recordId = '';
		}

		$multipleWhatsappNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedWhatsappNumber($currenUserID);
		foreach ($multipleWhatsappNumber as $key => $value) {
			if($value['whatsappstatus'] == 2){
				$noInternetNumber = $value['whatsappno'];
				break;
			}
		}

		$allUserNumber = CTWhatsAppBusiness_Record_Model::getAllUserWhatsappNumber($currenUserID);

		$whatsappFolderPath = "modules/CTWhatsAppBusiness/CTWhatsAppBusinessStorage/";
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
			
		$whatsappStorage = $site_URL.$whatsappFolderPath . "$year/$month/$week/";
		$scanQRCode = 'index.php?module=CTWhatsAppBusiness&parent=Settings&view=ConfigurationDetail&qrcode_status=1';

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ISADMIN', $is_admin);
		$viewer->assign('WHATSAPPSTATUS', $whatsappStatus);
		$viewer->assign('SCANQRCODE', $scanQRCode);
		$viewer->assign('PROFILEIMAGE', $profileImage);
		$viewer->assign('FULLNAME', $fullName);
		$viewer->assign('COMMETNMODULE', $commentModuleEnable);
		$viewer->assign('WHATSAPPMESSAGES', $whatsappMessages);
		$viewer->assign('SOURCEMODULE', $sourceModuleName);
		$viewer->assign('CURRENTDATETIME', $currentDateTime);
		$viewer->assign('WHATSAPPSTORAGEURL', $whatsappStorage);
		$viewer->assign('CURRENUSERNAME', $current_user->first_name.' '.$current_user->last_name);
		$viewer->assign('MOBILEPHONE', $mobilePhone);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
		$viewer->assign('WHATSAPP_NUMBER', $whatsappno);
		$viewer->assign('WHATSAPPUSERMANAGEMENT', $whatsappUserManagemnt);
		$viewer->assign('ALLUSERNUMBER', $allUserNumber);

		if($themeView == 'RTL'){
			echo $viewer->view('ChatPopupRTL.tpl', $moduleName, true);
		}else{
			echo $viewer->view('ChatPopup.tpl', $moduleName, true);
		}
	}

	function sentWhatsappMsg(Vtiger_Request $request){
		$sendMessageDate = CTWhatsAppBusiness_Record_Model::sendIndividulMessage($request);
		$numberactive = $sendMessageDate['numberactive'];

		$response = new Vtiger_Response();
		$response->setResult(array('sendMessage' => true, 'currenDatTime' => $sendMessageDate, 'numberactive' => $numberactive));
		$response->emit();
	}

	function allowAccessWhatsapp(Vtiger_Request $request) {
		$allowToWhatsAppModule = CTWhatsAppBusiness_Record_Model::getallowToWhatsAppModule($request);
		$iconActive = $allowToWhatsAppModule['iconActive'];
		$date = $allowToWhatsAppModule['date'];
		$currentDate = $allowToWhatsAppModule['currentDate'];
		$active = $allowToWhatsAppModule['active'];
		$unreadmsg = $allowToWhatsAppModule['unreadmsg'];
		$fieldValue = $allowToWhatsAppModule['fieldValue'];

		if($iconActive == 1 && strtotime($date) >= strtotime($currentDate)){
			$response = new Vtiger_Response();
			$response->setResult(array('active' => $active, 'unreadmsg' => $unreadmsg, 'fieldvalue' => $fieldValue));
			$response->emit();
		}	
	}

	function checkNotificationCount(Vtiger_Request $request) {
		global $adb, $current_user;
		$currenUserID = $current_user->id;
		$moduleName = $request->getModule();
		$senderNo = $request->get('senderNo');

		$configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
		$notificationtone = $configurationData['notificationtone'];
		$pushnotification = $configurationData['notification'];

		$relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId($senderNo);
		$relatedTo = $relatedToData['relatedTo'];
		$configureUserid = $relatedToData['smownerid'];
		$response = new Vtiger_Response();
		if($relatedTo){
			$setype = VtigerCRMObject::getSEType($relatedTo);

			$getcustomViewId = $adb->pquery("SELECT * FROM vtiger_customview WHERE entitytype=? AND viewname='All'", array($setype));
			$numRows = $adb->num_rows($getcustomViewId);
			if($numRows == 1){
				$viewid = $adb->query_result($getcustomViewId, 0, 'cvid');
			}

			$listViewModel = Vtiger_ListView_Model::getInstance($setype, $viewid);
			$queryGenerator = $listViewModel->get('query_generator');
			$listQuery = $queryGenerator->getQuery();
			$query = $adb->pquery($listQuery, array());
			$rows = $adb->num_rows($query);

			$moduleInstance = Vtiger_Module::getInstance($setype);
	        $baseTableid = $moduleInstance->basetableid;

			$notification = 0;
			for ($i=0; $i < $rows; $i++) { 
				$crmid = $adb->query_result($query, $i, $baseTableid);
				if($crmid == $relatedTo){
					$notification = $notification + 1;
				}
			}
			$response->setResult(array('notification' => $notification, 'notificationtone' => $notificationtone, 'pushnotification' => $pushnotification));
		}else{
			$response->setResult(array('notification' => 1, 'notificationtone' => $notificationtone, 'pushnotification' => $pushnotification));
		}
		$response->emit();
	}
}

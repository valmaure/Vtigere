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
class CTWhatsApp_WhatsappBot_View extends Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('WhatsappBotList');
		$this->exposeMethod('WhatsappBotConfiguration');
		$this->exposeMethod('getWhatsappBotFlowData');
		$this->exposeMethod('getWhatsappBotModuleFields');
		$this->exposeMethod('getWhatsappBotRelatedModuleFields');
		$this->exposeMethod('addFBFields');
		$this->exposeMethod('deleteFBFields'); 
		$this->exposeMethod('deleteBot');
		$this->exposeMethod('deleteWhatsAppBot');
		$this->exposeMethod('scanQRCodeInPopup');
		$this->exposeMethod('updateBotAuthenticationCode');
		$this->exposeMethod('getWhatsappBotStatus');
		$this->exposeMethod('botActiveDeactive');
		$this->exposeMethod('logoutWhatsApp');
		$this->exposeMethod('convertToBot');
		$this->exposeMethod('GetWhatsappBotList');
		$this->exposeMethod('createNewBot');
		$this->exposeMethod('whatsAppBotSetting');
		$this->exposeMethod('whatsAppBotButtonSetting');
		$this->exposeMethod('SaveWhatsAppBotButton');
		$this->exposeMethod('getWhatsappBotFlowbuilderField');
		$this->exposeMethod('WhatsappBotLicenseEdit');
		$this->exposeMethod('saveWhatsAppBotLicense');
		$this->exposeMethod('whatsAppBotLicenseDeactive');
		$this->exposeMethod('whatsAppBotLicenseDetail');
		$this->exposeMethod('getUserWhatsAppNumber');
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

    function WhatsappBotList(Vtiger_Request $request) {
		global $adb,$current_user;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$isAdmin = $current_user->is_owner;

		$selectBotsQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots");
		$rows = $adb->num_rows($selectBotsQuery);
		$allBots = array();

		$getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration");
		$multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');

		$getBotLicenseDetail = CTWhatsApp_WhatsappBot_View::getBotLicenseDetail();
		$licenseRows = $getBotLicenseDetail['rows'];
		$licensekey = $getBotLicenseDetail['licensekey'];
		$expirydate = $getBotLicenseDetail['expirydate'];
		$sendmessagelimit = $getBotLicenseDetail['sendmessagelimit'];
		$botLicenseStatus = $getBotLicenseDetail['status'];

		$currentdate = date('Y-m-d');
		$date = Settings_CTWhatsApp_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');
		$settingWhatsAppModules = 'Settings:CTWhatsApp';

		$today = date('Y-m-d');
        if(strtotime($date) >= strtotime($today)){
            $diff = strtotime($date) - strtotime($today);
            $dayRemain = abs(round($diff / 86400));
        }else{
            $dayRemain = 0;
        }

        $totalSendMonthMessage = CTWhatsApp_WhatsappBot_View::getMonthRecordCount();

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('WHTSAPPSTATUS', $whatsappbotstatus);
		$viewer->assign('ALLLBOTS', $allBots);
		$viewer->assign('WHATSAPPSTATUS', $whatsappbotstatus);
		$viewer->assign('WHATSAPPNUMBER', $whatsappno);
		$viewer->assign('WHATSAPPMANAGEMENT', $multipleWahtsapp);
		$viewer->assign('SETTINGMODULE', $settingWhatsAppModules);
		$viewer->assign('LICENCE_KEY', $licensekey);
		$viewer->assign('DAYREMAINING', $dayRemain);
		$viewer->assign('ISADMIN', $isAdmin);
		$viewer->assign('SENDMESSAGELIMIT', $sendmessagelimit);
		$viewer->assign('TOTALSENDMESSAGE', $totalSendMonthMessage);

		if(strtotime($date) > strtotime($currentdate) && $botLicenseStatus == 1){
			echo $viewer->view('WhatsappBotList.tpl', $moduleName, true);
		}else{
			echo $viewer->view('WhatsappBotLicense.tpl', $moduleName, true);
		}
	}

	public function getMonthRecordCount(){
		global $adb;
		$query = $adb->pquery("SELECT count(*) as totalrecord
			FROM vtiger_ctwhatsapp
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid
			WHERE vtiger_ctwhatsapp.message_type = 'Send' AND vtiger_crmentity.deleted = 0 AND MONTH(vtiger_crmentity.createdtime) = MONTH(NOW())
			AND YEAR(vtiger_crmentity.createdtime) = YEAR(NOW())");
		$totalrecord = $adb->query_result($query, 0, 'totalrecord');
		return $totalrecord;
	}

	function whatsAppBotLicenseDetail(Vtiger_Request $request){
		global $current_user;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$isAdmin = $current_user->is_owner;
		$getBotLicenseDetail = CTWhatsApp_WhatsappBot_View::getBotLicenseDetail();
		$licenseRows = $getBotLicenseDetail['rows'];
		$licensekey = $getBotLicenseDetail['licensekey'];
		$expirydate = $getBotLicenseDetail['expirydate'];
		$sendmessagelimit = $getBotLicenseDetail['sendmessagelimit'];

		$currentdate = date('Y-m-d');
		$date = Settings_CTWhatsApp_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');
		$settingWhatsAppModules = 'Settings:CTWhatsApp';

		$today = date('Y-m-d');
        if(strtotime($date) >= strtotime($today)){
            $diff = strtotime($date) - strtotime($today);
            $dayRemain = abs(round($diff / 86400));
        }else{
            $dayRemain = 0;
        }

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SETTINGMODULE', $settingWhatsAppModules);
		$viewer->assign('LICENCE_KEY', $licensekey);
		$viewer->assign('ISADMIN', $isAdmin);
		$viewer->assign('DAYREMAINING', $dayRemain);

		echo $viewer->view('WhatsappBotLicense.tpl', $moduleName, true);
	}

	function WhatsappBotLicenseEdit(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$getBotLicenseDetail = CTWhatsApp_WhatsappBot_View::getBotLicenseDetail();
		$licenseRows = $getBotLicenseDetail['rows'];
		$licensekey = $getBotLicenseDetail['licensekey'];
		$expirydate = $getBotLicenseDetail['expirydate'];
		$sendmessagelimit = $getBotLicenseDetail['sendmessagelimit'];

		$currentdate = date('Y-m-d');
		$date = Settings_CTWhatsApp_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');

		$settingWhatsAppModules = 'Settings:CTWhatsApp';

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SETTINGMODULE', $settingWhatsAppModules);
		$viewer->assign('LICENCE_KEY', $licensekey);

		echo $viewer->view('WhatsappBotLicenseEdit.tpl', $moduleName, true);
	}

	function getBotLicenseDetail(){
		global $adb;
		$licensevalidation = $adb->pquery("SELECT * FROM vtiger_ctwhatsapp_botlicense");
		$rows = $adb->num_rows($licensevalidation);
		$licenseDetail = array();
		$licenseDetail['rows'] = $rows;
		$licenseDetail['licensekey'] = $adb->query_result($licensevalidation, 0, 'licensekey');
		$licenseDetail['status'] = $adb->query_result($licensevalidation, 0, 'status');
		$licenseDetail['expirydate'] = $adb->query_result($licensevalidation, 0, 'expirydate');
		$licenseDetail['sendmessagelimit'] = $adb->query_result($licensevalidation, 0, 'sendmessagelimit');
		return $licenseDetail;
	}

	function whatsAppBotLicenseDeactive(Vtiger_Request $request){
		global $adb, $site_URL;
		$licensekey = $request->get('licensekey');

		$getBotLicenseDetail = CTWhatsApp_WhatsappBot_View::getBotLicenseDetail();
		$licenseRows = $getBotLicenseDetail['rows'];

		if($licenseRows == 1){
			$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => "https://crmtiger.com/whatsapp/newlicencedata.php",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
				CURLOPT_POSTFIELDS => array('license_key' => $licensekey,'domain' => $site_URL,'action' => 'deactivate'),
			));
			$response = curl_exec($curl);
			curl_close($curl);
			$result_response = json_decode($response,true);

			if($result_response['message']){
				$adb->pquery("DELETE FROM vtiger_ctwhatsapp_botlicense", array());

				$response = new Vtiger_Response();
				$response->setEmitType(Vtiger_Response::$EMIT_JSON);
				$response->setResult(array("message"=>$result_response['message']));
				$response->emit();
			
			}else{
				$response = new Vtiger_Response();
				$response->setEmitType(Vtiger_Response::$EMIT_JSON);
				$response->setResult(array("message"=>$result_response['message']));
				$response->emit();
			}
		}

	}

	function saveWhatsAppBotLicense(Vtiger_Request $request){
		global $adb, $site_URL;

		$getBotLicenseDetail = CTWhatsApp_WhatsappBot_View::getBotLicenseDetail();
		$licenseRows = $getBotLicenseDetail['rows'];

		$licenseKey = $request->get('licenseKey');
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://crmtiger.com/whatsapp/newlicencedata.php",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
			CURLOPT_POSTFIELDS => array('license_key' => $licenseKey,'domain' => $site_URL,'action' => 'activate'),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$result_response = json_decode($response,true);

		$expirydate = Settings_CTWhatsApp_SaveLicense_Action::encrypt_decrypt($result_response['expirydate'],'e');
		$sendmessagelimit = $result_response['messageallowed'];
		$message = $result_response['message'];
		if($message == 'Activated'){
			$status = '1';
		}else if($message == 'Already activated in another domain'){
			$status = '2';
		}else if($message == 'Invalid Data'){
			$status = '0';
		}

		if($status == 1){
			if($licenseRows == '1'){
				$query = $adb->pquery("UPDATE vtiger_ctwhatsapp_botlicense SET licensekey=?, expirydate=?, status=?, sendmessagelimit=?, domain=? WHERE 1",array($licenseKey, $expirydate, $status, $sendmessagelimit, $site_URL));
			}else{
				$query = $adb->pquery("INSERT INTO vtiger_ctwhatsapp_botlicense (licensekey,expirydate,status,sendmessagelimit,domain) values(?,?,?,?,?)",array($licenseKey, $expirydate, $status, $sendmessagelimit, $site_URL));
			}
		}else if($status == 2){
			if($licenseRows == '1'){
				$status = 1;
				$query = $adb->pquery("UPDATE vtiger_ctwhatsapp_botlicense SET licensekey=?, expirydate=?, status=?, sendmessagelimit=?, domain=? WHERE 1",array($licenseKey, $expirydate, $status, $sendmessagelimit, $site_URL));
			}
		}

		$result = array('msg' => $result_response['message'], 'status' => $status);

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	function getUserWhatsAppNumber(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
		$assignuserid = $request->get('assignuserid');
		$username = getUserName($assignuserid);

		$query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappusers WHERE whatsappstatus = '1' and customfield5 = ?", array($assignuserid));
		$rows = $adb->num_rows($query);
		$userWhatsAppnumber = array();
		$userNumbers = '<option value="">'.vtranslate('Select an Option',$moduleName).'</option>';
		for ($i=0; $i < $rows; $i++) { 
			$whatsappno = $adb->query_result($query, $i, 'whatsappno');
			$userWhatsAppnumber[] = array('whatsappnumber' => $whatsappno);

			$userNumbers .= '<option value='.$whatsappno.'>'.$whatsappno.'-'.$username.'</option>';
		}

		echo $userNumbers;
	}

	function createNewBot(Vtiger_Request $request) {
		global $adb;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$selectBotsQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE remove = 0");
		$rows = $adb->num_rows($selectBotsQuery);

		$whatsAppBot = array();
		for ($i=0; $i < $rows; $i++) { 
			$botname = $adb->query_result($selectBotsQuery, $i, 'botname');
			$botid = $adb->query_result($selectBotsQuery, $i, 'botid');
			$whatsAppBot[$botid] = $botname;
		}

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('ALL_WHATSAPP_BOT', $whatsAppBot);
		echo $viewer->view('NewWhatsappBot.tpl', $moduleName, true);
	}

	function GetWhatsappBotList(Vtiger_Request $request) {
		global $current_user;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$currenUserId = $current_user->id;
		$draw = $_POST['draw'];
		$start = $_POST['start'];
		$length = $_POST['length'];

		if($_POST['search']){
			$searchValue = $_POST['search']['value'];
			$searchQuery = " AND botname LIKE '%".$searchValue."%'";
		}

		global $adb;
		$SQL = "SELECT * FROM ctwhatsapp_bots WHERE assignuserid = ".$currenUserId." AND remove = 0".$searchQuery;
		$totalSQL = $SQL;

		if($_POST['columns'][0]['orderable']){
			$orderby = $_POST['order'][0]['dir'];
			$SQL.= " ORDER BY botname ".$orderby;
		}
		
		if($start != '' && $length != ''){
			$SQL.= " LIMIT $start, $length";
		}
		$selectBotsQuery = $adb->pquery($SQL);
		$rows = $adb->num_rows($selectBotsQuery);

		$allBots = array();
		for ($i=0; $i < $rows; $i++) { 
			$botid = $adb->query_result($selectBotsQuery, $i, 'botid');
			$botname = $adb->query_result($selectBotsQuery, $i, 'botname');
			$deleted = $adb->query_result($selectBotsQuery, $i, 'deleted');
			$remove = $adb->query_result($selectBotsQuery, $i, 'remove');
			$assignuserid = $adb->query_result($selectBotsQuery, $i, 'assignuserid');
			if($assignuserid){
				$username = '';
				$userQuery = $adb->pquery("SELECT * FROM vtiger_users WHERE id = ?", array($assignuserid));
				$username = $adb->query_result($userQuery, 0, 'first_name').' '.$adb->query_result($userQuery, 0, 'last_name');
			}else{
				$username = '';
			}

			if($remove != 1){
				$scanwhatsapp_number = $adb->query_result($selectBotsQuery, $i, 'scanwhatsapp_number');
				$whatsappbotstatus = '';	
				$getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration");
				$multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
				$status = 'Disconnected';
				if($multipleWahtsapp == 'multipleWhatsapp'){
					$scanNumberQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappusers WHERE whatsappno = ?", array($scanwhatsapp_number));
					if($adb->num_rows($scanNumberQuery)){
						$whatsappbotstatus = $adb->query_result($scanNumberQuery, 0, 'whatsappstatus');
						if($whatsappbotstatus == 1){
							$status = 'Connected';
						}
					}
				}else{
					$whatsappbotstatus = $adb->query_result($getUserGrous, 0, 'whatsappstatus');
					if($whatsappbotstatus == 1){
						$status = 'Connected';
					}
				}

				if($botid){
					$botQuery = "SELECT * FROM vtiger_ctwhatsapp INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid WHERE vtiger_ctwhatsapp.whatsapp_botid = ? AND vtiger_crmentity.deleted = 0";
					$totalBotCountQuery = $adb->pquery($botQuery, array($botid));
					$totalbotmessage = $adb->num_rows($totalBotCountQuery);
					if($totalbotmessage){
						$lasteMessageDateTime = '';
						$lasteBotDateTimeQuery = $adb->pquery($botQuery." ORDER BY vtiger_ctwhatsapp.ctwhatsappid DESC LIMIT 1", array($botid));
						$lastDateTime = $adb->query_result($lasteBotDateTimeQuery, 0, 'whatsapp_datetime');
						$lasteMessageDateTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($lastDateTime);
					}else{
						$lasteMessageDateTime = '';
					}

					$customerResponseQuery = $adb->pquery("SELECT * FROM whatsappbot_pre_que WHERE prebotid = ?", array($botid));
					$customerResponseCount = $adb->num_rows($customerResponseQuery);
				}

				$checked = '';
				if($deleted == 1){
					$checked = 'checked';
				}
				$allBots[] = array('checkbox'=>'<input type="checkbox" class="activeBot" data-scannumber="'.$scanwhatsapp_number.'" data-whatsappbotstatus="'.$whatsappbotstatus.'" data-botid="'.$botid.'" '.$checked.' name="activeBot" id="activeBot" value="'.$deleted.'">','botname'=>'<a href="index.php?module=CTWhatsApp&view=WhatsappBot&mode=WhatsappBotConfiguration&botRecordId='.$botid.'">'.$botname.'</a>','assignuserid'=>$username,'status'=>$status,
					'scanwhatsapp_number'=>$scanwhatsapp_number,'totalbotmessage'=>$totalbotmessage,'lastemessagedatetime'=>$lasteMessageDateTime,'action'=>'<a href="index.php?module=CTWhatsApp&view=List&viewname=103&orderby=&sortorder=&app=SALES&search_params=[[[%22whatsapp_botid%22%2C%22c%22%2C%22'.$botid.'%22]]]" target="_blank">
									<img src="layouts/v7/modules/CTWhatsApp/image/botlog.png" title="'.vtranslate('LBL_CHATLOG',$moduleName).'" style="width: 15px;">
								</a>&nbsp;&nbsp;<a href="index.php?module=CTWhatsApp&view=WhatsappBot&mode=WhatsappBotConfiguration&duplicateRecordId='.$botid.'">
									<i class="fa fa-clone" title="'.vtranslate('LBL_DUPLICATE',$moduleName).'"></i>
								</a>&nbsp;&nbsp;
								<a href="index.php?module=CTWhatsApp&view=WhatsappBot&mode=WhatsappBotConfiguration&botRecordId='.$botid.'">
									<i class="fa fa-pencil" title="'.vtranslate('LBL_EDIT',$moduleName).'"></i>
								</a>&nbsp;&nbsp;
								<span class="deleteBot" data-botid="'.$botid.'" onclick="deleteBot('.$botid.')" style="cursor:pointer;">
									<i class="fa fa-trash" title="'.vtranslate('LBL_DELETE',$moduleName).'"></i>
								</span>', 'customerResponseCount' => '<a href="index.php?module=CTWhatsApp&view=WhatsappChat&mode=allWhatsAppMSG&customerResponse=1">'.$customerResponseCount.'</a>');
			}
		}

		$totalResult = $adb->pquery($totalSQL,array());
		$totalCount = $adb->num_rows($totalResult);

		$result = array('draw'=>(int)$draw,'recordsTotal'=>$totalCount,'recordsFiltered'=>$totalCount,'data'=>$allBots);
		echo json_encode($result);
		exit;
	}

	function WhatsappBotConfiguration(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		global $adb, $current_user;
		$currentUserID = $current_user->id;
		$botRecordId = $request->get('botRecordId');
		$duplicateRecordId = $request->get('duplicateRecordId');
		$selectBotsQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE botid = ?", array($botRecordId));
		$rows = $adb->num_rows($selectBotsQuery);
		if($rows){
			$botjson = $adb->query_result($selectBotsQuery, $i, 'botjson');
			$botname = $adb->query_result($selectBotsQuery, $i, 'botname');
			$scanWhatsappNumber = $adb->query_result($selectBotsQuery, $i, 'scanwhatsapp_number');
			$assignuserid = $adb->query_result($selectBotsQuery, $i, 'assignuserid');
			$viewer->assign('BOATNAME', $botname);
			$viewer->assign('SCANWHATSAPPNUMBER', $scanWhatsappNumber);
			$viewer->assign('ASSINGUSERID', $assignuserid);
		}

		if($assignuserid == ''){
			$viewer->assign('ASSINGUSERID', $currentUserID);
		}else{
			$viewer->assign('ASSINGUSERID', $assignuserid);
		}

		if($duplicateRecordId){
			$selectBotsQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE botid = ?", array($duplicateRecordId));
			$rows = $adb->num_rows($selectBotsQuery);
			if($rows){
				$botjson = $adb->query_result($selectBotsQuery, $i, 'botjson');
				$botname = $adb->query_result($selectBotsQuery, $i, 'botname');
				$scanWhatsappNumber = $adb->query_result($selectBotsQuery, $i, 'scanwhatsapp_number');
				$viewer->assign('BOATNAME', $botname);
				$viewer->assign('SCANWHATSAPPNUMBER', $scanWhatsappNumber);

			}
		}

		$vicileadid = $adb->query("select tabid, name from vtiger_tab");
	 	$num_rows = $adb->num_rows($vicileadid);
		for($i=0; $i<$num_rows; $i++) {
			$moduleArray[]=array(
				'tabid'=>$adb->query_result($vicileadid, $i,'tabid'),
				'name'=>$adb->query_result($vicileadid, $i,'name'));
		}
		$selectFieldsQuery = $adb->pquery("SELECT * FROM flowbuilderfields");
		$rows = $adb->num_rows($selectFieldsQuery);
		$fieldArray=array();
		for($i=0; $i<$rows; $i++) {
			$fieldArray[]=array(
				'fieldname'=>$adb->query_result($selectFieldsQuery, $i,'fieldname'),
				'slug'=>$adb->query_result($selectFieldsQuery, $i,'slug'),
				'iconclass'=>$adb->query_result($selectFieldsQuery, $i,'iconclass'),
				'question'=>$adb->query_result($selectFieldsQuery, $i,'question'));
		}

		$query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappusers WHERE whatsappstatus = '1' and customfield5 = ?", array($currentUserID));
		$rowsdata = $adb->num_rows($query);
		$userWhatsAppnumber = array();
		
		for ($i=0; $i < $rowsdata; $i++) { 
			$whatsappno = $adb->query_result($query, $i, 'whatsappno');
			$whatsappstatus = $adb->query_result($query, $i, 'whatsappstatus');
			$userWhatsAppnumber[] = array('whatsappno' => $whatsappno, 'userid' => $currentUserID, 'whatsappstatus' => $whatsappstatus, 'username' => getUserName($currentUserID));
		}
		
		$viewer->assign('MODULLIST', $moduleArray);
		$viewer->assign('FIELDLISTS', $fieldArray);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('BOTJSON', $botjson);
		$viewer->assign('BOTRECORDID', $botRecordId);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $userWhatsAppnumber);
		$viewer->assign('DUPLICATEBOT', $duplicateRecordId);
		echo $viewer->view('WhatsappBotConfiguration1.tpl', $moduleName, true);
	}

	function getWhatsappBotFlowData(Vtiger_Request $request){
		global $adb, $current_user;
		$currentUserID = $current_user->id;
		$moduleName = $request->getModule();
		$crmJson = $request->get('crmJson');
		$flowData = $request->get('flowData');
		
		$botRecordid = $request->get('botRecordid');
		$scanWhatsappNumber = $request->get('scanWhatsappNumber');
		$botName = $request->get('botName');
		$assignuserid = $request->get('assignuserid');
		$assignuserchange = $request->get('assignuserchange');
		$duplicateBot = $request->get('duplicateBot');
		
		if($assignuserchange == 1){
			$adb->pquery("UPDATE ctwhatsapp_bots SET assignuserid = ?, deleted = ?, scanwhatsapp_number = ".$scanWhatsappNumber." WHERE botid = ?", array($assignuserid, 0, $botRecordid));
		}else{
			if($botRecordid){
				$getBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE botid = ?", array($botRecordid));
				$deleted = $adb->query_result($getBotQuery, 0, 'deleted');
				$adb->pquery("DELETE FROM ctwhatsapp_bots WHERE botid = ?", array($botRecordid));
				$adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prebotid = ?", array($botRecordid));
				$adb->pquery("DELETE FROM ctwhatsappbot_que_master WHERE botid = ?", array($botRecordid));
				$adb->pquery("DELETE FROM ctwhatsappbot_opt_master WHERE botid = ?", array($botRecordid));
				$adb->pquery("DELETE FROM ctwhatsappbot_que_opt_assign WHERE botid = ?", array($botRecordid));
			}
			$insertBotQuery = $adb->pquery("INSERT INTO ctwhatsapp_bots (botjson,botname,deleted,scanwhatsapp_number,assignuserid) VALUES (?,?,?,?,?)", array($_REQUEST['flowData'],$botName,$deleted,$scanWhatsappNumber,$assignuserid));

			$getBotIdQuery = $adb->pquery("SELECT botid FROM ctwhatsapp_bots WHERE assignuserid = ? ORDER BY botid DESC LIMIT 1", array($currentUserID));
			$botID = $adb->query_result($getBotIdQuery, 0, 'botid');
			if($botID){
				$adb->pquery("UPDATE vtiger_ctwhatsapp SET whatsapp_botid = ? WHERE whatsapp_botid = ?", array($botID, $botRecordid));
			}
			if($duplicateBot){
				$adb->pquery("UPDATE vtiger_ctwhatsapp SET whatsapp_botid = '' WHERE whatsapp_botid = ?", array($botID));	
			}

			foreach ($crmJson as $key => $value) {
				$question ="";
				$type=$value['data']['type'];
				if(isset($value['data']['question'])){
					$question =$value['data']['question'];
				}
				$messagetype = "";
				if($type == 'chatwithoperator-node'){
					$messagetype = 'chatwithoperator';
				}else{
					if(isset($value['data']['messagetype'])){
						$messagetype = $value['data']['messagetype'];
					}
				}
				$varmessagetype = "";
				if(isset($value['data']['varmessagetype'])){
					$varmessagetype = $value['data']['varmessagetype'];
				}
				$copy_from=isset($value['data']['copyfrom'])?$value['data']['copyfrom']:'';
				$copy_to=isset($value['data']['copyto'])?$value['data']['copyto']:'';
				$responseimg=isset($value['data']['responseimg'])?$value['data']['responseimg']:'';

				$insertQuestionQuery = $adb->pquery("INSERT INTO ctwhatsappbot_que_master(messagetype,que_text, que_type_id, search_module, search_column, botid, que_sequence, type, next_sequence, varmessagetype,copy_from, copy_to, responseimg) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)", array($messagetype,$question, '', '', '', $botID, $key, $type, $value['outputs'][0][0], $varmessagetype,$copy_from, $copy_to, $responseimg));

				$getQuestionIdQuery = $adb->pquery("SELECT que_id FROM ctwhatsappbot_que_master ORDER BY que_id DESC LIMIT 1");
				$questionId = $adb->query_result($getQuestionIdQuery, 0, 'que_id'); 
				$options = $value['data']['options'];
				if($type=='crm-action-node'){
					$tabid=$value['data']['tabid'];
					$action=$value['data']['action'];
					$related_tabid=$value['data']['related_tabid'];
	 				$insertCrmactionQuery = $adb->pquery("INSERT INTO ctwhatsappbot_crmaction_master(tabid, action, que_id,related_tabid) VALUES (?,?,?,?)", array($tabid, $action, $questionId,$related_tabid));
	 				$crmaction_id=$adb->getLastInsertID();
	 				foreach ($value['data']['fieldmappping'] as $k => $field) {
	 					$vtigerfield=$field['vtigerfield'];
	 					$flowbuilderfield=$field['flowbuilderfield'];
	 					$defaultvalue=$field['defaultvalue'];
	 					$insertCrmFieldQuery = $adb->pquery("INSERT INTO ctwhatsappbot_crmaction_fieldmapping(crmaction_id, vtigerfield, flowbuilderfield,defaultvalue) VALUES (?,?,?,?)", array($crmaction_id, $vtigerfield, $flowbuilderfield,$defaultvalue)); 
	  				}
	  				foreach ($value['data']['relatedfieldmappping'] as $k => $field) {
	 					$relvtigerfield=$field['relvtigerfield'];
	 					$relflowbuilderfield=$field['relflowbuilderfield'];
	 					$reldefaultvalue=$field['reldefaultvalue'];
	 					$insertCrmFieldQuery = $adb->pquery("INSERT INTO ctwhatsappbot_crmaction_relatedfieldmapping(relcrmaction_id, relvtigerfield, relflowbuilderfield,reldefaultvalue,botid) VALUES (?,?,?,?,?)", array($crmaction_id, $relvtigerfield, $relflowbuilderfield,$reldefaultvalue,$botID)); 
	  				}
	  				if($value['data']['responseVtiger']){
	  				  	$adb->pquery("INSERT INTO ctwhatsappbot_crmaction_fieldmapping(crmaction_id, vtigerfield, flowbuilderfield ,maptype) VALUES (?,?,?,?)", array($crmaction_id, $value['data']['responseVtiger'], $value['data']['responseVariable'],'response'));
	 				}
	 			}
	 			if($type=='condition-node'){
					$output_1=$value['outputs'][0][0];
					$output_2=$value['outputs'][1][0];
					$variablename=$value['data']['varConditon'];
					$condition=$value['data']['condition'];
					$variablevalue=$value['data']['question'];
		         	$insertCrmconditionQuery= $adb->pquery("INSERT INTO ctwhatsappbot_condition_assign(que_id, next_que_id1, next_que_id2, variablename, conditions, variablevalue) VALUES (?,?,?,?,?,?)", array($questionId, $output_1, $output_2, $variablename, $condition, $variablevalue));  
		         
	 			}
				if($options){
					foreach ($options as $key1 => $value1) {
						$opt_seq = $key1 + 1;
						$insertOptionQuery = $adb->pquery("INSERT INTO ctwhatsappbot_opt_master(opt_text, opt_seq, botid, seq_option) VALUES (?,?,?,?)", array($value1, $opt_seq, $botID, $key));

						$getOptionIdQuery = $adb->pquery("SELECT opt_id FROM ctwhatsappbot_opt_master ORDER BY opt_id DESC LIMIT 1");
						$optionId = $adb->query_result($getOptionIdQuery, 0, 'opt_id');

						$nextQuestionId = $value['outputs'][$key1][0];
	  
						if($optionId != 0){
							$insertQuery = $adb->pquery("INSERT INTO ctwhatsappbot_que_opt_assign(que_id, opt_id, next_que_id, botid) VALUES (?,?,?,?)", array($questionId, $optionId, $nextQuestionId, $botID));
						}
					}
				}else{
					$nextQuestionId = $value['outputs'][0][0]; 
				}
			}
		}
	}

	function getWhatsappBotModuleFields(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
  		$tabid = $request->get('tabid');  

  		$gettabname  = $adb->pquery("SELECT name from vtiger_tab where tabid = ?",array($tabid));
  		$modulename = $adb->query_result($gettabname, 0, 'name');

		$getBotIdQuery = $adb->pquery("SELECT * FROM vtiger_field  WHERE tabid = ?", array($tabid));
 		$rows = $adb->num_rows($getBotIdQuery);
 		for ($i=0; $i < $rows; $i++) { 
			$fieldname = $adb->query_result($getBotIdQuery, $i, 'fieldname');
			$fieldlabel = $adb->query_result($getBotIdQuery, $i, 'fieldlabel');
			echo "<option value='".$fieldname."'>".vtranslate($fieldlabel,$modulename)."</option>";
 		}
	}

	function getWhatsappBotRelatedModuleFields(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
  		$tabid = $request->get('tabid');  
  		/*$getBotIdQuery = $adb->pquery("SELECT * FROM vtiger_tab  WHERE tabid = ?", array($tabid));
  		$moduleName = $adb->query_result($getBotIdQuery, 0, 'name');*/
  		$relatedModules = array();
  		$results = $adb->pquery("SELECT DISTINCT(vtiger_tab.tabid), vtiger_tab.name, vtiger_relatedlists.actions FROM `vtiger_relatedlists` INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_relatedlists.tabid WHERE vtiger_relatedlists.related_tabid = ? AND vtiger_relatedlists.actions LIKE '%add%'",array($tabid));
  		echo '<option value="">'.vtranslate("Select an option",$moduleName).'</option>';
  		if($adb->num_rows($results)){
  			for ($i=0; $i < $adb->num_rows($results); $i++) { 
  				$tabid = $adb->query_result($results,$i,'tabid');
  				$name = $adb->query_result($results,$i,'name');
  				echo "<option value='".$tabid."'>".vtranslate($name,$name)."</option>";
  			}
  		}
  	
	}

	function addFBFields(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
  		$fieldname = $request->get('fieldname');  
  		$question = $request->get('question');  
  		$iconclass = $request->get('iconclass');  
		$slug=str_replace(" ","-",strtolower(trim($fieldname)));
 		$getBotIdQuery = $adb->pquery("INSERT INTO flowbuilderfields (fieldname, question, iconclass, slug) VALUES (?,?,?,?)", array($fieldname,$question,$iconclass,$slug));
  		 
	}
	function deleteFBFields(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
  		$fieldname = $request->get('fieldname');  
 		$slug=str_replace(" ","-",strtolower(trim($fieldname)));
 		$getBotIdQuery = $adb->pquery("DELETE from flowbuilderfields where slug=?", array($slug));
  		 
	}
	function deleteBot(Vtiger_Request $request){
		global $adb, $current_user;
		$currentUserID = $current_user->id;
		$moduleName = $request->getModule();
  		$botId = $request->get('botId');
  		$adb->pquery("UPDATE ctwhatsapp_bots SET deleted = 1 WHERE botid = ? AND assignuserid = ?", array($botId, $currentUserID));
	}

	function deleteWhatsAppBot(Vtiger_Request $request){
		global $adb, $current_user;
		$currentUserID = $current_user->id;
		$moduleName = $request->getModule();
  		$botId = $request->get('botId');
  		$adb->pquery("UPDATE ctwhatsapp_bots SET remove = 1, deleted = 0 WHERE botid = ? AND assignuserid = ?", array($botId, $currentUserID));
  		$adb->pquery("DELETE FROM whatsappbot_pre_que", array());
	}

	function botActiveDeactive(Vtiger_Request $request){
		global $adb, $current_user;
		$currentUserID = $current_user->id;
		$moduleName = $request->getModule();
  		$botactive = $request->get('botactive');
  		$botid = $request->get('botid');
  		$adb->pquery("UPDATE ctwhatsapp_bots SET deleted = 0 WHERE assignuserid = ?", array($currentUserID));
  		$adb->pquery("UPDATE ctwhatsapp_bots SET deleted = ? WHERE botid = ? AND assignuserid = ?", array($botactive ,$botid, $currentUserID));
  		$adb->pquery("DELETE FROM whatsappbot_pre_que", array());
	}

	function getWhatsappBotStatus(Vtiger_Request $request){
		global $adb;
		$moduleName = $request->getModule();
  		$confugurationQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bot_configuration", array());
  		$whatsappbotstatus = $adb->query_result($confugurationQuery, 0, 'whatsappbotstatus');
  		$whatsappno = $adb->query_result($confugurationQuery, 0, 'whatsappno');

  		$response = new Vtiger_Response();
		$response->setResult(array('whatsappStatus' => $whatsappbotstatus, 'whatsappNo' => $whatsappno));
		$response->emit();
	}

	function scanQRCodeInPopup(Vtiger_Request $request) {
    	global $adb, $current_user, $site_URL;
        $moduleName = $request->getModule();
        $currenUserID = $current_user->id;
        $confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration");
        $num_rows = $adb->num_rows($confugurationQuery);
        if($num_rows){
        	$apiUrl = $adb->query_result($confugurationQuery, 0, 'api_url');
        }

        $selectQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bot_configuration");
    	$row = $adb->num_rows($selectQuery);
    	$whatsappbotstatus = $adb->query_result($selectQuery, 0, 'whatsappbotstatus');
		if($whatsappbotstatus == 0){
			$getexpiredate = $adb->pquery("SELECT * FROM vtiger_ctwhatsapp_license_setting");
			$licenseKey = $adb->query_result($getexpiredate, 0, 'license_key');

	        $qrcodeurl = $apiUrl."/init";
			$fields = array(
				'domain' => $site_URL,
				"url" => $site_URL.'/modules/CTWhatsApp/CTWhatAppReceiverBot.php?userid='.$currenUserID,
				"licenceKey" => 'crmtiger12*',
				"statusurl" => $site_URL.'/modules/CTWhatsApp/WhatsappStatusBot.php?userid='.$currenUserID,
			);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $qrcodeurl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_CONNECTTIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
				CURLOPT_POSTFIELDS => json_encode($fields),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
			  	),
			));
			$result = curl_exec($curl);
			$response = json_decode($result);
			curl_close($curl);
			$qrcodeurl = $response->qr;
			$authTokenKey = $response->Authorization;
			$scanMessage = $response->message;

	        if($authTokenKey){
	        	$selectQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bot_configuration");
	        	$row = $adb->num_rows($selectQuery);
	        	if($row == 1){
	        		$adb->pquery("UPDATE ctwhatsapp_bot_configuration SET authtoken = ?", array($authTokenKey));
	        	}else{
	        		$adb->pquery("INSERT INTO ctwhatsapp_bot_configuration (whatsappno, authtoken) VALUES (?,?)", array('' ,$authTokenKey));
	        	}
	        }
    	}else{
    		$whatsappno = $adb->query_result($confugurationQuery, 0, 'whatsappno');
    		$whatsappbotstatus = $adb->query_result($confugurationQuery, 0, 'whatsappbotstatus');
    	}

        

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SCANQRCODE', $qrcodeurl);
		$viewer->assign('AUTHTOKENKEY', $authTokenKey);
		$viewer->assign('SCANMESSAGE', $scanMessage);
		$viewer->assign('SCANWHATSAPPNO', $whatsappbotstatus);
		$viewer->assign('APIURL', $apiUrl);
		echo $viewer->view('ScanBotQRCodeInPopup.tpl', $moduleName, true);	
    }

    public function logoutWhatsApp(Vtiger_Request $request){
    	global $adb;
    	$confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration");
        $num_rows = $adb->num_rows($confugurationQuery);
        if($num_rows){
        	$apiUrl = $adb->query_result($confugurationQuery, 0, 'api_url');
        }

    	$selectQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bot_configuration");
    	$row = $adb->num_rows($selectQuery);
    	$whatsappbotstatus = $adb->query_result($selectQuery, 0, 'whatsappbotstatus');
    	$authtoken = $adb->query_result($selectQuery, 0, 'authtoken');
    	if($whatsappbotstatus == 1){
    		$logoutURL = $apiUrl.'/disconnect';
			$curlLogout = curl_init();
			curl_setopt_array($curlLogout, array(
				CURLOPT_URL => $logoutURL,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_CONNECTTIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
				CURLOPT_POSTFIELDS => '',
				CURLOPT_HTTPHEADER => array(
			    	'Authorization: '.$authtoken
			  	),
			));
			$resultLogout = curl_exec($curlLogout);
			$responseLogout = json_decode($resultLogout);
			curl_close($curlLogout);
			$adb->pquery("UPDATE ctwhatsapp_bot_configuration SET authtoken = ''", array());

    	}
		header("Location: index.php?module=CTWhatsApp&view=WhatsappBot&mode=WhatsappBotList");
    }

    public function convertToBot(Vtiger_Request $request){
    	global $adb;
    	$phonenumber = $request->get('phonenumber');
    	$date_var = date("Y-m-d H:i:s");
    	$query = $adb->pquery("SELECT * FROM whatsappbot_pre_que WHERE prequemobilenumber LIKE '%".$phonenumber."'", array());
    	$manualtransfer = $adb->query_result($query, 0, 'manualtransfer');
    	if($manualtransfer == 1){
			$adb->pquery("UPDATE whatsappbot_pre_que SET manualtransfer = '0', manualdatetime = '' WHERE prequemobilenumber LIKE '%".$phonenumber."'", array());
			$manualtransferStatus = 0;
    	}else{
    		$adb->pquery("UPDATE whatsappbot_pre_que SET manualtransfer = '1' , manualdatetime = '".$date_var."'WHERE prequemobilenumber LIKE '%".$phonenumber."'", array());
    		$manualtransferStatus = 1;
    	}

    	$response = new Vtiger_Response();
		$response->setResult(array('manualtransferStatus' => $manualtransferStatus));
		$response->emit();
    }

    function whatsAppBotButtonSetting(Vtiger_Request $request) {
		global $adb, $current_user, $root_directory;
		$currentUserID = $current_user->id;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$multipleWhatsAppNumber = CTWhatsApp_Record_Model::getAllUserWhatsappNumber($currentUserID);

		$query = $adb->pquery("SELECT * FROM vtiger_whatsapp_botbutton");
		$hearder_text = $adb->query_result($query, 0, 'hearder_text');
		$hearder_description = $adb->query_result($query, 0, 'hearder_description');
		$header_color = $adb->query_result($query, 0, 'header_color');
		$header_image = $adb->query_result($query, 0, 'header_image');
		$button_text = $adb->query_result($query, 0, 'button_text');
		$button_color = $adb->query_result($query, 0, 'button_color');
		$bg_image = $adb->query_result($query, 0, 'bg_image');

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('HEADERTEXT', $hearder_text);
		$viewer->assign('HEADERDESCRIPTION', $hearder_description);
		$viewer->assign('HEADERCOLOR', $header_color);
		$viewer->assign('HEADERIMAGE', $header_image);
		$viewer->assign('BUTTONTEXT', $button_text);
		$viewer->assign('BUTTONCOLOR', $button_color);
		$viewer->assign('BGCOLOR', $bg_image);
		$viewer->assign('MULTI_WHATSAPPNUM', $multipleWhatsAppNumber);
		$viewer->assign('IMAGEDIRECTORY', '/modules/CTWhatsApp/CTWhatsAppStorage/BotButton/');

		echo $viewer->view('WhatsAppBotButtonSetting.tpl', $moduleName, true);
	}

    function whatsAppBotSetting(Vtiger_Request $request) {
		global $adb, $current_user, $root_directory;
		$currenUserID = $current_user->id;
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$multipleWhatsAppNumber = CTWhatsApp_Record_Model::getAllUserWhatsappNumber($currentUserID);

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('IMAGEDIRECTORY', '/modules/CTWhatsApp/CTWhatsAppStorage/BotButton/');

		echo $viewer->view('WhatsAppBotSetting.tpl', $moduleName, true);
	}

	function getWhatsappBotFlowbuilderField(){
		global $adb;
		$selectFieldsQuery = $adb->pquery("SELECT * FROM flowbuilderfields");
		$rows = $adb->num_rows($selectFieldsQuery);
		$fieldArray=array();
		$html = '';
		for($i=0; $i<$rows; $i++) {
			$fieldArray[]=array(
				'fieldname'=>$adb->query_result($selectFieldsQuery, $i,'fieldname'),
				'slug'=>$adb->query_result($selectFieldsQuery, $i,'slug'),
				'iconclass'=>$adb->query_result($selectFieldsQuery, $i,'iconclass'),
				'question'=>$adb->query_result($selectFieldsQuery, $i,'question'));
			$fieldname = $adb->query_result($selectFieldsQuery, $i,'fieldname');
			$slug = $adb->query_result($selectFieldsQuery, $i,'slug');
			$html.="<option value='$slug'>$fieldname</option>";
		}
		$response = new Vtiger_Response();
		$response->setResult($html);
		$response->emit();
	}

	function SaveWhatsAppBotButton(Vtiger_Request $request) {
		global $adb, $root_directory;
		$moduleName = $request->getModule();
		$hearder_text = $request->get('hearder_text');
		$hearder_description = $request->get('hearder_description');
		$header_color = $request->get('header_color');
		$button_text = $request->get('button_text');
		$button_color = $request->get('button_color');
		$scanned_number = $request->get('scanned_number');
		$header_image = $_FILES['header_image'];
		$bg_image = $_FILES['bg_image'];

		$target = $root_directory.'/modules/CTWhatsApp/CTWhatsAppStorage/BotButton/';

		if($header_image){
			move_uploaded_file($_FILES['header_image']['tmp_name'], $target.$_FILES['header_image']['name']);
		}
		
		if($bg_image){
			move_uploaded_file($_FILES['bg_image']['tmp_name'], $target.$_FILES['bg_image']['name']);
		}

		$query = $adb->pquery("SELECT * FROM vtiger_whatsapp_botbutton");
		$row = $adb->num_rows($query);

		if($row){
			$updateQuery = "UPDATE vtiger_whatsapp_botbutton SET hearder_text = ?, hearder_description = ?, header_color = ?, button_text = ?, button_color = ?, scanned_number = ?";
			$params = array($hearder_text, $hearder_description, $header_color, $button_text, $button_color, $scanned_number);

			if($_FILES['header_image']['name']){
				$updateQuery .= ", header_image = ?";
				array_push($params, $_FILES['header_image']['name']);
			}

			if($_FILES['bg_image']['name']){
				$updateQuery .= ", bg_image = ?";
				array_push($params, $_FILES['bg_image']['name']);
			}

			$adb->pquery($updateQuery." WHERE 1", array($params));

		}else{
			$adb->pquery("INSERT INTO vtiger_whatsapp_botbutton (hearder_text, hearder_description, header_color, header_image, button_text, button_color, bg_image,scanned_number) VALUES (?,?,?,?,?,?,?,?)", array($hearder_text, $hearder_description, $header_color, $_FILES['header_image']['name'], $button_text, $button_color, $_FILES['bg_image']['name'],$scanned_number));
		}


		header("Location: index.php?module=CTWhatsApp&view=WhatsappBot&mode=whatsAppBotButtonSetting");
	}

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request){
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.$moduleName.resources.WhatsappBot",
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances,$headerScriptInstances);
        return $headerScriptInstances;
    }
}





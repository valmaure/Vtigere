<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

class CTWhatsApp_Comments_View extends Vtiger_IndexAjax_View {

	function __construct() {
		$this->exposeMethod('commentsPopup');
		$this->exposeMethod('saveComments');
	}

	function commentsPopup(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$moduleName);
		echo $viewer->view('CommentsPopup.tpl', $moduleName, true);
	}
	
	function saveComments(Vtiger_Request $request) {
		global $adb, $current_user;
		$moduleName = $request->getModule();
		$userid = $current_user->id;
		$recordid = $request->get('recordid');
		$datefilter = $request->get('datefilter');
		if($datefilter == "today"){
			$startdate = date("Y-m-d");
			$enddate = date("Y-m-d");
		}else if($datefilter == "yesterday"){
			$startdate = date('Y-m-d',strtotime( "yesterday" ));
			$enddate = date('Y-m-d',strtotime( "yesterday" ));
		}else if($datefilter == "this_week"){
			$saturday = strtotime("last sunday");
			$saturday = date('w', $saturday)==date('w') ? $saturday+7*86400 : $saturday;
			$friday = strtotime(date("Y-m-d",$saturday)." +6 days");

			$startdate = date("Y-m-d",$saturday);
			$enddate = date("Y-m-d",$friday);
		}else if($datefilter == "last_week"){
			$saturday = strtotime("-1 week last sunday");
			$friday = strtotime(date("Y-m-d",$saturday)." +6 days");
			
			$startdate = date("Y-m-d",$saturday);
			$enddate = date("Y-m-d",$friday);
		}else if($datefilter == "this_month"){
			$startdate = date('Y-m-d',strtotime("first day of this month"));
			$enddate = date('Y-m-d',strtotime("last day of this month"));
			
		}else if($datefilter == "last_month"){
			$startdate = date("Y-m-d", strtotime("first day of last month"));
            $enddate = date("Y-m-d", strtotime("last day of last month"));
		}else{
			$customdate = explode(',', $request->get('customdate'));
			$startdate = DateTimeField::convertToDBFormat($customdate[0]);
			$enddate = DateTimeField::convertToDBFormat($customdate[1]);
		}
		
		$getWhatsAppRecords = CTWhatsApp_Record_Model::getWhatsAppRecordQuery($startdate, $enddate);
		$query = $adb->pquery($getWhatsAppRecords, array($recordid));
		
		$numrows = $adb->num_rows($query);
		$commententry = $request->get('commententry');
		if($commententry == "single"){
			$multiple_comment = '';
			for($i=0; $i < $numrows; $i++){
				$multiple_comment .= '['.$adb->query_result($query, $i, 'createdtime').'] '.$adb->query_result($query, $i, 'whatsapp_sendername').' : '.$adb->query_result($query, $i, 'message_body')."\n";
			}
			$recordModel = Vtiger_Record_Model::getCleanInstance("ModComments");
			$recordModel->set('mode', '');
			$recordModel->set('commentcontent', $multiple_comment);
			$recordModel->set('assigned_user_id', $userid);
			$recordModel->set('related_to', $recordid);
			$recordModel->set('userid', $userid);
			$recordModel->save();
		}else{
			for($i=0; $i < $numrows; $i++){
				$message_body = '['.$adb->query_result($query, $i, 'createdtime').'] '.$adb->query_result($query, $i, 'whatsapp_sendername').' : '.$adb->query_result($query, $i, 'message_body');
				$related_to = $adb->query_result($query, $i, 'whatsapp_contactid');
				
				$recordModel = Vtiger_Record_Model::getCleanInstance("ModComments");
				$recordModel->set('mode', '');
				$recordModel->set('commentcontent', $message_body);
				$recordModel->set('assigned_user_id', $userid);
				$recordModel->set('related_to', $related_to);
				$recordModel->set('userid', $userid);
				$recordModel->save();
			}
		}
	}
}

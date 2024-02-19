<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/
class CTWhatsApp_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getDefaultViewName() {
		return 'DashBoard';
	}

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getDefaultViewName().'&mode=moduleDashBoard&analytics=1';
	}

	public function getMobileNumber($mobileno, $customfield1){
		global $adb;
		$getCountrycodeQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappconfiguration");
		$customfield1 = $adb->query_result($getCountrycodeQuery, 0, 'customfield1');
		
		$mobilenoLen = strlen($mobileno);
		if($mobilenoLen > 10 && $customfield1 !=''){
			$withoutcode = substr($mobileno,-10);
			$mobileno = $customfield1.$withoutcode;
		}else{
			$mobileno = $customfield1.$mobileno;
		}
		return $mobileno;
	}

	public function updateWhatsappScanNumber($getWhatsappStatus, $getLowerStatus, $whatsappNumber, $userid, $msgid, $status){
		global $adb;
		if($getWhatsappStatus){
		    if($getLowerStatus == 'connected'){
		        $getStatus = 1;
		        if($userid == 'yes'){
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($whatsappNumber, $getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($whatsappNumber, $getStatus));

		        }else{
		            if($whatsappNumber == ''){
		                $configuratioData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($userid);
		                $whatsappNumber = $configuratioData['whatsappno'];
		            }

		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = ?",array($whatsappNumber, $getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = ?",array($whatsappNumber, $getStatus, $userid));

		        }
		    }else if($getLowerStatus == 'timeout'){
		        $getStatus = 2;
		        if($userid == 'yes'){
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		        }else{
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		        }
		    }else if($getLowerStatus == 'disconnected'){
		        $getStatus = 0;
		        if($userid == 'yes'){
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET  whatsappno = '', whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		        }else{
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappusers SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		        }
		    }
		}else{
		    
		    if($msgid != '' && $status == '4'){
		    	$getIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsapp INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapp.ctwhatsappid WHERE vtiger_ctwhatsapp.msgid = ?", array($msgid));
		    	$row = $adb->num_rows($getIdQuery);
		    	if($row){
		    		$ctwhatsappid = $adb->query_result($getIdQuery, 0, 'ctwhatsappid');
		    		if($ctwhatsappid){
		    			$whatsappRecordModel = Vtiger_Record_Model::getInstanceById($ctwhatsappid, 'CTWhatsApp');
		    			$whatsappRecordModel->set('mode', 'edit');
		    			$whatsappRecordModel->set('id', $ctwhatsappid);
		    			$whatsappRecordModel->set('whatsapp_unreadread', 'Read');
		    			$whatsappRecordModel->save();
		    		}
		    	}
		    }
		}
	}

	public function autoResponderUpdate($request){
		global $adb, $current_user;
    	$autoresponderMessage = $request->get('autoresponderMessage');
    	$userID = $current_user->id;
    	
    	$query = $adb->pquery("UPDATE vtiger_ctwhatsappconfiguration SET customfield6 = '1',customfield7 = ? WHERE customfield5 = ?", array($autoresponderMessage, $userID));
    	$query = $adb->pquery("UPDATE vtiger_ctwhatsappusers SET customfield6 = '1',customfield7 = ? WHERE customfield5 = ?", array($autoresponderMessage, $userID));
	}

}

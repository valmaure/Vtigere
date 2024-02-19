<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/
class CTWhatsAppBusiness_Module_Model extends Vtiger_Module_Model {

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
		$getCountrycodeQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
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
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($whatsappNumber, $getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($whatsappNumber, $getStatus));

		        }else{
		            if($whatsappNumber == ''){
		                $configuratioData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($userid);
		                $whatsappNumber = $configuratioData['whatsappno'];
		            }

		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = ?",array($whatsappNumber, $getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappno = ?, whatsappstatus = ? WHERE customfield5 = ?",array($whatsappNumber, $getStatus, $userid));

		        }
		    }else if($getLowerStatus == 'timeout'){
		        $getStatus = 2;
		        if($userid == 'yes'){
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		        }else{
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		        }
		    }else if($getLowerStatus == 'disconnected'){
		        $getStatus = 0;
		        if($userid == 'yes'){
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET  whatsappno = '', whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappstatus = ? WHERE customfield5 = 'whatsappBot'",array($getStatus));
		        }else{
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		            $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappstatus = ? WHERE customfield5 = ?",array($getStatus, $userid));
		        }
		    }
		}else{
		    
		    if($msgid != '' && $status == '4'){
		        $updateWhatsappRecord = $adb->pquery("UPDATE vtiger_ctwhatsappbusiness SET whatsapp_unreadread = 'Read' WHERE msgid = ?",array($msgid));
		    }
		}
	}

	public function autoResponderUpdate($request){
		global $adb, $current_user;
    	$autoresponderMessage = $request->get('autoresponderMessage');
    	$userID = $current_user->id;
    	
    	$query = $adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET customfield6 = '1',customfield7 = ? WHERE customfield5 = ?", array($autoresponderMessage, $userID));
    	$query = $adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET customfield6 = '1',customfield7 = ? WHERE customfield5 = ?", array($autoresponderMessage, $userID));
	}

}

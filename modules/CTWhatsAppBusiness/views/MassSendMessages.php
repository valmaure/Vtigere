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
class CTWhatsAppBusiness_MassSendMessages_View extends Vtiger_IndexAjax_View {

	function __construct() {
		$this->exposeMethod('sendMessagePopup');
		$this->exposeMethod('sendMessage');
		$this->exposeMethod('getTemplateData');
		$this->exposeMethod('showBanner');
	}

	function checkPermission() { }

	function getTemplateData(Vtiger_Request $request){
		$whatsAppTemplateData = CTWhatsAppBusiness_Record_Model::getWhatsAppTemplateData($request);

		$response = new Vtiger_Response();
		$response->setResult($whatsAppTemplateData);
		$response->emit();
	}
	
	function sendMessagePopup(Vtiger_Request $request){
		global $adb,$current_user;
		$dateFormat = $current_user->date_format;
		$hourFormat = $current_user->hour_format;
		$current_user = $current_user->id;
		$moduleName = $request->getModule();
		$selected_ids = $request->get('selected_ids');
		$sourceModule = $request->get('source_module');
		$viewer = $this->getViewer($request);
		
		$tabid = getTabid($sourceModule);
		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$fields = $moduleModel->getFields();
		$allFields = array();
		foreach ($fields as $key => $value) {
			$fieldlabel = $value->label;
			$columnname = $value->name;
			$allFields[$fieldlabel] = $columnname;
		}
		
		$whatsappModuleFieldsData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($sourceModule);
		$phoneField = $whatsappModuleFieldsData['phoneField'];
		
		$phonefield = CTWhatsAppBusiness_Record_Model::getPhoneFieldLabel($tabid, $phoneField);
		
		$templatesArray = WhatsAppBusinessTemplates_Record_Model::getWhatsappTemplates($sourceModule);
		$multipleWhatsappNumber = CTWhatsAppBusiness_Record_Model::getAllUserWhatsappNumber($current_user);

		
		$viewer->assign('WHATSAPPTEMPLATES', $templatesArray);
		$viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SELECTED_IDS', $selected_ids);
		$viewer->assign('PHONEFIELD', $phonefield);
		$viewer->assign('ALLFIELDS', $allFields);
		$viewer->assign('SELECTMODULE', strtolower($sourceModule));
		$viewer->assign('DATEFORMAT', $dateFormat);
		$viewer->assign('TIMEFORMAT', $hourFormat);
		echo $viewer->view('SendMessagePopup.tpl', $moduleName, true);
	}

	function sendMessage(Vtiger_Request $request){
		$sendMassMessagesData = CTWhatsAppBusiness_Record_Model::sendMassMessages($request);
	}

	function showBanner(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$bannerHTML = $request->get('bannerHTML');
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('BANNER', $bannerHTML);
		echo $viewer->view('ShowBanner.tpl', $moduleName, true);
	}
}

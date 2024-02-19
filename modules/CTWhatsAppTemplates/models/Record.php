<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/
class CTWhatsAppTemplates_Record_Model extends Vtiger_Record_Model {
	
	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails($recordId) {
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = urlencode(decode_html($imageName));

			if(!empty($imageName)){
				$imageDetails[] = array(
						'id' => $imageId,
						'orgname' => $imageOriginalName,
						'path' => $imagePath.$imageId,
						'name' => $imageName
				);
			}
		}
		return $imageDetails;
	}

	public function getModulefields($request){
		global $adb;
		$sourceModuleName = $request->get('sourcemodulename');
		$tabid = getTabid($sourceModuleName);
		
		$getModuleFields = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ?", array($tabid));
		$rows = $adb->num_rows($getModuleFields);
		$modulesFieldshtml = '<option value="">'.vtranslate('LBL_SELECT_OPTION','Vtiger').'</option>';
		for ($i=0; $i < $rows; $i++) { 
			$fieldName = $adb->query_result($getModuleFields, $i, 'columnname');
			$fieldLabel = $adb->query_result($getModuleFields, $i, 'fieldlabel');
			if($fieldName != 'source' && $fieldName != 'starred' && $fieldName != 'tags'){
				$modulesFieldshtml .= '<option value='.strtolower($sourceModuleName).'-'.$fieldName.'>'.vtranslate($fieldLabel,$fieldLabel).'</option>';
			}
		}

		return $modulesFieldshtml;
	}

	function getWhatsappTemplates($source_module){
		global $adb;
		$getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsapptemplates INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapptemplates.wptemplate_modules = ?", array($source_module));
		$whatsappTemplateRows = $adb->num_rows($getWhatsappTemplateQuery);
		$templatesArray = array();
		for ($j=0; $j < $whatsappTemplateRows; $j++) { 
			$templatesID = $adb->query_result($getWhatsappTemplateQuery, $j, 'ctwhatsapptemplatesid');
			$templateTitle = $adb->query_result($getWhatsappTemplateQuery, $j, 'wptemplate_title');
			$templatesArray[$templatesID] = $templateTitle;
		}
		return $templatesArray;
	}

	public function getWhatsappAllowAllModules(){
		global $adb;
		$whatsappModuleQuery = $adb->pquery("SELECT * FROM vtiger_ctwharsappallow_whatsappmodule WHERE active = 1");
		$rows = $adb->num_rows($whatsappModuleQuery);
		
		$whatsaappModule = array();
		for ($i=0; $i < $rows; $i++) { 
			$module = $adb->query_result($whatsappModuleQuery, $i, 'module');
			$data = CTWhatsApp_Record_Model::checkPermissionModule($module);
			if($data == 1){
				$moduleIsEnable = CTWhatsApp_Record_Model::getmoduleIsEnable($module);

				if($moduleIsEnable == 0){
					$whatsaappModuleData = CTWhatsApp_Record_Model::getWhatsappAllowModuleFields($module);
					$phoneField = $whatsaappModuleData['phoneField'];
					$serach = '';
					$whatsaappModule[] = array('module' => $module, 'phoneField' => $phoneField);
				}
			}
		}
		return $whatsaappModule;
	}
}
<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

class CTWhatsAppTemplates_WhatsAppTemplatesData_View extends Vtiger_IndexAjax_View {

	function __construct() {
		$this->exposeMethod('getModuleFields');
		$this->exposeMethod('getWhatsappTemplates');
		$this->exposeMethod('getWhatsappTemplateInWorkflow');
	}

	function getModuleFields(Vtiger_Request $request) { 
		global $adb;
		$moduleName = $request->getModule();
		$modulesFields = CTWhatsAppTemplates_WhatsAppTemplatesData_View::getAllModuleEmailTemplateFields($request);
		$modulesFieldshtml = '<option value="">'.vtranslate('LBL_SELECT_OPTION','Vtiger').'</option>';
		foreach ($modulesFields as $key => $value) {
			$modulesFieldshtml .= '<option value='.$value[1].'>'.$value[0].'</option>';
		}
		$fieldData = array('modulesFieldshtml' => $modulesFieldshtml);
		
		$response = new Vtiger_Response();
		$response->setResult($fieldData);
		$response->emit();
	}

	function getWhatsappTemplateInWorkflow(Vtiger_Request $request){
		$moduleName = $request->get('moduleName');
		$templatesArray = CTWhatsAppTemplates_Record_Model::getWhatsappTemplates($moduleName);
		$workflowEhatsappTemplate = '<option value="">'.vtranslate('LBL_SELECT_OPTION','Vtiger').'</option>';
		foreach ($templatesArray as $key => $value) {
			$workflowEhatsappTemplate .= '<option value='.$key.'>'.$value.'</option>';
		}
		$response = new Vtiger_Response();
		$response->setResult($workflowEhatsappTemplate);
		$response->emit();
	}

    public function getAllModuleEmailTemplateFields($request) {
    	$sourceModuleName = $request->get('sourcemodulename');
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $allRelFields = array();
        
        $fieldList = $this->getRelatedFields($sourceModuleName, $currentUserModel);
        
        $allFields = array();
        foreach ($fieldList as $key => $field) {
            $option = array(vtranslate($field['module'], $field['module']) . ':' . vtranslate($field['fieldlabel'], $field['module']), "$" . strtolower($field['module']) . "-" . $field['columnname'] . "$");
            $allFields[] = $option;
            if (!empty($field['referencelist'])) {
                foreach ($field['referencelist'] as $referenceList) {
                    foreach($referenceList as $key => $relField) {
                    $relOption = array(vtranslate($field['fieldlabel'], $field['module']) . ':' . '(' . vtranslate($relField['module'], $relField['module']) . ')' . vtranslate($relField['fieldlabel'],$relField['module']), strtolower($field['module']) . "-" . $field['columnname'] . ":" . $relField['columnname']);
                    $allRelFields[] = $relOption;
                }
            }
        }
        }
        if(is_array($allFields) && is_array($allRelFields)){
            $allFields = array_merge($allFields, $allRelFields);
            $allRelFields= array();
        }
        return $allFields;
    }

    function getRelatedFields($module, $currentUserModel) {
        $handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
        $meta = $handler->getMeta();
        $moduleFields = $meta->getModuleFields();
        $db = PearDatabase::getInstance();
        //adding record id merge tag option 
        $fieldInfo = array('columnname' => 'id','fieldname' => 'id','fieldlabel' =>vtranslate('LBL_RECORD_ID', $module));
        $recordIdField = WebserviceField::fromArray($db, $fieldInfo);
        $moduleFields[$recordIdField->getFieldName()] = $recordIdField;

        $returnData = array();
        foreach ($moduleFields as $key => $field) {
            if(!in_array($field->getPresence(), array(0,2))){
                continue;
            }
            $referencelist = array();
            $relatedField = $field->getReferenceList();
            if ($field->getFieldName() == 'assigned_user_id') {
                $relModule = 'Users';
                $referencelist[] = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
            }
            if (!empty($relatedField)) {
                foreach ($relatedField as $ind => $relModule) {
                    $referencelist[] = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
                }
            }
            $returnData[] = array('module' => $module, 'fieldname' => $field->getFieldName(), 'columnname' => $field->getColumnName(), 'fieldlabel' => $field->getFieldLabelKey(), 'referencelist' => $referencelist);
        }
        return $returnData;
    }

    function getRelatedModuleFieldList($relModule, $user) {
        $handler = vtws_getModuleHandlerFromName($relModule, $user);
        $relMeta = $handler->getMeta();
        if (!$relMeta->isModuleEntity()) {
            return array();
        }
        $relModuleFields = $relMeta->getModuleFields();
        $relModuleFieldList = array();
        foreach ($relModuleFields as $relind => $relModuleField) {
            if(!in_array($relModuleField->getPresence(), array(0,2))){
                continue;
            }
            if($relModule == 'Users') {
                if(in_array($relModuleField->getFieldDataType(),array('string','phone','email','text'))) {
                    $skipFields = array(98,115,116,31,32);
                    if(!in_array($relModuleField->getUIType(), $skipFields) && $relModuleField->getFieldName() != 'asterisk_extension'){
                        $relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
                    }
                }
            } else {
                $relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
            }
        }
        return $relModuleFieldList;
    }
}

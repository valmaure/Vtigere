<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CTWhatsAppTemplates_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		global $adb,$current_user;
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$recordModel = $this->record;
        $currenUserID = $current_user->id;
             
		$viewer = $this->getViewer($request);
		if(!$recordModel){
			if (!empty($recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			} else {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			}
			$this->record = $recordModel;
		}
		$allUserNumber = CTWhatsApp_Record_Model::getAllConnectedWhatsappNumber($currenUserID);
		$viewer->assign('ALL_USER_CONNECTED_NUMBER', $allUserNumber);
		$selectModule = $recordModel->get('wptemplate_modules');
		$getModules = Vtiger_Module_Model::getAll();
		$moduleArray = array();
		foreach ($getModules as $key => $value) {
			$sourceModuleName = $value->name;
			$moduleArray[$sourceModuleName] = vtranslate($sourceModuleName, $sourceModuleName);
		}

		$viewer->assign('ALLMODULES', $moduleArray);
		$viewer->assign('SELECTMODULE', $selectModule);
		$viewer->assign('IMAGE_DETAILS', CTWhatsAppTemplates_Record_Model::getImageDetails($recordId));

		parent::process($request);
	}
	
}

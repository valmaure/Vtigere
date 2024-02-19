<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class CTWhatsAppTemplates extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_ctwhatsapptemplates';
	var $table_index= 'ctwhatsapptemplatesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ctwhatsapptemplatescf', 'ctwhatsapptemplatesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ctwhatsapptemplates', 'vtiger_ctwhatsapptemplatescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ctwhatsapptemplates' => 'ctwhatsapptemplatesid',
		'vtiger_ctwhatsapptemplatescf'=>'ctwhatsapptemplatesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'ctWhatsAppTemplates No' => Array('ctwhatsapptemplates', 'ctwhatsapptemplates_no'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'ctWhatsAppTemplates No' => 'ctwhatsapptemplates_no',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'ctwhatsapptemplates_no';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'ctWhatsAppTemplates No' => Array('ctwhatsapptemplates', 'ctwhatsapptemplates_no'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'ctWhatsAppTemplates No' => 'ctwhatsapptemplates_no',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('ctwhatsapptemplates_no');

	// For Alphabetical search
	var $def_basicsearch_col = 'ctwhatsapptemplates_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'ctwhatsapptemplates_no';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('ctwhatsapptemplates_no','assigned_user_id');

	var $default_order_by = 'ctwhatsapptemplates_no';
	var $default_sort_order='ASC';

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
 			// TODO Handle actions after this module is installed.
 			$this->insertWSEntity();
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
			$this->insertWSEntity();
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
			$this->insertWSEntity();
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
			$this->insertWSEntity();
		}
 	}

 	private function insertWSEntity() {
        $adb = PearDatabase::getInstance();
        $getentity =$adb->pquery("SELECT * FROM vtiger_ws_entity WHERE name = ?", array('CTWhatsAppTemplates'));
		if($adb->num_rows($getentity) != 1){
			$seq = $adb->pquery("SELECT * FROM vtiger_ws_entity_seq", array());
			$id = $adb->query_result($seq, 0, 'id');
			$seq = $id + 1;
			$adb->pquery("INSERT INTO vtiger_ws_entity (id, name, handler_path, handler_class, ismodule) VALUES ($seq, 'CTWhatsAppTemplates', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1')");
			$adb->pquery("UPDATE vtiger_ws_entity_seq SET id = ?",$seq);
		}
	}

 	function save_module($module){
		$this->insertIntoAttachment($this->id,$module);
	}

	/**
	 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	*/
	function insertIntoAttachment($id,$module){
		global $log, $adb;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		if(count($_FILES)) {
			foreach($_FILES as $fileindex => $files){
				if($files['name'] != '' && $files['size'] > 0){
					$filename = $files['name'];
					$files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
					$file_saved = $this->uploadAndSaveFile($id,$module,$files);
				}
			}
		}
		if($filename){
			$query = "UPDATE vtiger_ctwhatsapptemplates SET wptemplate_image = ? WHERE ctwhatsapptemplatesid = ?";
			$re=$adb->pquery($query,array(decode_html($filename), $this->id));
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}
}
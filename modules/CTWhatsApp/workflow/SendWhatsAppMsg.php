<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTSimpleTemplate.inc');

require_once('modules/CTWhatsApp/CTWhatsApp.php');

class SendWhatsAppMsg extends VTTask {
	public $executeImmediately = true; 
	
	public function getFieldNames(){
		return array('content', 'sms_recepient');
	}
	
	public function doTask($entity){
		global $adb, $current_user,$log;
		
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$ws_id = $entity->getId();
		$entityCache = new VTEntityCache($admin);
		
		$et = new VTSimpleTemplate($this->sms_recepient);
		$recepient = $et->render($entityCache, $ws_id);
		$recepients = explode(',',$recepient);
		$relatedIds = $this->getRelatedIdsFromTemplate($this->sms_recepient, $entityCache, $ws_id);
		$relatedIds = explode(',', $relatedIds);
		$relatedIdsArray = array();
		foreach ($relatedIds as $entityId) {
			if (!empty($entityId)) {
				list($moduleId, $recordId) = vtws_getIdComponents($entityId);
				if (!empty($recordId)) {
					$relatedIdsArray[] = $recordId;
				}
			}
		}

		$ct = new VTSimpleTemplate($this->content);
		$content = $ct->render($entityCache, $ws_id);
		$relatedCRMid = substr($ws_id, stripos($ws_id, 'x')+1);
		$relatedIdsArray[] = $relatedCRMid;

		$relatedid = $entity->id;
		
		$relatedModule = $entity->getModuleName();
		
		/** Pickup only non-empty numbers */
		$tonumbers = array();
		foreach($recepients as $tonumber) {
			if(!empty($tonumber)) $tonumbers[] = $tonumber;
		}
		
		if(!empty($tonumbers)){
			$sendMessagewpnumber = $this->summary;
			$this->smsNotifierId = CTWhatsApp::sendsms(strip_tags($content), $tonumbers, $current_user->id, $relatedid, $sendMessagewpnumber);
		}
		$util->revertUser();
		
	}

	public function getRelatedIdsFromTemplate($template, $entityCache, $entityId) {
		$this->template = $template;
		$this->cache = $entityCache;
		$this->parent = $this->cache->forId($entityId);
		return preg_replace_callback('/\\$(\w+|\((\w+) : \(([_\w]+)\) (\w+)\))/', array($this,"matchHandler"), $this->template);
	}

	public function matchHandler($match) {
		preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $match[1], $matches);
		// If parent is empty then we can't do any thing here
		if(!empty($this->parent)){
			if(count($matches) != 0){
				list($full, $referenceField, $referenceModule, $fieldname) = $matches;
				$referenceId = $this->parent->get($referenceField);
				if($referenceModule==="Users" || $referenceId==null){
					$result ="";
				} else {
					$result = $referenceId;
				}
			}
		}
		return $result;
	}
}
?>

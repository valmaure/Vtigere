<?php

/*
 * Copyright (C) www.vtiger.com. All rights reserved.
 * @license Proprietary
 */
class VTEStore_ActionAjax_Action extends Vtiger_IndexAjax_View {

    protected $modelInstance;

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getDataForVTPremiumIcon');
    }

    function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    protected function getModelInstance() {
        if(!isset($this->modelInstance)){
            $this->modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
        }
        return $this->modelInstance;
    }

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

    protected function getDataForVTPremiumIcon(Vtiger_Request $request) {
        $modelInstance = new VTEStore_VTEModule_Model();
        $VTPremiumHeader=$modelInstance->getSystemInfo();

        $response = new Vtiger_Response();
        $response->setResult(array('VTPremiumHeader' => $VTPremiumHeader));
        $response->emit();
        exit();
    }
}

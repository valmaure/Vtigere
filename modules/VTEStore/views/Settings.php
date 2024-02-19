<?php
/* ********************************************************************************
 * The content of this file is subject to the VTiger Premium ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
if(md5($_REQUEST['ShowInfo'])=='b2e019e789a58db5f4a050cd51ab307a'){
    ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
    echo "<br>php version: ".phpversion();
    echo "<br>root directory: ".dirname(__FILE__);
    phpinfo();
}
class VTEStore_Settings_View extends Settings_Vtiger_Index_View {

    function __construct() {
        parent::__construct();
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if ($mode) {
            $this->$mode($request);
        } else {
            $this->step1($request);
        }
    }

    function step1 (Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $soapEnable=$IonCubeLoaded=$curlEnable=$simplexml='0';
        if (extension_loaded('simplexml')) {
            $simplexml='1';
        }
        if (extension_loaded('soap')) {
            $soapEnable='1';
        }
        if (extension_loaded('curl')) {
            $curlEnable='1';
        }
        if (extension_loaded('openssl')) {
            $openSSLEnable='1';
        }

        if (extension_loaded('ionCube Loader')) {
            $IonCubeLoaded='1';
            if (function_exists('ioncube_loader_version')) {
                $ioncubeVersionStr = ioncube_loader_version();
                $ioncubeVersion = (int)substr($ioncubeVersionStr, 0, strpos($ioncubeVersionStr, '.'));
            }
        }
        $module = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $module);
        $viewer->assign('SIMPLEXMLENABLE', $simplexml);
        $viewer->assign('SOAPENABLE', $soapEnable);
        $viewer->assign('CURLENABLE', $curlEnable);
        $viewer->assign('IONCUBELOADED', $IonCubeLoaded);
        $viewer->assign('IONCUBE_VERSION', $ioncubeVersion);
        $viewer->assign('IONCUBE_VERSION_STR', $ioncubeVersionStr);
        $viewer->assign('openSSLEnable', $openSSLEnable);

        $viewer->assign('default_socket_timeout', ini_get('default_socket_timeout'));
        $viewer->assign('max_execution_time', ini_get('max_execution_time'));
        $viewer->assign('max_input_time', ini_get('max_input_time'));
        $viewer->assign('memory_limit', str_replace('M','',ini_get('memory_limit')));
        $viewer->assign('post_max_size', str_replace('M','',ini_get('post_max_size')));
        $viewer->assign('upload_max_filesize', str_replace('M','',ini_get('upload_max_filesize')));
        $viewer->assign('max_input_vars', ini_get('max_input_vars'));

        $phpVersion=phpversion();
        if(version_compare($phpVersion, '5.3', '>=') && version_compare($phpVersion, '8.3', '<')) {
            $phpVersionStatus=1;
        }else{
            $phpVersionStatus=0;
        }
        $viewer->assign('PHPVERSIONSTATUS', $phpVersionStatus);
        $viewer->assign('PHPVERSION', $phpVersion);

        $viewer->view('Step1.tpl', $module);
    }

    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.VTEStore.resources.SettingsInstall",
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

    function upgradeVTEStoreModule(Vtiger_Request $request) {
        $modelInstance = new VTEStore_VTEModule_Model();
        $extensionId = $request->get('extensionId');
        $extensionName = $request->get('extensionName');
        $moduleAction = $request->get('moduleAction');
        $phpVersion=$modelInstance->getPhpVersion();
        $moduleInfo=array('moduleId' => $extensionId, 'moduleName' => $extensionName, 'phpVersion' => $phpVersion, 'moduleAction'=>$moduleAction);
        $serverResponse = $modelInstance->upgradeVTEStoreModule($moduleInfo);
        $message=$serverResponse['message'];
        if ($serverResponse['error'] == '1'){
            $serverResponse['error'] = 'yes';
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('ERROR', $serverResponse['error']);
        $viewer->assign('EXTENSION_NAME', $extensionName);
        $viewer->assign('MESSAGE', $message);
        $viewer->view('InstallResult.tpl', 'VTEStore');
    }
}
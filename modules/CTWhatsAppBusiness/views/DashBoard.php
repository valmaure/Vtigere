<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/

class CTWhatsAppBusiness_DashBoard_View extends Vtiger_Index_View {

    function __construct() {
        $this->exposeMethod('moduleDashBoard');
        $this->exposeMethod('sendqueueMessages');
        $this->exposeMethod('getWhatsappMessage');
        $this->exposeMethod('pauseResumeMessage');
        $this->exposeMethod('deleteMassMessage');
        $this->exposeMethod('previewMassMessage');
        $this->exposeMethod('updateAuthenticationCode');
        $this->exposeMethod('getWhatsappStatus');
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        if(!Users_Privileges_Model::isPermitted($moduleName, $actionName)) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        if(!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
        return;
    }

    function getLicenseDetail(){
        $licenseDetail = CTWhatsAppBusiness_Record_Model::getWhatsAppLicenseDetail();
        return $licenseDetail;
    }


    function moduleDashBoard(Vtiger_Request $request) {
        global $adb, $site_URL, $current_user;
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $analytics = $request->get('analytics');
        $displayQRCode = $request->get('showqrcode');

        $getMassBatchConfiguration = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::getMassBatchConfigurationData();
        $batch = $getMassBatchConfiguration['batch'];
        $timeInterval = $getMassBatchConfiguration['timeInterval'];

        $currenUserID = $current_user->id;
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $adminApiUrl = $configurationData['api_url'];
        $whatsappNo = $configurationData['whatsappno'];
        $contryCode = $configurationData['customfield1'];
        $iconActive = $configurationData['iconactive'];
        $authToken = $configurationData['authtoken'];
        $allocatedWhatsappUser = $configurationData['customfield3'];
        $whatsappStatus = $configurationData['whatsappstatus'];

        $whatsAppBot = CTWhatsAppBusiness_Record_Model::getAllWhatsAppBot();

        /*$multipleWhatsappNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedWhatsappNumber($currenUserID);
        foreach ($multipleWhatsappNumber as $key => $value) {
            if($value['whatsappstatus'] == 2){
                $noInternetNumber = $value['whatsappno'];
                break;
            }
        }*/

        $multipleWhatsappNumber = CTWhatsAppBusiness_Record_Model::getConnectedWhatsAppNumber();

        $getLicenseDetail = CTWhatsAppBusiness_DashBoard_View::getLicenseDetail();
        $licenseKey = $getLicenseDetail['licenseKey'];

        $showQRCode = 'index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&showqrcode=1';
        $qrCodeScan = 'index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&qrcode_status=1&showqrcode=1';
        $logout = 'index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&whatsapp_action=logout&showqrcode=1';
        $analyticsURL = 'index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG';

        $isAdmin = $current_user->is_admin;

        $qrcodeStatus = $request->get('qrcode_status');
        $qrcodeAction = $request->get('whatsapp_action');

        /*if($qrcodeAction == 'logout'){
            $logoutURL = $adminApiUrl.'/disconnect';
            $curlLogout = curl_init();
            curl_setopt_array($curlLogout, array(
                CURLOPT_URL => $logoutURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
                CURLOPT_POSTFIELDS => '',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: '.$authToken
                ),
            ));
            $resultLogout = curl_exec($curlLogout);
            $responseLogout = json_decode($resultLogout);
            curl_close($curlLogout);

            CTWhatsAppBusiness_Record_Model::updateWhatsAppSatatus($currenUserID);
         
            $getOtherUsers = $adb->pquery("SELECT * FROM vtiger_users2group WHERE userid = ?", array($currenUserID));
            $groupid = $adb->query_result($getOtherUsers, 0, 'groupid');
            CTWhatsAppBusiness_Record_Model::updateWhatsAppSatatus($groupid);
            $getGroupUsers = $adb->pquery("SELECT * FROM vtiger_users2group WHERE groupid = ?", array($groupid));
            $numRows = $adb->num_rows($getGroupUsers);
            for ($i=0; $i < $numRows; $i++) { 
                $userid = $adb->query_result($getGroupUsers, $i, 'userid');
                CTWhatsAppBusiness_Record_Model::updateWhatsAppSatatus($userid);
            }
            header("Location: index.php?module=CTWhatsAppBusiness&view=WhatsappChat&mode=allWhatsAppMSG");
        }

        if($qrcodeStatus == 1){
            $configurationUserData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
            $apiUrl = $configurationUserData['api_url'];
            $whatsappStatus = $configurationUserData['whatsappstatus'];
            $whatsappNo = $configurationData['whatsappno'];

            $qrcodeurl = $apiUrl."/init";
            $fields = array(
                "url" => $site_URL.'/modules/CTWhatsAppBusiness/CTWhatAppReceiver.php',
                "licenceKey" => $licenseKey,
                "statusurl" => $site_URL.'/modules/CTWhatsAppBusiness/WhatsappStatus.php?userid='.$currenUserID,
            );

            foreach($fields as $key=>$value) { $fieldsString .= $key.'='.$value.'&'; }
            rtrim($fieldsString, '&');

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $qrcodeurl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
                CURLOPT_POSTFIELDS => $fieldsString,
            ));
            $result = curl_exec($curl);
            $response = json_decode($result);
            curl_close($curl);
            $qrcodeurl = $response->qr;
            $authTokenKey = $response->key;
        }*/

        $settingModule = 'Settings:'.$moduleName;

        $viewer->assign('QRCODEURL', $qrcodeurl);
        $viewer->assign('AUTHTOKENKEY', $authTokenKey);
        $viewer->assign('QRCODESTATUS', $qrcodeStatus);
        $viewer->assign('WHATSAPPSTATUS', $whatsappStatus);
        //Add new functionality

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('BATCH', $batch);
        $viewer->assign('TIMEINTERVAL', $timeInterval);
        $viewer->assign('WHATSAPPNUMBER', $whatsappNo);

        //Add new functionality
        $viewer->assign('QRCODESCAN', $qrCodeScan);
        $viewer->assign('LOGOUT', $logout);
        $viewer->assign('ISADMIN', $isAdmin);
        $viewer->assign('USERSSHOWBUTTON', $numRowsUsers);
        $viewer->assign('ANALYTICSURL', $analyticsURL);
        $viewer->assign('ANALYTICS', $analytics);
        $viewer->assign('SHOWQRCODE', $showQRCode);
        $viewer->assign('DISPLAYQRCODE', $displayQRCode);
        $viewer->assign('QUALIFIED_MODULE', $settingModule);
        $viewer->assign('NOINTERNETNUMBER', $noInternetNumber);
        $viewer->assign('MULTIPELWHATSAPPNUMBER', $multipleWhatsappNumber);
        $viewer->assign('WHATSAPPBOT', $whatsAppBot);
        //Add new functionality

        $viewer->view('DashBoard.tpl',$moduleName);
    }

    function updateAuthenticationCode(Vtiger_Request $request) {
        $updateAuthAuthentication = CTWhatsAppBusiness_Record_Model::updateAuthCode($request);
    }
    
    function getWhatsappStatus(Vtiger_Request $request) {
       $whatsappStatusData = CTWhatsAppBusiness_Record_Model::getWhatsAppStatus($request);

        $response = new Vtiger_Response();
        $response->setResult(array('whatsappStatus' => $whatsappStatusData['whatsappStatus'], 'whatsappNo' => $whatsappStatusData['whatsappNo']));
        $response->emit();
    }

    function getMassMessageDetail(){
        $msaaMessageDetails = CTWhatsAppBusiness_Record_Model::getMassMessageData($request);
        return $msaaMessageDetails;
    }

    function sendqueueMessages(Vtiger_Request $request){
        $sendqueueMessages = CTWhatsAppBusiness_Record_Model::getSendQueueMessages($request);
        
        $response = new Vtiger_Response();
        $response->setResult($sendqueueMessages);
        $response->emit();
    }

    function getWhatsappMessage(Vtiger_Request $request){
        global $adb;
        $moduleName = $request->getModule();
        $datePeriodChart = CTWhatsAppBusiness_DashBoard_View::getPeriodDate($request);
        $getDataFromPeriodData = CTWhatsAppBusiness_DashBoard_View::getDataFromPeriodData($request);

        $periodData = $getDataFromPeriodData['arrayData'];
        $totalSentMessage = $getDataFromPeriodData['totalSentMessage'];
        $totalSentMessageURL = $getDataFromPeriodData['totalSentMessageURL'];
        $totalReceivedMessage = $getDataFromPeriodData['totalReceivedMessage'];
        $totalReceivedMessageURL = $getDataFromPeriodData['totalReceivedMessageURL'];
        $totalMessage = $getDataFromPeriodData['totalMessage'];
        $totalMessageURL = $getDataFromPeriodData['totalMessageURL'];

        $totalMassMessages = $getDataFromPeriodData['totalMassMessages'];
        $totalMassMessagesURL = $getDataFromPeriodData['totalMassMessagesURL'];
        $totalReadMassMessages = $getDataFromPeriodData['totalReadMassMessages'];
        $totalReadMassMessagesURL = $getDataFromPeriodData['totalReadMassMessagesURL'];
        $totalUnReadMassMessages = $getDataFromPeriodData['totalUnReadMassMessages'];
        $totalUnReadMassMessagesURL = $getDataFromPeriodData['totalUnReadMassMessagesURL'];
        
        $totalFinishedChat = $getDataFromPeriodData['totalFinishedChat'];
        $totalPendingChat = $getDataFromPeriodData['totalPendingChat'];

        $totalBotMessages = $getDataFromPeriodData['totalBotMessages'];
        $totalSendBotMessages = $getDataFromPeriodData['totalSendBotMessages'];
        $totalReceivedBotMessage = $getDataFromPeriodData['totalReceivedBotMessage'];
        
        $totalactiveBotChat = $getDataFromPeriodData['totalactiveBotChat'];
        $totalfinishBotChat = $getDataFromPeriodData['totalfinishBotChat'];

        $reportData = array('periodData' => json_encode($datePeriodChart), 'getDataFromPeriodData' => json_encode($periodData), 'totalMessage' => $totalMessage, 'totalSentMessage' => $totalSentMessage, 'totalReceivedMessage' => $totalReceivedMessage, 'totalMassMessages' => $totalMassMessages, 'totalReadMassMessages' => $totalReadMassMessages, 'totalUnReadMassMessages' => $totalUnReadMassMessages, 'totalFinishedChat' => $totalFinishedChat, 'totalPendingChat' => $totalPendingChat, 'totalBotMessages' => $totalBotMessages, 'totalSendBotMessages' => $totalSendBotMessages, 'totalReceivedBotMessage' => $totalReceivedBotMessage, 'totalactiveBotChat' => $totalactiveBotChat, 'totalfinishBotChat' => $totalfinishBotChat, 'totalMessageURL' => $totalMessageURL, 'totalSentMessageURL' => $totalSentMessageURL, 'totalReceivedMessageURL' => $totalReceivedMessageURL, 'totalMassMessagesURL' => $totalMassMessagesURL, 'totalReadMassMessagesURL' => $totalReadMassMessagesURL, 'totalUnReadMassMessagesURL' => $totalUnReadMassMessagesURL);

        $response = new Vtiger_Response();
        $response->setResult($reportData);
        $response->emit();
    }

    public function getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot){
        $reportData = CTWhatsAppBusiness_Record_Model::getWhatsAppReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);
        
        return $reportData;
    }

    public function getDataFromPeriodData($request){
        global $adb,$current_user;
        $start_day = 'Monday';
        $periodData = $request->get('periodData');
        $reportChart = $request->get('reportChart');
        $scanUsers = $request->get('scanUsers');
        $whatsAppBot = $request->get('whatsAppBot');

        $yAxisData1 = array();
        $yAxisData2 = array();
        $arrayData = array();
        if($periodData == 'today'){
            $todayDate = date('Y-m-d');
            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($todayDate);
            $realEnd->add($interval);

            $startdate = $todayDate;
            $endtdate = $todayDate;

            $period = new DatePeriod(new DateTime($todayDate), $interval, $realEnd);
            $format = 'Y-m-d';
            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }elseif($periodData == 'yesterday'){
            $yesterdayDate = date('Y-m-d',strtotime("-1 days"));
            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($yesterdayDate);
            $realEnd->add($interval);

            $startdate = $yesterdayDate;
            $endtdate = $yesterdayDate;

            $period = new DatePeriod(new DateTime($yesterdayDate), $interval, $realEnd);
            $format = 'Y-m-d';
            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }elseif ($periodData == 'thisweek'){
            $saturday = strtotime("last ".$start_day);
            $saturday = date('w', $saturday)==date('w') ? $saturday+7*86400 : $saturday;
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");

            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($endtdate);
            $realEnd->add($interval);
            $format = 'Y-m-d';

            $period = new DatePeriod(new DateTime($startdate), $interval, $realEnd);
            $periodCount = iterator_count($period) - 1;

            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }elseif ($periodData == 'lastweek') {
            $currentDay = date("N", strtotime(date("Y-m-d")));
            if($currentDay == 1){
                $saturday = strtotime("0 week last ".$start_day);
            }else{
                $saturday = strtotime("-1 week last ".$start_day);
            }
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");
            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($endtdate);
            $realEnd->add($interval);
            $format = 'Y-m-d';

            $period = new DatePeriod(new DateTime($startdate), $interval, $realEnd);

            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }elseif ($periodData == 'thismonth') {
            $firstDayOfMonth = date("d-m-Y", strtotime("first day of this month"));
            $lastDayOfMonth = date("d-m-Y", strtotime("last day of this month"));

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($lastDayOfMonth);
            $realEnd->add($interval);
            $format = 'Y-m-d';
            $period = new DatePeriod(new DateTime($firstDayOfMonth), $interval, $realEnd);

            $startdate = $firstDayOfMonth;
            $endtdate = $lastDayOfMonth;

            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);     

        }elseif ($periodData == 'lastmonth') {
            $firstDayOfMonth = date("d-m-Y", strtotime("first day of last month"));
            $lastDayOfMonth = date("d-m-Y", strtotime("last day of last month"));

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($lastDayOfMonth);
            $realEnd->add($interval);
            $format = 'Y-m-d';
            $period = new DatePeriod(new DateTime($firstDayOfMonth), $interval, $realEnd);

            $startdate = $firstDayOfMonth;
            $endtdate = $lastDayOfMonth;

            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }elseif ($periodData == 'alltime') {
            $minYear = '2019';
            $maxYear = date("Y");

            $interval = new DateInterval('P1Y');
            $realEnd = new DateTime($maxYear);
            $realEnd->add($interval);
            $format = 'Y';
            $period = new DatePeriod(new DateTime($minYear), $interval, $realEnd);

            $startdate = $maxYear.'-01-01';
            $endtdate = $maxYear.'-12-31';

            $arrayData = CTWhatsAppBusiness_DashBoard_View::getReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot);

        }
        return $arrayData;
    }


    /**
    * Function to Get Date from Selected Period Type like Alltime/Today/etc
    */
    public function getPeriodDate($request){
        global $adb;
        $start_day = 'Monday';
        $periodData = $request->get('periodData');
        if($periodData == 'thisweek'){
            $saturday = strtotime("last ".$start_day);
            $saturday = date('w', $saturday)==date('w') ? $saturday+7*86400 : $saturday;
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");

            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($endtdate);
            $realEnd->add($interval);
            $format = 'Y-m-d';

            $period = new DatePeriod(new DateTime($startdate), $interval, $realEnd);
            $dateString = array();
            foreach($period as $key => $date) {
                $dateString[] =  DateTimeField::convertToUserFormat($date->format($format));
            }
        }elseif ($periodData == 'lastweek') {
            $currentDay = date("N", strtotime(date("Y-m-d")));
            if($currentDay == 1){
                $saturday = strtotime("0 week last ".$start_day);
            }else{
                $saturday = strtotime("-1 week last ".$start_day);
            }
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");
            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($endtdate);
            $realEnd->add($interval);
            $format = 'Y-m-d';

            $period = new DatePeriod(new DateTime($startdate), $interval, $realEnd);
            $periodCount = iterator_count($period) - 1;
            $dateString = array();
            foreach($period as $key => $date) {
                $dateString[] =  DateTimeField::convertToUserFormat($date->format($format));
            }
        }elseif ($periodData == 'thismonth') {
            $firstDayOfMonth = date("d-m-Y", strtotime("first day of this month"));
            $lastDayOfMonth = date("d-m-Y", strtotime("last day of this month"));

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($lastDayOfMonth);
            $realEnd->add($interval);
            $format = 'Y-m-d';
            $period = new DatePeriod(new DateTime($firstDayOfMonth), $interval, $realEnd);
            $dateString = array();
            foreach($period as $date) {
                $dateString[] =  DateTimeField::convertToUserFormat($date->format($format));
            }
        }elseif ($periodData == 'lastmonth') {
            $firstDayOfMonth = date("d-m-Y", strtotime("first day of last month"));
            $lastDayOfMonth = date("d-m-Y", strtotime("last day of last month"));

            $interval = new DateInterval('P1D');
            $realEnd = new DateTime($lastDayOfMonth);
            $realEnd->add($interval);
            $format = 'Y-m-d';
            $period = new DatePeriod(new DateTime($firstDayOfMonth), $interval, $realEnd);
            $dateString = array();
            foreach($period as $date) {
                $dateString[] =  DateTimeField::convertToUserFormat($date->format($format));
            }
        }elseif ($periodData == 'today') {
            $todayDate = date('Y-m-d');
            $dateString[] =  DateTimeField::convertToUserFormat($todayDate);
        }elseif ($periodData == 'yesterday') {
            $yesterdayDate = date('Y-m-d',strtotime("-1 days"));
            $dateString[] =  DateTimeField::convertToUserFormat($yesterdayDate);
        }elseif ($periodData == 'alltime') {
            global $adb;
            $minYear = '2019';
            $maxYear = date('Y');

            $interval = new DateInterval('P1Y');
            $realEnd = new DateTime($maxYear);
            $realEnd->add($interval);
            $format = 'Y';
            $period = new DatePeriod(new DateTime($minYear), $interval, $realEnd);
            $dateString = array();
            foreach($period as $date) {
                $dateString[] =  $date->format($format);
            }
        }
        return $dateString;
    }

    public function getPeriodDataQuery($request,$tablename){
        $start_day = "Monday";
        $periodData = $request->get('periodData');
        if ($periodData == 'today') {
            $dataSelectQuery = " DATE($tablename) = CURDATE()";
        }elseif ($periodData == 'yesterday') {
            $dataSelectQuery = " DATE($tablename) = CURDATE() - INTERVAL 1 DAY";
        }elseif ($periodData == 'thisweek'){
            $saturday = strtotime("last ".$start_day);
            $saturday = date('w', $saturday)==date('w') ? $saturday+7*86400 : $saturday;
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");

            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $dataSelectQuery = " (DATE($tablename) BETWEEN '".$startdate."' AND '".$endtdate."')";
        }elseif ($periodData == 'lastweek') {
            $currentDay = date("N", strtotime(date("Y-m-d")));
            if($currentDay == 1){
                $saturday = strtotime("0 week last ".$start_day);
            }else{
                $saturday = strtotime("-1 week last ".$start_day);
            }
            $friday = strtotime(date("Y-m-d",$saturday)." +6 days");
            $startdate = date("Y-m-d",$saturday);
            $endtdate = date("Y-m-d",$friday);

            $dataSelectQuery = " (DATE($tablename) BETWEEN '".$startdate."' AND '".$endtdate."')";
        }elseif ($periodData == 'thismonth') {
            $dataSelectQuery = " MONTH($tablename)=MONTH(CURDATE( )) AND YEAR($tablename) = YEAR(CURDATE())";
        }elseif ($periodData == 'lastmonth') {
           $dataSelectQuery = " MONTH($tablename)=MONTH(CURDATE( )) - 1 AND YEAR($tablename) = YEAR(CURDATE())";
        }elseif ($periodData == 'customdate') {
            $startdate = date("Y-m-d",strtotime($request->get('startDate')));
            $enddate = date("Y-m-d",strtotime($request->get('endDate')));

            $dataSelectQuery = " (DATE($tablename) BETWEEN '".$startdate."' AND '".$enddate."')";
        }
        return $dataSelectQuery;
    }

    function pauseResumeMessage(Vtiger_Request $request){
        $moduleName = $request->getModule();
        $recordid = $request->get('recordid');
        $action = $request->get('buttonaction');
        if($action == 'resume'){
            $status = 0;
        }elseif($action == 'pause'){
            $status = 2;
        }
        CTWhatsAppBusiness_Record_Model::massMessagePauseResume($status, $recordid);
    }

    function deleteMassMessage(Vtiger_Request $request){
        global $adb;
        $moduleName = $request->getModule();
        $recordid = $request->get('recordid');
        CTWhatsAppBusiness_Record_Model::massMessageDelete($recordid);
    }

    function previewMassMessage(Vtiger_Request $request){
        global $adb;
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $massMessage = $request->get('massMessage');

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MASSMESSAGE', $massMessage);
        echo $viewer->view('PreviewMassMessage.tpl',$moduleName, true);
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "modules.$moduleName.resources.DashBoard",
            "modules.$moduleName.resources.highcharts",
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances,$headerScriptInstances);
        return $headerScriptInstances;
    }
}

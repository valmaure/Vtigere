<?php
/*+**********************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
 ************************************************************************************/
class CTWhatsAppBusiness_Record_Model extends Vtiger_Record_Model {

    //function for get Whatsapp icon
    public function getWhatsappIcon($sourceModule){
        global $adb,$current_user, $site_URL;
        $currenUserID = $current_user->id;
        $isAdmin = $current_user->is_admin;
        $roleID = $current_user->roleid;

        $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
        $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
        $notificationtone = $adb->query_result($getUserGrous, 0, 'notificationtone');
        $pushnotification = $adb->query_result($getUserGrous, 0, 'pushnotification');

        if($multipleWahtsapp == 'multipleWhatsapp'){
            $getUserGroups = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers INNER JOIN vtiger_ctwhatsappbusiness_user_groups ON vtiger_ctwhatsappbusiness_user_groups.userid = vtiger_ctwhatsappbusinessusers.customfield5 WHERE vtiger_ctwhatsappbusiness_user_groups.active = 1");
            $userrows = $adb->num_rows($getUserGroups);
            $allUsers = '';
            for ($i=0; $i < $userrows; $i++) { 
                $allUsers .= $adb->query_result($getUserGroups, $i, 'userid').',';
                $allUsers .= $adb->query_result($getUserGroups, $i, 'multiple_userid').',';
            }

            $usersGroups = explode(',', $allUsers);
        }else{
            $usersGroups = explode(',', $adb->query_result($getUserGrous, 0, 'users_groups'));
        }

        $userid = in_array($currenUserID, $usersGroups);
        if($userid){
            $num_rows = 1;
        }else{
            $queryGetGroupId = $adb->pquery("SELECT * FROM vtiger_users2group WHERE userid = ?", array($currenUserID));
            $numRows = $adb->num_rows($queryGetGroupId);
            for ($i=0; $i < $numRows; $i++) { 
                $groupid[] = $adb->query_result($queryGetGroupId, $i, 'groupid');
            }

            foreach ($groupid as $key => $value) {
                $existGroup = in_array($value, $usersGroups);
                if($existGroup){
                    $num_rows = 1;
                    break;
                }else{
                    $num_rows = 0;
                }
            }
        }

        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $apiurl = str_replace('/api/', '', $configurationData['api_url']);
        $apiUrl = $apiurl.'/'.$configurationData['authtoken'];
        $iconactive = $configurationData['iconactive'];
        $inappNotification = $configurationData['inapp_notification'];
        $scanWhatsappNumber = $configurationData['whatsappno'];
        $whatsappStatus = $configurationData['whatsappstatus'];

        $allUserNumber = CTWhatsAppBusiness_Record_Model::getAllUserWhatsappNumber($currenUserID);

        if(is_null($scanWhatsappNumber)){
            $scanWhatsappNumber = '-';
        }

        $themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();
        if($themeView == 'RTL'){
            $picStyle1 = "margin: 0px 0px 0px 10px;float: right;";
            $picStyle2 = "margin: 0px 0px 0px 10px;float: right;";
            $divStyle = "style='direction: rtl;text-align: right;'";
            $timeStyle = "float: left;direction: ltr;";
        }else{
            $picStyle1 = "margin: 0px 19px 0px 0px;float: left;";
            $picStyle2 = "margin: 0px 19px 0px 0px;float: left;";
            $divStyle = "";
            $timeStyle = "float: right;";
        }

        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currenUserID);
        $inLogNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currenUserID);

        $unreadmsgCountsQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread' AND vtiger_ctwhatsappbusiness.message_type = 'Recieved' AND vtiger_ctwhatsappbusiness.whatsapp_withccode != 'Groups' ".$inNumberQuery);
        $unreadCountCounts = $adb->num_rows($unreadmsgCountsQuery);
        if($unreadCountCounts == ''){
            $unreadCountCounts = 0;
        }

        $unreadmsgQuery = $adb->pquery("SELECT vtiger_whatsappbusinesslog.whatsapplog_contactid, vtiger_whatsappbusinesslog.whatsapplog_unreadread, vtiger_whatsappbusinesslog.whatsapplog_withccode, vtiger_whatsappbusinesslog.messagelog_body, vtiger_whatsappbusinesslog.whatsapplog_datetime, vtiger_whatsappbusinesslog.whatsapplog_sendername FROM vtiger_whatsappbusinesslog
        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
        INNER JOIN (SELECT ct2.whatsapplog_withccode , MAX(ct2.whatsappbusinesslogid) AS maxid FROM vtiger_whatsappbusinesslog ct2 GROUP BY ct2.whatsapplog_withccode) ct3 ON (vtiger_whatsappbusinesslog.whatsappbusinesslogid = ct3.maxid) WHERE vtiger_crmentity.deleted = 0 ".$inLogNumberQuery."  ORDER BY vtiger_whatsappbusinesslog.whatsapplog_datetime DESC LIMIT 0, 5");

        $unreadCountRow = $adb->num_rows($unreadmsgQuery);
        $notificationHTML = '';
        for ($i=0; $i < $unreadCountRow; $i++) { 
            $recordId = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_contactid');
            $messageReadUnread = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_unreadread');
            if($recordId == ''){
                $label = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_withccode');
                $lastBody = $adb->query_result($unreadmsgQuery, $i, 'messagelog_body');
                $lastDateTime = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_datetime');

                if($label != 'Groups'){
                    $individulMessageID = 'id="whatsapp"';
                    $class = "";
                    $imageIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/AvtarIcon.png';
                    $messagename = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_withccode');
                }else{
                    $individulMessageID = '';
                    $class = "receivednewmessages";
                    $imageIcon = 'layouts/v7/modules/CTWhatsAppBusiness/image/groups.png';
                    $messagename = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_sendername');
                }

                $notificationHTML .= '<li class="whatsapp_new_messages '.$class.'" '.$individulMessageID.' data-recordid="'.$label.'" style="width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);">
                <a href="#" style="padding: 10px 10px !important;color: #333 !important;display: inline-block;float: left;width: 100%;">
                <div class="pic" style="display: inline-block;padding-right: 10px;width: 50px;height: 50px;border-radius: 50%;box-shadow: 0 0 5px rgb(68 80 100 / 0.25);max-width: 36px;max-height : 36px;margin : 0;'.$picStyle1.'">
                <img src="'.$imageIcon.'" style="width: 40px;max-width: 36px;max-height : 36px;margin : 0;border-radius: 50%;background: #4ebb46;"/>
                </div>
                <div '.$divStyle.'>
                <span class=""><b style="font-size: 14px; !important;font-size: 14px;overflow: hidden;text-overflow: ellipsis;display: inline-block;position: relative;top: 6px;width: 230px;">'.$messagename.'</b></span>
                <p style="max-width: 300px;font-size: 12px;max-width: 200px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">';
                if($messageReadUnread == 'Unread'){
                    $notificationHTML .= '<b>'.$lastBody.'</b>';
                }else{
                    $notificationHTML .= $lastBody;
                }
                $notificationHTML .= '</p>
                <span class="" style="font-size: 12px;line-height: 16px;font-weight: 400;display: block;'.$timeStyle.'">'.Vtiger_Util_Helper::formatDateDiffInStrings($lastDateTime).'</span>
                </div>
                </a>
                </li>';
            }else{
                $setype = VtigerCRMObject::getSEType($recordId);
                $deleteRecord = CTWhatsAppBusiness_Record_Model::recordDelete($recordId);
                if(Users_Privileges_Model::isPermitted($setype, 'EditView', $recordId)) {
                    if($deleteRecord == 0){
                        if($recordId){
                            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $setype);
                            $label = $recordModel->get('label');
                        }
                        $profileImage = CTWhatsAppBusiness_Record_Model::getImageDetails($recordId, $setype);
                        $imageTag = 1;
                        if($profileImage == ''){
                            $labelExplode = explode(' ', $label);
                            $profileImage = mb_substr($labelExplode[0],0,1, "UTF-8").mb_substr($labelExplode[1],0,1, "UTF-8");
                            $imageTag = 0;
                        }

                        $lastBody = $adb->query_result($unreadmsgQuery, $i, 'messagelog_body');
                        $lastDateTime = $adb->query_result($unreadmsgQuery, $i, 'whatsapplog_datetime');

                        $notificationHTML .= '<li class="whatsapp_new_messages" id="whatsapp" data-recordid="'.$recordId.'" data-setype="'.$setype.'" style="width: 100%;display: inline-block;float: left;border-bottom: 1px solid rgb(44 59 73 / 15%);">
                        <a href="#" style="padding: 10px 10px !important;color: #333 !important;display: inline-block;float: left;width: 100%;">
                        <div class="pic" style="display: inline-block;float: left; width: 36px;height: 36px;border-radius: 50%;box-shadow: 0 0 5px rgb(68 80 100 / 0.25); '.$picStyle2.' text-align: center; color: #4ebb46;">';
                        if($imageTag == 1){
                            $notificationHTML .= '<img src="'.$profileImage.'" style=" width: 100%;margin: 0;height: 100%;border-radius: 50%;"/>';
                        }else{
                            $notificationHTML .= '<span class="imagename" id="imagename" style="font-size: 15px;line-height: 36px;"><b>'.$profileImage.'</b></span>';
                        }


                        $notificationHTML .= '</div>
                        <div  '.$divStyle.'>
                        <span class=""><b style="font-size: 14px; !important;font-size: 14px;overflow: hidden;text-overflow: ellipsis;display: inline-block;position: relative;top: 6px;width: 230px;">'.$label.'</b></span>
                        <p style="max-width: 300px;font-size: 12px;max-width: 200px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">';
                        if($messageReadUnread == 'Unread'){
                            $notificationHTML .= '<b>'.$lastBody.'</b>';
                        }else{
                            $notificationHTML .= $lastBody;
                        }
                        $notificationHTML .= '</p>
                        </p>
                        <span class="" style="font-size: 12px;line-height: 16px;font-weight: 400;display: block;'.$timeStyle.'">'.Vtiger_Util_Helper::formatDateDiffInStrings($lastDateTime).'</span>
                        </div>
                        </a>
                        </li>';
                    }
                }
            }
        }

        $currentdate = date('Y-m-d');
        $getexpiredate = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_license_setting");
        $expirydate = $adb->query_result($getexpiredate, 0, 'expirydate');
        $licenseKey = $adb->query_result($getexpiredate, 0, 'license_key');
        $date = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');

        $allwModules = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($sourceModule);
        $moduleMassMessage = $allwModules['rows'];
        $moduleIconActive = $allwModules['moduleIconActive'];
        $phoneField = $allwModules['phoneField'];

        if(strtotime($date) < strtotime($currentdate)){
            $licenseExpire = 'yes';
        }else{
            $licenseExpire = 'no';
        }

        if($licenseKey != ''){
            $whatsappModuleData = array('moduleMassMessage' => $moduleMassMessage, 'apiUrl' => $apiUrl, 'whatsappicon' => $num_rows, 'unread_count' => $unreadCountCounts, 'currentdate' => strtotime($currentdate), 'licensedate' => strtotime($date), 'whatsappStatus' => $whatsappStatus, 'moduleIconActive' => $moduleIconActive, 'phoneField' => $phoneField,'inappNotification' => $inappNotification, 'notificationHTML' => $notificationHTML, 'isAdmin' => $isAdmin, 'themeView' => $themeView, 'iconactive' => $iconactive, 'licenseExpire' => $licenseExpire, 'scanWhatsappNumber' => $scanWhatsappNumber, 'notificationtone' => $notificationtone, 'pushnotification' => $pushnotification);
        }
        return $whatsappModuleData;
    }

    //Function for get QR Code scan URL
    function getScanQRCodeURL() {
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $isAdmin = $current_user->is_admin;
        $queryUserExist = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield3 LIKE '%".$currentUserID."%'", array());
        $numRowsUsers = $adb->num_rows($queryUserExist);

        if($numRowsUsers == 0){
            $queryGetGroupId = $adb->pquery("SELECT * FROM vtiger_group2role INNER JOIN vtiger_user2role ON vtiger_user2role.roleid = vtiger_group2role.roleid WHERE vtiger_user2role.userid = ?", array($currentUserID));
            $groupid = $adb->query_result($queryGetGroupId, 0, 'groupid');
            if($groupid != ''){
                $queryGroupExist = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield3 LIKE '%".$groupid."%'", array());
            }
            $numRowsUsers = $adb->num_rows($queryGroupExist);
        }
        
        if($isAdmin == 'on'){
            $scanQRCode = 'index.php?module=CTWhatsAppBusiness&parent=Settings&view=ConfigurationDetail';
        }else{
            $scanQRCode = 'index.php?module=CTWhatsAppBusiness&view=DashBoard&mode=moduleDashBoard&showqrcode=1';
        }
        return $scanQRCode;
    }

    //function for get all allow whatsapp module
    function getWhatsappAllowModules() {
        global $adb;
        $whatsappModuleQuery = $adb->pquery("SELECT * FROM vtiger_ctwharsappallow_whatsappmodule WHERE active = 1");
        $rows = $adb->num_rows($whatsappModuleQuery);
        
        $whatsaappModule = array();
        for ($i=0; $i < $rows; $i++) { 
            $module = $adb->query_result($whatsappModuleQuery, $i, 'module');
            $data = CTWhatsAppBusiness_Record_Model::checkPermissionModule($module);
            if($data == 1){
                $moduleIsEnable = CTWhatsAppBusiness_Record_Model::getmoduleIsEnable($module);

                if($moduleIsEnable == 0){
                    $whatsaappModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($module);
                    $phoneField = $whatsaappModuleData['phoneField'];
                    /*$serach = '';
                    $moduleQuery = CTWhatsAppBusiness_Record_Model::moduleQueryCount($module, $phoneField, $serach);
                    $query = $adb->pquery($moduleQuery);
                    $row = $adb->num_rows($query);
                    if($row == ''){
                        $row = 0;
                    }*/
                    $whatsaappModule[] = array('module' => $module, 'rows' => $row, 'phoneField' => $phoneField);
                }
            }
        }
        return $whatsaappModule;
    }

    //function for get select module active or not
    function getmoduleIsEnable($moduleName){
        global $adb;
        $moduleQuery = $adb->pquery("SELECT * FROM vtiger_tab WHERE name = ?", array($moduleName));
        return $presence = $adb->query_result($moduleQuery, 0, 'presence');
    }

    //function for get Whatsapp modules field
    function getWhatsappAllowModuleFields($sourceModule) {
        global $adb;
        $getAllowModule = $adb->pquery("SELECT * FROM vtiger_ctwharsappallow_whatsappmodule WHERE module = ?", array($sourceModule));
        $rows = $adb->num_rows($getAllowModule);

        $moduleIconActive = $adb->query_result($getAllowModule, 0, 'active');
        $phoneField = $adb->query_result($getAllowModule, 0, 'phone_field');
        $allwModules = array('moduleIconActive' => $moduleIconActive, 'phoneField' => $phoneField, 'rows' => $rows);
        return $allwModules;
    }

    //function for get module query
    function moduleQuery($modulename, $phoneField, $searchValue){
        global $adb, $current_user;
        $currenUserID = $current_user->id;
        $moduleModel = CRMEntity::getInstance($modulename);
        $moduleInstance = Vtiger_Module::getInstance($modulename);
        $baseTable = $moduleInstance->basetable;
        $baseTableid = $moduleInstance->basetableid;

        if($searchValue){
            $searchQuery = " AND (vtiger_crmentity.label LIKE '%".$searchValue."%' OR ".$phoneField." LIKE '%".$searchValue."%')";
        }else{
            $searchQuery = "";
        }

        $mainTable = 0;
        $query = "SELECT * FROM ".$baseTable;
        foreach ($moduleModel->tab_name_index as $key => $value) {
            $mainTable = $mainTable + 1;
            if($mainTable != 2){ 
                  if($key != 'vtiger_seproductsrel' && $key != 'vtiger_producttaxrel'){
                      $query .= " INNER JOIN ".$key." ON ".$key.".".$value." = ".$baseTable.".".$baseTableid;
                  }
            }
        }

        $isAdmin = $current_user->is_admin;
        if($isAdmin != 'on'){
            $tabid = getTabid($modulename);
            if($tabid){
                $getRecordPermissionQuery = $adb->pquery("SELECT * FROM vtiger_def_org_share WHERE tabid = ?", array($tabid));
                $permission = $adb->query_result($getRecordPermissionQuery, 0, 'permission');
                if($permission == '3'){
                    $assignQuery = " AND vtiger_crmentity.smownerid = '$currenUserID'";
                }
            }
        }

        $query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid = vtiger_groups.groupid ";

        $query .= " LEFT JOIN
          (
            SELECT messagelog_body, whatsapplog_datetime, whatsapplog_displayname, whatsapplog_contactid, max(whatsapplog_datetime) as whatsapp_date
            FROM vtiger_whatsappbusinesslog
            INNER JOIN vtiger_crmentity as crmentitylog ON crmentitylog.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
            WHERE crmentitylog.deleted = 0 
            group by whatsapplog_contactid
          ) last_shared on (last_shared.whatsapplog_contactid = ".$baseTable.".".$baseTableid." )";

        $query .= " WHERE vtiger_crmentity.deleted=0 AND ".$baseTable.".".$baseTableid." > 0 AND ".$phoneField." != ''".$searchQuery." ".$assignQuery." GROUP BY ".$phoneField." ORDER BY coalesce(last_shared.whatsapp_date) DESC" ;
        
        return $query;
    }


    //function for get module query
    function moduleQueryCount($modulename, $phoneField, $searchValue){
        global $adb, $current_user;
        $currenUserID = $current_user->id;
        $moduleModel = CRMEntity::getInstance($modulename);
        $moduleInstance = Vtiger_Module::getInstance($modulename);
        $baseTable = $moduleInstance->basetable;
        $baseTableid = $moduleInstance->basetableid;

        if($searchValue){
          $searchQuery = " AND vtiger_crmentity.label LIKE '%".$searchValue."%'";
        }else{
          $searchQuery = "";
        }

        $mainTable = 0;
        $query = "SELECT count(*) as rows1 FROM ".$baseTable." 
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ".$baseTable.".".$baseTableid." 
            WHERE vtiger_crmentity.deleted = 0";

        return $query;
    }

    //function for get Whatsapp modules query
    public function unreadQuery(){
        global $adb, $current_user;
        $userID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currentUserID);
        $query = "SELECT * FROM vtiger_ctwhatsappbusiness 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
          WHERE vtiger_crmentity.deleted = 0";
        return $query;
    }

    public function unreadQueryCount(){
        global $adb, $current_user;
        $userID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currentUserID);
        $query = "SELECT count(*) FROM vtiger_ctwhatsappbusiness 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
          WHERE vtiger_crmentity.deleted = 0 ".$inNumberQuery;
        return $query;
        }

    //Function for get Unread Whatsapp messages
    public function getUnreadMessagesCount(){
        global $adb;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $query = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread' AND vtiger_ctwhatsappbusiness.message_type = 'Recieved' AND vtiger_ctwhatsappbusiness.whatsapp_withccode != 'Groups'");
        $rows = $adb->num_rows($query);
        return $rows;
    }

    //function for get important message count
    public function getImportantMessagesCounts(){
        global $adb, $current_user;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $currentUserID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currentUserID);
        $query = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_important = 1 ".$inNumberQuery." GROUP BY vtiger_whatsappbusinesslog.whatsapplog_contactid, vtiger_whatsappbusinesslog.whatsapplog_withccode", array());

        $rows = $adb->num_rows($query);

        $delete = 0;
        for ($i=0; $i < $rows; $i++) { 
            $relatedRecordId = $adb->query_result($query, $i, 'whatsapplog_contactid');
            if($relatedRecordId != ''){
                $deleteRecord = CTWhatsAppBusiness_Record_Model::recordDelete($relatedRecordId);
                if($deleteRecord == 1){
                    $delete = $delete + 1;
                }
            }
        }

        $numRows = 0;
        for ($i=0; $i < $rows; $i++) { 
            $relatedRecordId = $adb->query_result($query, $i, 'whatsapplog_contactid');
            $setype = VtigerCRMObject::getSEType($relatedRecordId);
            if(Users_Privileges_Model::isPermitted($setype, 'EditView', $relatedRecordId)) {
                $numRows = $numRows + 1;
            }
        }

        $numOfRows = $numRows - $delete;
        return $numOfRows;
    }

  //function for get new message count
    public function getNewMessagesCounts(){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQueryCount();
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currentUserID);
        $query = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_withccode != '' AND vtiger_whatsappbusinesslog.whatsapplog_unreadread = 'Unread'  AND vtiger_whatsappbusinesslog.messagelog_type = 'Recieved' AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' ".$inNumberQuery." GROUP BY vtiger_whatsappbusinesslog.whatsapplog_contactid, vtiger_whatsappbusinesslog.whatsapplog_withccode ORDER BY vtiger_crmentity.createdtime", array());
        $rows = $adb->num_rows($query);

        $allNewMessageCountquery = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_withccode != '' AND vtiger_whatsappbusinesslog.whatsapplog_unreadread = 'Unread'  AND vtiger_whatsappbusinesslog.messagelog_type = 'Recieved' AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' ".$inNumberQuery."", array());
        $allRows = $adb->num_rows($allNewMessageCountquery);

        $result = array('rows' => $rows, 'allRows' => $allRows);
        return $result;
    }

    //function for get all messages count
    public function getAllMessagesCounts(){
        global $adb, $current_user;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $userID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currentUserID);

        $allQuery = "SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_withccode != '' ".$inNumberQuery." AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' GROUP BY vtiger_whatsappbusinesslog.whatsapplog_contactid, vtiger_whatsappbusinesslog.whatsapplog_withccode";

        $unreadQuery = "SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_withccode != '' AND vtiger_whatsappbusinesslog.messagelog_type = 'Recieved' AND vtiger_whatsappbusinesslog.whatsapplog_unreadread = 'Unread' ".$inNumberQuery." AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' GROUP BY vtiger_whatsappbusinesslog.whatsapplog_contactid, vtiger_whatsappbusinesslog.whatsapplog_withccode";

        $allUnreadMessageCount = $adb->pquery($allQuery);
        $row = $adb->num_rows($allUnreadMessageCount);

        $delete = 0;
        for ($i=0; $i < $row; $i++) { 
            $relatedRecordId = $adb->query_result($allUnreadMessageCount, $i, 'whatsapplog_contactid');
            if($relatedRecordId != ''){
                $deleteRecord = CTWhatsAppBusiness_Record_Model::recordDelete($relatedRecordId);
                if($deleteRecord == 1){
                    $delete = $delete + 1;
                }
            }
        }
        $rows = $row - $delete;

        $allUnreadCount = $adb->pquery($unreadQuery);
        $allRows = $adb->num_rows($allUnreadCount);

        $result = array('rows' => $allRows, 'allRows' => $rows);
        return $result;
    }

    //function for get unknown message count
    public function getUnknownMessagesCounts(){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currentUserID);
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQueryCount();
        $query = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0  AND vtiger_whatsappbusinesslog.whatsapplog_contactid = '' AND vtiger_whatsappbusinesslog.whatsapplog_unreadread = 'Unread' AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' AND vtiger_whatsappbusinesslog.messagelog_type = 'Recieved' ".$inNumberQuery." GROUP BY vtiger_whatsappbusinesslog.whatsapplog_withccode", array());
        $unknownRows = $adb->num_rows($query);
        if($unknownRows == ''){
            $unknownRows = 0;
        }

        $allUnknownquery = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
          INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
          WHERE vtiger_crmentity.deleted = 0  AND vtiger_whatsappbusinesslog.whatsapplog_contactid = '' AND vtiger_whatsappbusinesslog.whatsapplog_unreadread = 'Unread' AND vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' ".$inNumberQuery." GROUP BY vtiger_whatsappbusinesslog.whatsapplog_withccode", array());
        $allUnknownRows = $adb->num_rows($allUnknownquery);
        if($allUnknownRows == ''){
            $allUnknownRows = 0;
        }
        $result = array('unknownRows' => $unknownRows, 'allUnknownRows' => $allUnknownRows);
        return $result;
    }

    //function for get whatsapp message record
    public function getModuleRecrods($request) {
        $moduleName = $request->getModule();
        $whatsappmodule = $request->get('whatsappmodule');
        $searchValue = $request->get('searchValue');
        $start = $request->get('start');
        $end = $request->get('end');
        $groupWhatsappNumber = $request->get('groupWhatsappNumber');
        $responseCustomer = $request->get('responseCustomer');

        $html = '';

        if($whatsappmodule == 'Important'){
            $importantMessages = 'yes';
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getNewMessagesData($importantMessages, $start, $end, $searchValue, $whatsappmodule, $responseCustomer);
        }else if($whatsappmodule == 'NewMessages'){
            $importantMessages = 'no';
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getNewMessagesData($importantMessages, $start, $end, $searchValue, $whatsappmodule, $responseCustomer);
        }else if($whatsappmodule == 'Unknown'){
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getUnknownMessagesData($start, $end, $searchValue);
        }else if($whatsappmodule == 'AllMessages'){
            $importantMessages = 'no';
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getNewMessagesData($importantMessages, $start, $end, $searchValue, $whatsappmodule, $responseCustomer);
        }else if($whatsappmodule == 'Groups'){
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getWhatsappGroup($groupWhatsappNumber);
        }else{
            $moduleMessages = CTWhatsAppBusiness_Record_Model::getModuleMessagesData($whatsappmodule, $start, $end, $searchValue);
            global $adb;
            $whatsaappModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($whatsappmodule);
            $phoneField = $whatsaappModuleData['phoneField'];
            $serach = '';
            $moduleQuery = CTWhatsAppBusiness_Record_Model::moduleQueryCount($whatsappmodule, $phoneField, $serach);
            $query = $adb->pquery($moduleQuery);
            $row = $adb->query_result($query, 0, 'rows1');
        }
    
        $index = 0;
        foreach ($moduleMessages as $key => $value) {
            if($value['unreadMessageCount'] == 0){
                $read = '';
                $count = '';
            }else{
                $read = 'unRead';
                $count = '<span class="counterMsg" style="top: 0px !important;margin-left: 40px; !important">'.$value['unreadMessageCount'].'</span>';
            }
          
            if($index == 0){
                $bydefaulOpenChat = 'bydefaulOpenChat';
            }else{
                $bydefaulOpenChat = '';
            }
      
            if($whatsappmodule == 'Groups'){
                $html .= '<div class="profile showChatMessages '.$bydefaulOpenChat.'" data-recordid="'.$value['recordId'].'" data-label="'.$value['label'].'" data-groupid="'.$value['groupid'].'" data-groupMember="'.$value['groupMember'].'" data-sendmessageingroup="'.$value['isReadOnly'].'" style="cursor: pointer;">';
                      ;
                      if($value['imagetag'] == 1){
                        $html .= '<div class="pic"><img src="'.$value['profileImage'].'" style="width: 100%;"/></div>';
                      }else{
                        $html .= '<div class="pic"><span class="imagename" id="imagename" style="margin: 7px;font-size: 28px;margin: 2px;color: teal;"><b>'.$value['profileImage'].'</b></span></div>';
                      }

                      $html .= '<div class="pText">
                        <span>'.$value['label'].'</span>'.$count.'<p>'.$value['lastBody'].'</p>
                      </div>
                    </div>';
            }else{
                $html .= '<div class="profile showChatMessages '.$bydefaulOpenChat.'" data-recordid="'.$value['recordId'].'" style="cursor: pointer;">';

                      if($value['imagetag'] == 1){
                        $html .= '<div class="pic"><img src="'.$value['profileImage'].'" style="width: 100%;"/></div>';
                      }else{
                        $html .= '<div class="pic"><span class="imagename" id="imagename" style="margin: 7px;font-size: 28px;margin: 2px;color: teal;"><b>'.$value['profileImage'].'</b></span></div>';
                      }

                      $html .= '<div class="pText">
                        <span>'.$value['label'].'</span>'.$count.'<p>'.$value['lastBody'].'</p>
                      </div>
                      <div class="dateTime">'.$value['lastDateTime'].'</div>
                    </div>';
            }
            $index = $index + 1;
        }
        $html .= '</div>
        </div>';
        
        if(empty($moduleMessages)){
            $response = new Vtiger_Response();
            $response->setResult(array('allMessageshtml' => '', 'rows' => 0));
            $response->emit();
        }else{
            $response = new Vtiger_Response();
            $response->setResult(array('allMessageshtml' => $html, 'rows' => $row));
            $response->emit();
        }
    }

    public function recordDelete($recordId){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_crmentity WHERE crmid = ?", array($recordId));
        $deleted = $adb->query_result($query, 0, 'deleted');
        return $deleted;
    }

    //function for get new whatsapp message record
    public function getNewMessagesData($importantMessages, $start, $end, $searchValue, $whatsappmodule, $responseCustomer){
        global $adb, $current_user;
        $currentUserID = $current_user->id;

        if($importantMessages == 'yes'){
            $important = " AND vc.whatsapplog_important = 1 ";
        } 

        if($searchValue){
            if(is_numeric($searchValue)){
                $searchQuery = " AND vc.whatsapplog_withccode LIKE '%".$searchValue."%'";
            }else{
                $searchQuery = " AND cw.label like '%".$searchValue."%'";
                $searchInnerQuery = " INNER JOIN vtiger_crmentity cw on cw.crmid = vc.whatsapplog_contactid ";
            }
        }

        if($whatsappmodule == "NewMessages"){
            $customUnreadQuery = " AND vc.whatsapplog_unreadread = 'Unread' AND vc.messagelog_type = 'Recieved'";
        }else{
            $temporaryQuery = "INNER JOIN ct_whatsapp_temp_table_timeline ON vc.whatsappbusinesslogid = ct_whatsapp_temp_table_timeline.whatsappbusinesslogid ";
        }

        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currentUserID);
        $scanWhatsappNumber = $configurationData['whatsappno'];
        
        $whatsAppAllNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedWhatsappNumber($currentUserID);
        $inNumber = '';
        foreach ($whatsAppAllNumber as $key => $value) {
            $inNumber .= "'".$value['whatsappno']."',";
        }
        $allnumber = rtrim($inNumber, ',');
        $inNumberQuery = ' AND vc.whatsapplog_your_number IN ('.$allnumber.') ';
        $inNumber_Query = ' AND vc_inner.whatsapplog_your_number IN ('.$allnumber.') ';

        if($responseCustomer == 1){
            $responseQuery = $adb->pquery("SELECT * FROM whatsappbot_pre_que");
            $responseRows = $adb->num_rows($responseQuery);
            $responsecustomerNumber = '';
            for ($i=0; $i < $responseRows; $i++) { 
                $responsecustomerNumber .= '"'.$adb->query_result($responseQuery, $i, 'prequemobilenumber').'",';
            }
            
            $query = $adb->pquery("SELECT vc.whatsapplog_withccode, vc.messagelog_body, vc.whatsappbusinesslogid, vc.whatsapplog_datetime, vc.whatsapplog_contactid
                FROM vtiger_whatsappbusinesslog vc INNER JOIN vtiger_crmentity cr ON cr.crmid = vc.whatsappbusinesslogid
                ".$searchInnerQuery."
                AND vc.whatsapplog_withccode != ''   
                ".$inNumberQuery."
                ".$important."
                ".$searchQuery."
                ".$customUnreadQuery."
                AND vc.whatsapplog_withccode != 'Groups' 
                AND cr.deleted = 0 
                AND whatsapplog_withccode IN (".rtrim($responsecustomerNumber, ',').")
                ORDER BY vc.whatsapplog_datetime DESC LIMIT 0,".$start);
        }else{
            $query = $adb->pquery("SELECT vc.whatsapplog_withccode, vc.messagelog_body, vc.whatsappbusinesslogid, vc.whatsapplog_datetime, vc.whatsapplog_contactid
                FROM vtiger_whatsappbusinesslog vc INNER JOIN vtiger_crmentity cr ON cr.crmid = vc.whatsappbusinesslogid
                ".$searchInnerQuery."
                AND vc.whatsapplog_withccode != ''   
                ".$inNumberQuery."
                ".$important."
                ".$searchQuery."
                ".$customUnreadQuery."
                AND vc.whatsapplog_withccode != 'Groups' 
                AND cr.deleted = 0 
                ORDER BY vc.whatsapplog_datetime DESC LIMIT 0,".$start);
        }
        
        $rows = $adb->num_rows($query);
        $newMessagearray = array();
        for ($i=0; $i < $rows; $i++) { 
            $unreadMessageCount = '';
            $recordId = $adb->query_result($query, $i, 'whatsapplog_contactid');
            if($recordId == ''){
                $label = $adb->query_result($query, $i, 'whatsapplog_withccode');
                $lastBody = $adb->query_result($query, $i, 'messagelog_body');
                $lastDateTime = Vtiger_Util_Helper::formatDateDiffInStrings($adb->query_result($query, $i, 'whatsapplog_datetime'));
                $recordId = $label;
                $profileImage = 'layouts/v7/modules/CTWhatsAppBusiness/image/AvtarIcon.png';
                $imagetag = 1;
                $messageData = CTWhatsAppBusiness_Record_Model::getWhatsappUnReadNewMessageCounts($label);
                $unreadMessageCount = $messageData['unreadCount'];
                $avgMessageDay = $messageData['avgMessageDay'];

                $newMessagearray[$label] = array('recordId' => $recordId, 'label' => $label, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'unreadMessageCount' => $unreadMessageCount, 'avgMessageDay' => $avgMessageDay,'lastBody' => $lastBody, 'lastDateTime' => $lastDateTime);
            }else{
                $setype = VtigerCRMObject::getSEType($recordId);
                $actionName = 'DetailView';
                if(!Users_Privileges_Model::isPermitted($setype, $actionName, $recordId)) {
                    $permissionRecord = "0";
                }else{
                    $permissionRecord = "1";
                }
                if($permissionRecord == 1){
                    $moduleIsEnable = CTWhatsAppBusiness_Record_Model::getmoduleIsEnable($setype);
                    if($moduleIsEnable == 0){
                    $deleteRecord = CTWhatsAppBusiness_Record_Model::recordDelete($recordId);
                    if($deleteRecord == 0){
                        if($recordId){
                            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $setype);
                            $label = $recordModel->get('label');
                        }
                        $profileImage = CTWhatsAppBusiness_Record_Model::getImageDetails($recordId, $setype);
                        $imagetag = 1;
                        if($profileImage == ''){
                            $labelExplode = explode(' ', $label);
                            $profileImage = mb_substr($labelExplode[0],0,1, "UTF-8").mb_substr($labelExplode[1],0,1, "UTF-8");
                            $imagetag = 0;
                        }

                        $messageData = CTWhatsAppBusiness_Record_Model::getWhatsappUnReadMessageCounts($recordId);
                        $unreadMessageCount = $messageData['unreadCount'];
                        $avgMessageDay = $messageData['avgMessageDay'];
                        $lastBody = $adb->query_result($query, $i, 'messagelog_body');
                        $lastDateTime = Vtiger_Util_Helper::formatDateDiffInStrings($adb->query_result($query, $i, 'whatsapplog_datetime'));

                        $newMessagearray[$label] = array('recordId' => $recordId, 'label' => $label, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'unreadMessageCount' => $unreadMessageCount, 'avgMessageDay' => $avgMessageDay,'lastBody' => $lastBody, 'lastDateTime' => $lastDateTime);
                        }
                    }
                }
            }
        }
        return $newMessagearray;
    }

    //function for get new whatsapp unreadmessage count
    public function getWhatsappUnReadNewMessageCounts($phonenumber){
        global $adb;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $query = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread' AND vtiger_ctwhatsappbusiness.whatsapp_withccode = ? AND vtiger_ctwhatsappbusiness.message_type = 'Recieved'", array($phonenumber));
        $unreadCount = $adb->num_rows($query);

        $allMessageQuery = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_withccode = ?", array($phonenumber));
        $rows = $adb->num_rows($allMessageQuery);

        $totalDays = 30;
        $avgMessageDay = $rows/$totalDays;

        $result = array('unreadCount' => $unreadCount, 'avgMessageDay' => number_format($avgMessageDay, 2));

        return $result;
    }

    //function for get last whatsapp message data
    public function getLastWhatsappNewMessageData($phonenumber){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currentUserID);
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $query = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_withccode = ? ".$inNumberQuery." ORDER BY vtiger_ctwhatsappbusiness.whatsapp_datetime DESC LIMIT 0,1", array($phonenumber));

        $body = $adb->query_result($query, 0, 'message_body');
        if($adb->query_result($query, 0, 'whatsapp_datetime')){
            $dateTime = Vtiger_Util_Helper::formatDateDiffInStrings($adb->query_result($query, 0, 'whatsapp_datetime'));
        }else{
            $dateTime == '';
        }

        $whatsappData = array('body' => $body, 'dateTime' => $dateTime);

        return $whatsappData;
    }

    //function for get image details
    public function getImageDetails($recordId, $setype) {
        global $root_directory;
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
                $imagePath = $imagePath.$imageId.'_'.$imageName;
            }
        }
        return $imagePath;
    }

    //function for get read unread whatsapp message count
    public function getWhatsappUnReadMessageCounts($recordId){
        global $adb;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $query = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread' AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ? AND vtiger_ctwhatsappbusiness.message_type = 'Recieved'", array($recordId));
        $unreadCount = $adb->num_rows($query);

        $allMessageQuery = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ?", array($recordId));
        $rows = $adb->num_rows($allMessageQuery);

        $totalDays = 30;
        $avgMessageDay = $rows/$totalDays;

        $result = array('unreadCount' => $unreadCount, 'avgMessageDay' => number_format($avgMessageDay, 2));

        return $result;
    }

    //function for get last whatsapp message record
    public function getLastWhatsappMessageData($recordId){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currentUserID);
        $query = $adb->pquery($unreadQuery." AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ? ".$inNumberQuery." ORDER BY vtiger_ctwhatsappbusiness.whatsapp_datetime DESC LIMIT 0,1", array($recordId));

        $body = $adb->query_result($query, 0, 'message_body');
        if($adb->query_result($query, 0, 'whatsapp_datetime')){
            $dateTime = Vtiger_Util_Helper::formatDateDiffInStrings($adb->query_result($query, 0, 'whatsapp_datetime'));
        }else{
            $dateTime == '';
        }
        $whatsappData = array('body' => $body, 'dateTime' => $dateTime);

        return $whatsappData;
    }

    //function for get unknown whatsapp message record
    public function getUnknownMessagesData($start, $end, $searchValue){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getLogInNumberQuery($currentUserID);
        

        if($searchValue){
            $searchQuery = " AND vtiger_whatsappbusinesslog.whatsapplog_withccode LIKE '%".$searchValue."%'";
        }

        $query = $adb->pquery("SELECT vtiger_whatsappbusinesslog.whatsapplog_withccode, vtiger_whatsappbusinesslog.messagelog_body, vtiger_whatsappbusinesslog.whatsapplog_datetime FROM vtiger_whatsappbusinesslog 
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
            WHERE vtiger_crmentity.deleted = 0 AND (vtiger_whatsappbusinesslog.whatsapplog_withccode != 'Groups' AND vtiger_whatsappbusinesslog.whatsapplog_withccode != '') AND vtiger_whatsappbusinesslog.whatsapplog_contactid = '' ".$inNumberQuery.$customQuery.$searchQuery." GROUP BY vtiger_whatsappbusinesslog.whatsapplog_withccode ORDER BY vtiger_crmentity.createdtime DESC LIMIT 0, ".$start, array());
        $rows = $adb->num_rows($query);

        $newMessagearray = array();
        for ($i=0; $i < $rows; $i++) { 
            $label = $adb->query_result($query, $i, 'whatsapplog_withccode');
            $recordId = $label;
            $profileImage = 'layouts/v7/modules/CTWhatsAppBusiness/image/AvtarIcon.png';
            $imagetag = 1;
            $messageData = CTWhatsAppBusiness_Record_Model::getWhatsappUnReadNewMessageCounts($label);
            $unreadMessageCount = $messageData['unreadCount'];
            $avgMessageDay = $messageData['avgMessageDay'];

            /*$lastWhatsappMessageData = CTWhatsAppBusiness_Record_Model::getLastWhatsappNewMessageData($label);
            $lastBody = $lastWhatsappMessageData['body'];
            $lastDateTime = $lastWhatsappMessageData['dateTime'];*/

            $lastBody = $adb->query_result($query, $i, 'messagelog_body');
            $lastDateTime = Vtiger_Util_Helper::formatDateDiffInStrings($adb->query_result($query, $i, 'whatsapplog_datetime'));

            $newMessagearray[$label] = array('recordId' => $recordId, 'label' => $label, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'unreadMessageCount' => $unreadMessageCount, 'avgMessageDay' => $avgMessageDay, 'lastBody' => $lastBody, 'lastDateTime' => $lastDateTime);
        }
        return $newMessagearray;
    }

    //function for get modules record data
    public function getModuleMessagesData($whatsappmodule, $start, $end, $searchValue){
        global $adb;
        $whatsaappModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($whatsappmodule);
        $phoneField = $whatsaappModuleData['phoneField'];

        $moduleQuery = CTWhatsAppBusiness_Record_Model::moduleQuery($whatsappmodule, $phoneField, $searchValue);
        if($searchValue){
            $query = $adb->pquery($moduleQuery);
        }else{
            $query = $adb->pquery($moduleQuery.' LIMIT 0, '.$start);
        }
        
        $rows = $adb->num_rows($query);

        $moduleRecordData = array();
        $whatsapplogDatetime = '';
        for ($i=0; $i < $rows; $i++) { 
            $recordId = $adb->query_result($query, $i, 'crmid');
            $label = $adb->query_result($query, $i, 'whatsapplog_displayname');
            if($label == ''){
                $label = $adb->query_result($query, $i, 'label');
            }
            $setype = $adb->query_result($query, $i, 'setype');
            $profileImage = CTWhatsAppBusiness_Record_Model::getImageDetails($recordId, $setype);
            $imagetag = 1;
            if($profileImage == ''){
                $labelExplode = explode(' ', $label);
                $profileImage = mb_substr($labelExplode[0],0,1, "UTF-8").mb_substr($labelExplode[1],0,1, "UTF-8");
                $imagetag = 0;
            }
        $messageData = CTWhatsAppBusiness_Record_Model::getWhatsappUnReadMessageCounts($recordId);
        $unreadMessageCount = $messageData['unreadCount'];
        $avgMessageDay = $messageData['avgMessageDay'];

        $lastBody = $adb->query_result($query, $i, 'messagelog_body');
        
        $whatsapplogDatetime = $adb->query_result($query, $i, 'whatsapplog_datetime');
        if($whatsapplogDatetime){
            $lastDateTime = Vtiger_Util_Helper::formatDateDiffInStrings($whatsapplogDatetime);
        }else{
            $lastDateTime = '';
        }

        $moduleRecordData[] = array('recordId' => $recordId, 'label' => $label, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'unreadMessageCount' => $unreadMessageCount, 'avgMessageDay' => $avgMessageDay,'lastBody' => $lastBody, 'lastDateTime' => $lastDateTime);
        }
        return $moduleRecordData;
    }

    public function getImportantMessageDetail($phone){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
            WHERE vtiger_whatsappbusinesslog.whatsapplog_withccode = ?", array($phone));
        $important = $adb->query_result($query, 0, 'whatsapplog_important');
        return $important;
    }

    //function for get modules record data and whatsapp messages
    public function getModuleRecordData($recordId, $setype, $whatsappModule, $groupid, $groupWhatsappNumber){
        global $adb;
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $setype);
        $label = $recordModel->get('label');

        $phoneFieldData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($setype);
        $phoneField = $phoneFieldData['phoneField'];
        $phone = $recordModel->get($phoneField);

        $profileImage = CTWhatsAppBusiness_Record_Model::getImageDetails($recordId, $setype);
        $imagetag = 1;
        if($profileImage == ''){
            $labelExplode = explode(' ', $label);
            $profileImage = mb_substr($labelExplode[0],0,1, "UTF-8").mb_substr($labelExplode[1],0,1, "UTF-8");
          $imagetag = 0;
        }

        $getWhatsappMessages = CTWhatsAppBusiness_Record_Model::getWhatsappMessages($recordId, $setype, $whatsappModule, $groupid, $groupWhatsappNumber);
        $whatsappMessageHTML = $getWhatsappMessages['whatsappMessageHTML'];
        $totalSent = $getWhatsappMessages['totalSent'];
        $totalReceived = $getWhatsappMessages['totalReceived'];
        $recentComments = $getWhatsappMessages['commentHTML'];
        $moduleIsEnable = $getWhatsappMessages['moduleIsEnable'];

        $messageImportant = CTWhatsAppBusiness_Record_Model::getImportantMessageDetail($phone);

        $tabid = getTabid($setype);
        $query = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? AND summaryfield = ? AND uitype NOT IN ('10', '51')", array($tabid, 1));
        $rows = $adb->num_rows($query);
        $keyFields = array();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $setype);
        $commentModule = CTWhatsAppBusiness_Record_Model::checkCommentModuleEnable($setype);
        $ModCommentsEnable = CTWhatsAppBusiness_Record_Model::checkPermissionModule('ModComments');

        $actionName = 'EditView';
        if(!Users_Privileges_Model::isPermitted($setype, $actionName, $recordId)) {
            $permissionRecord = "0";
        }else{
            $permissionRecord = "1";
        }

        $themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();
        $keyFieldsHTML = '';
        if($permissionRecord == 1){
            for ($i=0; $i < $rows; $i++) { 
                if($i < 4){
                    $fieldlabel = vtranslate($adb->query_result($query, $i, 'fieldlabel'), $setype);
                    $fieldname = $adb->query_result($query, $i, 'fieldname');

                    $fieldvalue = $recordModel->get($fieldname);
                    if($fieldname == 'assigned_user_id'){
                        $getusername = $adb->pquery("SELECT * FROM vtiger_users WHERE id=?", array($fieldvalue));
                        $userrows = $adb->num_rows($getusername);
                        if($userrows == 0){
                            $getgroup = $adb->pquery("SELECT * FROM vtiger_groups WHERE groupid=?", array($fieldvalue));
                            $grouprows = $adb->num_rows($getgroup);
                            $fieldvalue = $adb->query_result($getgroup, 0, 'groupname');
                        }else{
                            $fieldvalue = $adb->query_result($getusername, 0, 'first_name').' '.$adb->query_result($getusername, 0, 'last_name');
                        }
                    }
                    if($themeView == 'RTL'){
                        $keyFieldsHTML .= '<p><span class="">'.$fieldvalue.'</span> : '.$fieldlabel.'</p>';
                    }else{
                        $keyFieldsHTML .= '<p>'.$fieldlabel.' : <span class="">'.$fieldvalue.'</span></p>';
                    }
                }
            }
        }

        $relatedModules = CTWhatsAppBusiness_Record_Model::getRelatedModules($setype);

        $relatedModuleHTML = '';
        foreach ($relatedModules as $key => $value) {
            $relatedModule = vtranslate($value['relatedModule'], $value['relatedModule']);
            $moduleURL = 'index.php?module='.$value['relatedModule'].'&view=QuickCreateAjax&'.$value['relatedFieldName'].'='.$recordId;
            $relatedModuleHTML .= '<a id="menubar_quickCreate_'.$value['relatedModule'].'" class="dropdown-item quickCreateModule" href="#" data-name="'.$value['relatedModule'].'" data-url="'.$moduleURL.'">'.$relatedModule.'</a>';
        }

        $getBotMobile = CTWhatsAppBusiness_Record_Model::getPreQuestionDetail($phone);
        $activeBot = $getBotMobile['activeBot'];
        $manualtransfer = $getBotMobile['manualtransfer'];
    
        $moduleRecrodData = array('recordId' => $recordId, 'label' => $label, 'phone' => $phone, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'whatsappMessages' => $whatsappMessageHTML, 'keyFieldsHTML' => $keyFieldsHTML, 'totalSent' => $totalSent, 'totalReceived' => $totalReceived, 'recentComments' => $recentComments, 'relatedModuleHTML' => $relatedModuleHTML, 'messageImportant' => $messageImportant, 'setype' => $setype, 'commentModule' => $commentModule, 'moduleIsEnable' => $moduleIsEnable, 'ModCommentsEnable' => $ModCommentsEnable, 'permissionRecord' => $permissionRecord, 'manualtransfer' => $manualtransfer, 'activeBot' => $activeBot);
        return $moduleRecrodData;
    }

    public function getPreQuestionDetail($phone){
        global $adb;
        $roboticmodeQuery = $adb->pquery("SELECT * FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($phone));
        $rows = $adb->num_rows($roboticmodeQuery);
        $manualtransfer = $adb->query_result($roboticmodeQuery, 0, 'manualtransfer');
        $preQuestionDetail = array('manualtransfer' => $manualtransfer, 'activeBot' => $rows);
        return $preQuestionDetail;
    }

    public function getWhatsAppRelatedRecord($recordId, $setype, $whatsappModule, $groupid, $nextWhatsappRelatedMessage){
        $getWhatsappMessages = CTWhatsAppBusiness_Record_Model::getRelatedModuleWhatsappMessages($recordId, $setype, $whatsappModule, $groupid, $nextWhatsappRelatedMessage);
        $whatsappMessageHTML = $getWhatsappMessages['whatsappMessageHTML'];

        $moduleRecrodData = array('whatsappMessages' => $whatsappMessageHTML);
        return $moduleRecrodData;
    }

    public function getRelatedModuleWhatsappMessages($recordId, $setype, $whatsappModule, $groupid, $nextWhatsappRelatedMessage){
        global $adb, $current_user;
        $userID = $current_user->id;
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($userID);
        $whatsappScanNo = $configurationData['whatsappno'];

        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($userID);
        if($nextWhatsappRelatedMessage == ''){
            $nextWhatsappRelatedMessage = 0;
        }
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery(); 
        $customQuery = " AND whatsapp_contactid = ? ORDER BY whatsapp_datetime ASC LIMIT ".$nextWhatsappRelatedMessage.", 5";
        $query = $adb->pquery($unreadQuery.$customQuery, array($recordId));
        $rows = $adb->num_rows($query);

        $whatsappMessageHTML = '';

        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
        $pdfExts = array("pdf");
        $fileExts = array("txt", "php", "zip", "csv", "https");
        $mp3Exts = array("mp3");
        $excelExts = array("xls");
        $wordlExts = array("docx", "doc");

        for ($i=0; $i < $rows; $i++) { 
            $ctWhatsappId = $adb->query_result($query, $i, 'ctwhatsappid');
            $messageImportant = $adb->query_result($query, $i, 'whatsapp_important');
            $messageType = $adb->query_result($query, $i, 'message_type');
            $messageReadUnRead = $adb->query_result($query, $i, 'whatsapp_unreadread');
            $messageSenderame = $adb->query_result($query, $i, 'whatsapp_sendername');
            $isGroup = $adb->query_result($query, $i, 'whatsapp_withccode');
            $your_number = $adb->query_result($query, $i, 'your_number');
            $getNumberDetails = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($your_number);
            $getNumberUsername = $getNumberDetails['username'];
            $quotemessage = $adb->query_result($query, $i, 'whatsapp_quotemessage');
            $msgid = $adb->query_result($query, $i, 'msgid');
            $documentBody = $adb->query_result($query, $i, 'message_body');

            $messageBody = nl2br(preg_replace("#\*([^*]+)\*#", "<b>$1</b>", $adb->query_result($query, $i, 'message_body')));
          
            $urlExt = pathinfo($messageBody, PATHINFO_EXTENSION);
            if (in_array($urlExt, $imgExts)) {
                $messageBody = '<image src="'.$messageBody.'" style="height: 60px !important;cursor: pointer;">';
            }else if(in_array($urlExt, $fileExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
            }else if(in_array($urlExt, $pdfExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png"></a>';
            }else if(in_array($urlExt, $excelExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png"></a>';
            }else if(in_array($urlExt, $wordlExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg"></a>';
            }else if(in_array($urlExt, $mp3Exts)){
                $messageBody = ' <audio controls>
                        <source src="'.$messageBody.'" type="audio/ogg">
                        <source src="'.$messageBody.'" type="audio/mpeg">
                      Your browser does not support the audio element.
                    </audio> ';
            }

            if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                $replyMessageHTML = '';
                $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                $notReplyWhatsapp = '0';
            }else{
                if($whatsappModule != "Groups"){
                    $copyMessage = '&nbsp;&nbsp;
                        <span class="copyMessageBody" data-copymessage="'.$messageBody.'">
                          <img style="float: left;margin-right: 10px;width: 15px; !important" src="layouts/v7/modules/CTWhatsAppBusiness/image/copy.png" title="'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'"><p>'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'</p>
                        </span>';
                    $notReplyWhatsapp = '1';
                }
                $whatsAppFileName = '';
            } 

            $createdTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($query, $i, 'whatsapp_datetime'));
            if($messageType == 'Send' || $messageType == 'Mass Message'){
                $totalSent = $totalSent + 1;
                $whatsappMessageHTML .= '<div class="sendChat">
                          <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                          </div>
                          <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                          </div>
                          <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">
                            <div class="mainMessageDiv">';
                            if($notReplyWhatsapp != '0'){
                                $whatsappMessageHTML .= '<div class="dropdown">
                                                      <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="float: right !important;">
                                                        <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                                                      </div>
                                                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                      <div class="dropdownInnerMenu">
                                                        <a>';
                                                          $whatsappMessageHTML .= $replyMessageHTML."";
                                $whatsappMessageHTML .= '</a>
                                                        <a>';
                                                          $whatsappMessageHTML .= $copyMessage."";
                                $whatsappMessageHTML .= '</a>
                                                      </div>
                                                      </div>
                                                    </div>';
                            }
                            $whatsappMessageHTML .= '<div class="bubble send" data-whatsappid='.$ctWhatsappId.'>';
                              if($quotemessage != ''){
                                $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                              }
                              if($isGroup == 'Groups'){
                                $whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
                              }
                              $whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p>
                            </div>
                            </div>
                            <span class="chatTime"><b><a class="fa fa-eye" href="index.php?module=CTWhatsAppBusiness&view=Detail&record='.$ctWhatsappId.'" target="_black"></a>&nbsp;'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
                            if($messageReadUnRead == 'Read'){
                              $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png">';
                            }else{
                              $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png">';
                            }
              $whatsappMessageHTML .= '</span></div>
                        </div>';

            }else if($messageType == 'Recieved'){
                $totalReceived = $totalReceived + 1;
                $whatsappMessageHTML .= '<div class="replyChat">
                          <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">';
                $whatsappMessageHTML .= '<div class="bubble reply" data-whatsappid='.$ctWhatsappId.'>';
                if($quotemessage != ''){
                    $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                }
                if($isGroup == 'Groups'){
                    $whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
                }
                $whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p></div>';

                if($setype){
                    if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                        $whatsappMessageHTML .= '';
                    }else{
                        if(Users_Privileges_Model::isPermitted($setype, 'EditView', $recordId)) {
                            $whatsappMessageHTML .= '&nbsp;&nbsp;
                                              <span class="editField" style="cursor: pointer;">
                                                <img style="float: left;width: 15px; !important" src="layouts/v7/modules/CTWhatsAppBusiness/image/editcontent.png" title="'.vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness').' '.vtranslate($setype, $setype).'"><p>'.vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness').' '.vtranslate($setype, $setype).'</p>
                                              </span>';
                        }
                    }
                }
                if($notReplyWhatsapp != '0'){
                    $whatsappMessageHTML .= '<div class="dropdown" style="display: inline-block !important;margin: 0px 0px 0px 12px;">
                    <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true">
                    <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                    </div>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <div class="dropdownInnerMenu">';                         
                    $whatsappMessageHTML .= '<a>'.$replyMessageHTML.'</a><a>'.$copyMessage.'</a><a>'.$createTaskMessage.'</a>';  
                    $whatsappMessageHTML .= '</div></div></div>';
                }

                $whatsappMessageHTML .= '<span class="chatTime" style="width: 100%; !important"><b><a class="fa fa-eye" href="index.php?module=CTWhatsAppBusiness&view=Detail&record='.$ctWhatsappId.'" target="_black"></a>&nbsp;'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
                if($messageReadUnRead == 'Read'){
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png">';
                }else{
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png">';
                }
                $whatsappMessageHTML .= '</span></div>';
                $whatsappMessageHTML .= '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                </div>

                </div>';
            }
      
        }
        $whatsappMessageHTML .= '';
        $messageData = array('whatsappMessageHTML' => $whatsappMessageHTML, 'rows' => $rows);
        return $messageData;
    }

    //function for get modules record data
    public function getMessagesRecordData($recordId, $whatsappModule, $groupid, $groupWhatsappNumber){
        global $adb;
        $label = $recordId;
        if($groupid){
            $profileImage = 'layouts/v7/modules/CTWhatsAppBusiness/image/groups.png';
        }else{
            $profileImage = 'layouts/v7/modules/CTWhatsAppBusiness/image/AvtarIcon.png';
        }
        $imagetag = 1;

        $getWhatsappMessages = CTWhatsAppBusiness_Record_Model::getWhatsappMessages($recordId, $setype, $whatsappModule, $groupid, $groupWhatsappNumber);
        $whatsappMessageHTML = $getWhatsappMessages['whatsappMessageHTML'];
        $totalSent = $getWhatsappMessages['totalSent'];
        $totalReceived = $getWhatsappMessages['totalReceived'];
        $messageImportant = $getWhatsappMessages['messageImportant'];
        $moduleIsEnable = $getWhatsappMessages['moduleIsEnable'];
        $recentComments = '';

        $themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();
        if($themeView == 'RTL'){
            $keyFieldsHTML = '<p><span class="">'.$recordId.'</span> : '.vtranslate('LBL_PHONE_NUMBER', 'Vtiger').'</p>';
        }else{
            $keyFieldsHTML = '<p>'.vtranslate('LBL_PHONE_NUMBER', 'Vtiger').' : <span class="">'.$recordId.'</span></p>';
        }

        $relatedModules = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModules();
        $commentModule = CTWhatsAppBusiness_Record_Model::checkCommentModuleEnable($setype);
        $ModCommentsEnable = CTWhatsAppBusiness_Record_Model::checkPermissionModule('ModComments');
        $relatedModuleHTML = '';

        if($whatsappModule == 'Groups'){    
            $relatedModuleHTML .= '<a id="menubar_quickCreate_HelpDesk" class="dropdown-item quickCreateModule" href="#" data-name="HelpDesk" data-url="index.php?module=HelpDesk&view=QuickCreateAjax">'.vtranslate('LBL_CREATE', 'CTWhatsAppBusiness').' '.vtranslate('HelpDesk', 'HelpDesk').'</a>';
        
        }else{
            foreach ($relatedModules as $key => $value) {
                $relatedModule = vtranslate($value['module'], $value['module']);
                $moduleURL = 'index.php?module='.$value['module'].'&view=QuickCreateAjax&'.$value['phoneField'].'='.$recordId;
                $relatedModuleHTML .= '<a id="menubar_quickCreate_'.$value['module'].'" class="dropdown-item quickCreateModule" href="#" data-name="'.$value['module'].'" data-url="'.$moduleURL.'">'.vtranslate('LBL_CREATE', 'Vtiger').' '.$relatedModule.'</a>';

                $relatedModuleHTML .= '<a id="menubar_quickCreate_'.$value['module'].'" class="dropdown-item quickUpdateModule" href="#" data-name="'.$value['module'].'" data-url="'.$moduleURL.'">'.vtranslate('LBL_UPDATES', 'CTWhatsAppBusiness').' '.$relatedModule.'</a>';
            }
        }

        $getBotMobile = CTWhatsAppBusiness_Record_Model::getPreQuestionDetail($recordId);
        $activeBot = $getBotMobile['activeBot'];
        $manualtransfer = $getBotMobile['manualtransfer'];

        $moduleRecrodData = array('label' => $label, 'phone' => $recordId, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'whatsappMessages' => $whatsappMessageHTML, 'totalSent' => $totalSent, 'totalReceived' => $totalReceived, 'keyFieldsHTML' => $keyFieldsHTML, 'recentComments' => $recentComments, 'relatedModuleHTML' => $relatedModuleHTML, 'messageImportant' => $messageImportant, 'commentModule' => $commentModule, 'moduleIsEnable' => $moduleIsEnable, 'ModCommentsEnable' => $ModCommentsEnable, 'manualtransfer' => $manualtransfer, 'activeBot' => $activeBot);
        return $moduleRecrodData;
    }

    public function getInNumberQuery($userid){
        $whatsAppAllNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedWhatsappNumber($userid);
        $inNumber = '';
        foreach ($whatsAppAllNumber as $key => $value) {
            $inNumber .= "'".$value['whatsappno']."',";
        }
        $allnumber = rtrim($inNumber, ',');
        $inNumberQuery = ' AND vtiger_ctwhatsappbusiness.your_number IN ('.$allnumber.') ';
        return $inNumberQuery;
    }

    public function getLogInNumberQuery($userid){
        $whatsAppAllNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedWhatsappNumber($userid);
        $inNumber = '';
        foreach ($whatsAppAllNumber as $key => $value) {
            $inNumber .= "'".$value['whatsappno']."',";
        }
        $allnumber = rtrim($inNumber, ',');
        $inNumberQuery = ' AND vtiger_whatsappbusinesslog.whatsapplog_your_number IN ('.$allnumber.') ';
        return $inNumberQuery;
    }

    public function getFilenameWhatsappMessage($body){
        $expolodeFilename = explode('/', $body);
        $lenth = count($expolodeFilename) - 1;
        return $expolodeFilename[$lenth];
    }

    //function for get whatsapp message record data
    public function getWhatsappMessages($recordId, $setype, $whatsappModule, $groupid, $groupWhatsappNumber){
        global $adb, $current_user;
        $userID = $current_user->id;
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($userID);
        $whatsappScanNo = $configurationData['whatsappno'];

        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($userID);
    
        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery(); 
        if($whatsappModule == "Groups"){
            $query = $adb->pquery("SELECT * FROM (
                  SELECT * FROM vtiger_ctwhatsappbusiness 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
                WHERE vtiger_crmentity.deleted = 0 AND msgid = ? ".$inNumberQuery." ORDER BY whatsapp_datetime DESC LIMIT 0,25
              ) wp_group
              ORDER BY ctwhatsappid ASC", array($groupid));
        }else{
            if($setype){
                $query = $adb->pquery("SELECT * FROM (
                  SELECT * FROM vtiger_ctwhatsappbusiness 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
                WHERE vtiger_crmentity.deleted = 0 AND whatsapp_contactid = ? ".$inNumberQuery." ORDER BY whatsapp_datetime DESC LIMIT 0,25 ) wp_group ORDER BY ctwhatsappid ASC", array($recordId));
            }else{
                $recordId = preg_replace('/[^A-Za-z0-9]/', '', $recordId);
                $query = $adb->pquery('SELECT * FROM (
                  SELECT * FROM vtiger_ctwhatsappbusiness 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
                WHERE vtiger_crmentity.deleted = 0 AND whatsapp_withccode LIKE "%'.$recordId.'%" '.$inNumberQuery.' ORDER BY whatsapp_datetime DESC LIMIT 0,25 ) wp_group ORDER BY ctwhatsappid ASC', array());
            }
        }

        $rows = $adb->num_rows($query);

        $totalSent = 0;
        $totalReceived = 0;
        $whatsappMessage = array();

        $whatsappMessageHTML = '<div class="chatDiv">';

        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
        $pdfExts = array("pdf");
        $fileExts = array("txt", "php", "zip", "csv", "https");
        $mp3Exts = array("mp3");
        $excelExts = array("xls");
        $wordlExts = array("docx", "doc");

        for ($i=0; $i < $rows; $i++) { 
            $ctWhatsappId = $adb->query_result($query, $i, 'ctwhatsappid');
            $messageImportant = $adb->query_result($query, $i, 'whatsapp_important');
            $messageType = $adb->query_result($query, $i, 'message_type');
            $messageReadUnRead = $adb->query_result($query, $i, 'whatsapp_unreadread');
            $messageSenderame = $adb->query_result($query, $i, 'whatsapp_sendername');
            $isGroup = $adb->query_result($query, $i, 'whatsapp_withccode');
            $your_number = $adb->query_result($query, $i, 'your_number');
            $getNumberDetails = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($your_number);
            $getNumberUsername = $getNumberDetails['username'];
            $quotemessage = $adb->query_result($query, $i, 'whatsapp_quotemessage');
            $msgid = $adb->query_result($query, $i, 'msgid');
            $documentBody = $adb->query_result($query, $i, 'message_body');
            $whatsapp_chatid = $adb->query_result($query, $i, 'whatsapp_chatid');
            $whatsapp_contactid = $adb->query_result($query, $i, 'whatsapp_contactid');
            $setype = VtigerCRMObject::getSEType($whatsapp_contactid);
            if($setype == 'Contacts'){
                $relatedtotask = 'contact_id='.$whatsapp_contactid.'';
            }else{
                $relatedtotask = 'parent_id='.$whatsapp_contactid.'';
            }

            if($ctWhatsappId){
                if($messageReadUnRead == "Unread" && $messageType == 'Recieved'){
                    $recordModel = Vtiger_Record_Model::getInstanceById($ctWhatsappId, 'CTWhatsAppBusiness');
                    $recordModel->set('mode', 'edit');
                    $recordModel->set('id', $ctWhatsappId); 
                    $recordModel->set('whatsapp_unreadread', 'Read');
                    $recordModel->save();
                }
            }

            $themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();
            if($themeView == 'RTL'){
                $taskstyle = 'style="float: right;margin-left: 10px;width: 15px; !important;cursor: pointer;"';
                $menuicon = 'margin: 0px 25px 0px 0px;';
                $menuwidth = 'min-width: 110px;';
                $menumargin = 'margin: 1px -81px 0px 0px';
            }else{
                $taskstyle = 'style="float: left;margin-right: 0px;width: 15px; !important;cursor: pointer;"';
                $menuicon = 'margin: 0px 0px 0px 18px;';
                $menuwidth = 'min-width: 100px;';
                $menumargin = 'margin: 2px 0px 0px 10px;';
            }

            $messageBody = nl2br(preg_replace("#\*([^*]+)\*#", "<b>$1</b>", $adb->query_result($query, $i, 'message_body')));
      
            $urlExt = pathinfo($messageBody, PATHINFO_EXTENSION);
            if (in_array($urlExt, $imgExts)) {
                $messageBody = '<image src="'.$messageBody.'" style="height: 60px !important;cursor: pointer;">';
            }else if(in_array($urlExt, $fileExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
            }else if(in_array($urlExt, $pdfExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png"></a>';
            }else if(in_array($urlExt, $excelExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png"></a>';
            }else if(in_array($urlExt, $wordlExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg"></a>';
            }else if(in_array($urlExt, $mp3Exts)){
                $messageBody = ' <audio controls>
                    <source src="'.$messageBody.'" type="audio/ogg">
                    <source src="'.$messageBody.'" type="audio/mpeg">
                  Your browser does not support the audio element.
                </audio> ';
            }

            if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                $replyMessageHTML = '';
                $copyMessage = '';
                $createTaskMessage = '';
                $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                $notReplyWhatsapp = '0';
            }else{
                if($whatsappModule != "Groups"){
                    $replyMessageHTML = '
                        <span class="replyMessageBody" data-replymessage="'.$messageBody.'" data-replymessageid="'.$msgid.'">
                        <img style="float: left;width: 15px; !important" src="layouts/v7/modules/CTWhatsAppBusiness/image/reply.png" title="'.vtranslate("LBL_REPLY", 'CTWhatsAppBusiness').'"><p>'.vtranslate("LBL_REPLY", 'CTWhatsAppBusiness').'</p>
                        </span>';
                    if($setype){
                        if($whatsapp_chatid){
                            $createTaskMessage = '
                            <span '.$taskstyle.'>
                            <a href="index.php?module=Calendar&view=Detail&record='.$whatsapp_chatid.'" target="_blank"><span><img class="taskid" style="width: 15px;" src="layouts/v7/modules/CTWhatsAppBusiness/image/watch.jpg" title="'.vtranslate("LBL_VIEW", 'Vtiger').'"><p>'.vtranslate("LBL_VIEW", 'Vtiger').'</p></span></a>
                            </span>';
                        }else{
                            $createTaskMessage = '
                            <span class="taskMessageBody quickCreateTaskModule" data-task="yes" data-whatsappid="'.$ctWhatsappId.'"  data-url="index.php?module=Calendar&view=QuickCreateAjax&'.$relatedtotask.'&description='.$messageBody.'" data-taskmessage="'.$messageBody.'">
                            <img '.$taskstyle.' src="layouts/v7/modules/CTWhatsAppBusiness/image/watch.jpg" title="'.vtranslate("LBL_CREATE", 'Vtiger').'"><p>'.vtranslate("LBL_CREATE", 'Vtiger').'</p>
                            </span>';
                        }   
                    }
                }
                $whatsAppFileName = '';
                $copyMessage = '
                        <span class="copyMessageBody" data-copymessage="'.$messageBody.'">
                        <img style="float: left;width: 15px; !important" src="layouts/v7/modules/CTWhatsAppBusiness/image/copy.png" title="'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'"><p>'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'</p>
                        </span>';
                $notReplyWhatsapp = '1';
            }

            $createdTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($query, $i, 'whatsapp_datetime'));
            $whatsappMessage[] = array('messageType' => $messageType, 'messageBody' => $messageBody, 'createdTime' => $createdTime);

            if($messageType == 'Send' || $messageType == 'Mass Message'){
                $totalSent = $totalSent + 1;
                $whatsappMessageHTML .= '<div class="sendChat">
                    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                    </div>
                    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                    </div>
                    <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">
                    <div class="mainMessageDiv">';

                if($notReplyWhatsapp != 0){
                    $whatsappMessageHTML .= '<div class="dropdown" style="width: max-content !important;">
                                              <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="float: right !important;cursor: pointer;">
                                                <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                                              </div>
                                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="'.$menuwidth.'">
                                              <div class="dropdownInnerMenu">
                                                <a>';
                    $whatsappMessageHTML .= $replyMessageHTML."";
                    $whatsappMessageHTML .= '</a><a>';
                    $whatsappMessageHTML .= $copyMessage."";
                    $whatsappMessageHTML .= '</a>
                                              </div>
                                              </div>
                                            </div>';
                }
                $whatsappMessageHTML .= '<div class="bubble send" data-whatsappid='.$ctWhatsappId.'>';
                  if($quotemessage != ''){
                        $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                  }
                  if($isGroup == 'Groups'){
                        $whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
                  }
                  $whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p></div></div><span class="chatTime"><b>'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
                if($messageReadUnRead == 'Read'){
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png">';
                }else{
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png">';
                }
                $whatsappMessageHTML .= '</span></div></div>';

            }else if($messageType == 'Recieved'){
                $totalReceived = $totalReceived + 1;
                $whatsappMessageHTML .= '<div class="replyChat">
                      <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">';
                $whatsappMessageHTML .= '<div class="bubble reply" data-whatsappid='.$ctWhatsappId.'>';
                if($quotemessage != ''){
                    $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                }
                if($isGroup == 'Groups'){
                    $whatsappMessageHTML .= '<span><b>'.$messageSenderame.'</b></span>';
                }
                $whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p></div>';

                if($notReplyWhatsapp != 0){
                    $whatsappMessageHTML .= '<div class="dropdown" style="display: inline-block !important;width: max-content !important;'.$menuicon.'">
                              <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="cursor: pointer;">
                                <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                              </div>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="'.$menuwidth.'">
                              <div class="dropdownInnerMenu">';
                    if($isGroup != 'Groups'){
                        if($whatsapp_contactid){
                            $whatsappMessageHTML .= '<a>'.$replyMessageHTML.'</a><a>'.$copyMessage.'</a><a>'.$createTaskMessage.'</a>';
                            if($setype){
                                if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                                    $whatsappMessageHTML .= '';
                                }else{
                                    if($isGroup != 'Groups'){
                                        if($whatsapp_contactid){
                                            if(Users_Privileges_Model::isPermitted($setype, 'EditView', $whatsapp_contactid)) {
                                                $whatsappMessageHTML .= '<a>
                                                    <span class="editField" data-messagebody="'.$messageBody.'" style="cursor: pointer;">
                                                        <img style="float: left;width: 15px; !important; '.$menumargin.'" src="layouts/v7/modules/CTWhatsAppBusiness/image/editcontent.png" title="'.vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness').' '.vtranslate($setype, $setype).'"><p>'.vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness').' '.vtranslate($setype, $setype).'</p>
                                                    </span>
                                                </a>';
                                            }
                                        }
                                    }
                                }
                            }                    
                        }else{
                            $whatsappMessageHTML .= '<a>'.$replyMessageHTML.'</a><br><a>'.$copyMessage.'</a><br>';  
                        }
                    }else{
                        $whatsappMessageHTML .= '<a>'.$copyMessage.'</a><br>';  
                    }
                    $whatsappMessageHTML .= '</div></div></div>';
                }

                $whatsappMessageHTML .= '<span class="chatTime" style="width: 100%; !important"><b>'.$your_number.'('.$getNumberUsername.') - </b>'.$createdTime.'';
                if($messageReadUnRead == 'Read'){
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png">';
                }else{
                    $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png">';
                }
                $whatsappMessageHTML .= '</span></div>';
                  $whatsappMessageHTML .= '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div></div>';
            }
      
        }
        $whatsappMessageHTML .= '</div>';

        $moduleIsEnable = CTWhatsAppBusiness_Record_Model::getmoduleIsEnable($setype);

        if($setype){
            $pagingModel = new Vtiger_Paging_Model();
            $recentComments = ModComments_Record_Model::getRecentComments($recordId, $pagingModel);

            $comments = array();
            $commentHTML = '';
            foreach ($recentComments as $key => $value) {
                if($key < 2){
                    $commentcontent = $recentComments[$key]->get('commentcontent');
                    $createdtime = $recentComments[$key]->get('createdtime');
                    $smownerid = $recentComments[$key]->get('smownerid');

                    $commentHTML .= '<div class="comment1">
                            <!-- <div class="pic"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pic4.png" /></div> -->
                            <div class="pName">
                              <div class="pText">
                                <span>'.Vtiger_Functions::getUserName($smownerid).'</span>
                                <span class="time">'.Vtiger_Util_Helper::formatDateDiffInStrings($createdtime).'</span>
                                <p>'.decode_html($commentcontent).'</p>
                              </div>
                            </div>
                          </div>';

                }
                if($key > 3){
                    $moreCommentLink = "<span class='pull-right' style='color: blue;'><a href='index.php?module=".$setype."&relatedModule=ModComments&view=Detail&record=".$recordId."&mode=showRelatedList' target='_black'>".vtranslate('LBL_SHOW_MORE','Vtiger')."</a><span>";
                }
            }
        }

        $messageData = array('whatsappMessageHTML' => $whatsappMessageHTML, 'totalSent' => $totalSent, 'totalReceived' => $totalReceived, 'commentHTML' => $commentHTML.$moreCommentLink, 'messageImportant' => $messageImportant, 'moduleIsEnable' => $moduleIsEnable);
        return $messageData;
    }

  //function for get related modules
  function getRelatedModules($moduleName){
    global $adb;
    $tabid = getTabid($moduleName);
    $getRelatedModuleQuery = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid = ? AND presence = 0 AND related_tabid NOT IN(8,9,10,20,21,22,23,35,43) AND actions LIKE '%ADD%'", array($tabid));
    $relatedModulesRows = $adb->num_rows($getRelatedModuleQuery);
    $relatedModuleArray = array();
    for ($j=0; $j < $relatedModulesRows; $j++) {
      $relatedModuleTabid = $adb->query_result($getRelatedModuleQuery, $j, 'related_tabid');
      $getModuleNameQuery = $adb->pquery("SELECT * FROM vtiger_tab WHERE tabid = ? AND presence = 0", array($relatedModuleTabid));
      $relatedModule = $adb->query_result($getModuleNameQuery, 0, 'name');
      if($relatedModule != ''){
        $data = CTWhatsAppBusiness_Record_Model::checkPermissionModule($relatedModule);
        if($data == 1){
          $getRelatedFieldNameQuery = $adb->pquery("SELECT vtiger_field.fieldname FROM vtiger_relatedlists INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_relatedlists.relationfieldid WHERE vtiger_relatedlists.related_tabid = ? AND vtiger_relatedlists.tabid = ?", array($relatedModuleTabid, $tabid));
          $relatedFieldName = $adb->query_result($getRelatedFieldNameQuery, 0, 'fieldname');
          $relatedModuleArray[$relatedModule] = array('relatedModule' => $relatedModule, 'relatedFieldName' => $relatedFieldName);
        }
      }
    }
    return $relatedModuleArray;
  }

    //function for check module permission
    public function checkPermissionModule($moduleName) {
        $moduleName = $moduleName;
        $record = '';

        $actionName = 'DetailView';
        if(!Users_Privileges_Model::isPermitted($moduleName, $actionName, $record)) {
            return 0;
        }else{
            return 1;
        }
    }

    //function for message is important
    public function setMessagesImportant($recordId, $messagesImportant) {
        global $adb;
        $setype = VtigerCRMObject::getSEType($recordId);
        if($messagesImportant == 0){
            $query = "UPDATE vtiger_whatsappbusinesslog SET whatsapplog_important = 1";
        }else{
            $query = "UPDATE vtiger_whatsappbusinesslog SET whatsapplog_important = 0";
        }
        if($setype){
            $customQuery = ' WHERE whatsapplog_contactid = ?';
        }else{
            $customQuery = ' WHERE whatsapplog_withccode = ?';
        }
        $updateQuery = $adb->pquery($query.$customQuery, array($recordId));
        return 1;
    }

    //function for get unread whatsapp modules record data
    public function getAllNewUnreadMessages($recordId, $moduleName, $individulMessage, $lastMessageID){
        global $adb, $current_user;
        $setype = VtigerCRMObject::getSEType($recordId);
        $userID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($userID);

        if($setype){
            $customQuery = ' AND whatsapp_contactid = ? AND vtiger_ctwhatsappbusiness.message_type IN ("Recieved")';
        }else{
            if (strpos($recordId, '@g.us') !== false) {
                $customQuery = ' AND msgid = ?';
            }else{
                $customQuery = ' AND whatsapp_withccode = ? AND vtiger_ctwhatsappbusiness.message_type IN ("Recieved")';
            }
        }

        $unreadQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        if($individulMessage == 1){
            $query = $unreadQuery.$customQuery.$inNumberQuery." AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread' AND vtiger_ctwhatsappbusiness.message_type IN ('Recieved') ORDER BY ctwhatsappid DESC LIMIT 0,1";
        }else{
            if($lastMessageID){
                $query = $unreadQuery.$customQuery.$inNumberQuery." AND ctwhatsappid > ".$lastMessageID;
            }else{
                $query = $unreadQuery.$customQuery.$inNumberQuery;
            }
        }

        $queryResult = $adb->pquery($query, array($recordId));
        $rows = $adb->num_rows($queryResult);
        $whatsappMessageHTML = '';

        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
        $pdfExts = array("pdf");
        $fileExts = array("txt", "php", "zip", "csv", "https");
        $mp3Exts = array("mp3");
        $excelExts = array("xls");
        $wordlExts = array("docx", "doc");

        for ($i=0; $i < $rows; $i++) {
            $replyMessageHTML = '';
            $copyMessage = '';
            $createTaskMessage = '';
            $ctwhatsappId = $adb->query_result($queryResult, $i, 'ctwhatsappid');
            $senderName = $adb->query_result($queryResult, $i, 'whatsapp_sendername');
            $messageType = $adb->query_result($queryResult, $i, 'message_type');
            $messageReadUnRead = $adb->query_result($queryResult, $i, 'whatsapp_unreadread');
            $isGroup = $adb->query_result($queryResult, $i, 'whatsapp_withccode');
            $your_number = $adb->query_result($queryResult, $i, 'your_number');
            $quotemessage = $adb->query_result($queryResult, $i, 'whatsapp_quotemessage');
            $documentBody = $adb->query_result($queryResult, $i, 'message_body');
            $whatsapp_contactid = $adb->query_result($queryResult, $i, 'whatsapp_contactid');
            $setype = VtigerCRMObject::getSEType($whatsapp_contactid);
            if($setype == 'Contacts'){
                $relatedtotask = 'contact_id='.$whatsapp_contactid.'';
            }else{
                $relatedtotask = 'parent_id='.$whatsapp_contactid.'';
            }

            $msgid = $adb->query_result($queryResult, $i, 'msgid');
            $messageBody = nl2br(preg_replace("#\*([^*]+)\*#", "<b>$1</b>", $adb->query_result($queryResult, $i, 'message_body')));
            if($ctwhatsappId){
                if($messageReadUnRead == "Unread" && $messageType == 'Recieved'){
                    $recordModel = Vtiger_Record_Model::getInstanceById($ctwhatsappId, 'CTWhatsAppBusiness');
                    $recordModel->set('mode', 'edit');
                    $recordModel->set('id', $ctwhatsappId);
                    $recordModel->set('whatsapp_unreadread', 'Read');
                    $recordModel->save();
                }
            }

            $themeView = CTWhatsAppBusiness_Record_Model::getWhatsappTheme();
            if($themeView == 'RTL'){
                $taskstyle = 'style="float: right;width: 20px; !important;cursor: pointer;"';
            }else{
                $taskstyle = 'style="float: left;width: 20px; !important;cursor: pointer;"';
            }

            $urlExt = pathinfo($messageBody, PATHINFO_EXTENSION);
            if($urlExt){
                if (in_array($urlExt, $imgExts)) {
                    $messageBody = '<image src="'.$messageBody.'" style="height: 60px !important;cursor: pointer;">';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else if(in_array($urlExt, $fileExts)){
                    $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else if(in_array($urlExt, $pdfExts)){
                    $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png"></a>';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else if(in_array($urlExt, $excelExts)){
                    $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png"></a>';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else if(in_array($urlExt, $wordlExts)){
                    $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg"></a>';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else if(in_array($urlExt, $mp3Exts)){
                    $messageBody = ' <audio controls>
                    <source src="'.$messageBody.'" type="audio/ogg">
                    <source src="'.$messageBody.'" type="audio/mpeg">
                    Your browser does not support the audio element.
                    </audio> ';
                    $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($documentBody);
                }else{
                    $whatsAppFileName = '';
                }
                $notReplyWhatsapp = '0';
            }else{
                $messageBody = $messageBody;
                if($individulMessage == 1){
                    $style = " ";
                }else{
                    $style = "style='float: left;width: 15px;margin-right: 10px;'";
                }
                if($setype){
                    if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                        $whatsappMessageHTML .= '';
                    }else{
                        if(Users_Privileges_Model::isPermitted($setype, 'EditView', $whatsapp_contactid)) {
                            if($individulMessage == 1){
                                $editFieldHTML = "
                                <span class='editField'>
                                <img src='layouts/v7/modules/CTWhatsAppBusiness/image/editcontent.png' title='".vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness')." ".vtranslate($setype, $setype)."' ".$style.">
                                </span>";
                            }else{
                                $editFieldHTML = "
                                <span class='editField'>
                                <img src='layouts/v7/modules/CTWhatsAppBusiness/image/editcontent.png' title='".vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness')." ".vtranslate($setype, $setype)."' ".$style.">".vtranslate("LBL_EDITFIELD", 'CTWhatsAppBusiness')." ".vtranslate($setype, $setype)."
                                </span>";
                            }
                        }
                    }
                }
                if (in_array($urlExt, $imgExts) || in_array($urlExt, $fileExts) || in_array($urlExt, $pdfExts) || in_array($urlExt, $mp3Exts) || in_array($urlExt, $excelExts) || in_array($urlExt, $wordlExts)) {
                    $replyMessageHTML .= '';
                    $copyMessage .= '';
                    
                }else{
                    if($isGroup != 'Groups'){
                        $replyMessageHTML .= "<span class='replyMessageBody' data-replymessage='".$messageBody."' data-replymessageid='".$msgid."''>
                        <img src='layouts/v7/modules/CTWhatsAppBusiness/image/reply.png' title='".vtranslate('LBL_REPLY','CTWhatsAppBusiness')."' ".$style.">".vtranslate('LBL_REPLY','CTWhatsAppBusiness')."
                        </span>";

                    }
                    $copyMessage = '
                    <span class="copyMessageBody" data-copymessage="'.$messageBody.'">
                    <img '.$style.' src="layouts/v7/modules/CTWhatsAppBusiness/image/copy.png" title="'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'">'.vtranslate("LBL_COPY", 'CTWhatsAppBusiness').'
                    </span>';

                    $createTaskMessage = '
                    <span class="taskMessageBody quickCreateTaskModule" data-task="yes" data-whatsappid="'.$ctWhatsappId.'"  data-url="index.php?module=Calendar&view=QuickCreateAjax&'.$relatedtotask.'&description='.$messageBody.'" data-taskmessage="'.$messageBody.'">
                    <img '.$taskstyle.' src="layouts/v7/modules/CTWhatsAppBusiness/image/watch.jpg" title="'.vtranslate("LBL_CREATE", 'Vtiger').'">'.vtranslate("LBL_CREATE", 'Vtiger').'
                    </span>';
                }
                $whatsAppFileName = '';
                $notReplyWhatsapp = '1';
            }

            $createdTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($queryResult, $i, 'whatsapp_datetime'));

            if($individulMessage == 1){
                $whatsappMessageHTML .= '<div class="message received">
                '.$messageBody.'<br> '.urldecode($whatsAppFileName).'
                    <span class="metadata">
                <span class="time"><b>'.$your_number.'</b>&nbsp;&nbsp; '.$createdTime.'</span>&nbsp';

                if($messageReadUnRead == 'Unread'){
                            $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png"/>';
                }else{
                          $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png"/>';
                        }
                $whatsappMessageHTML .= '</span>&nbsp;
                </div>';
                $whatsappMessageHTML .= $editFieldHTML;
            }else{
                if($messageType == 'Send'){
                    $whatsappMessageHTML .= '<div class="sendChat">
                        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>
                        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>
                        <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">
                        <div class="mainMessageDiv">';
                    if($notReplyWhatsapp != '0'){
                        $whatsappMessageHTML .= '<div class="dropdown">
                              <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true" style="float: right !important;">
                                <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                              </div>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <div class="dropdownInnerMenu">
                                <a>';
                                    $whatsappMessageHTML .= $replyMessageHTML."";
                                    $whatsappMessageHTML .= '</a>
                                <a>';
                                    $whatsappMessageHTML .= $copyMessage."";
                                    $whatsappMessageHTML .= '</a>
                                </div>
                              </div>
                            </div>';
                    }
                    $whatsappMessageHTML .= '<div class="bubble send" data-whatsappid='.$ctwhatsappId.'>';
                    if($quotemessage != ''){
                        $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                    }
                    if($isGroup == 'Groups'){
                        $whatsappMessageHTML .= '<span><b>'.$senderName.'</b></span>';
                    }
                    $whatsappMessageHTML .= '<p style="word-wrap: break-word;">'.$messageBody.'<br> '.urldecode($whatsAppFileName).' </p>
                        </div>
                        </div>
                        <span class="chatTime" style="width: chatTime%; !important"><b>'.$your_number.'</b> - '.$createdTime.'';
                    if($messageReadUnRead == 'Unread'){
                        $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png"/>';
                    }else{
                        $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png"/>';
                    }
                    $whatsappMessageHTML .= '</span>&nbsp;</div>';
                    $whatsappMessageHTML .= '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                        </div>
                        </div>';
                }else{
                    $whatsappMessageHTML .= '<div class="replyChat">
                        <div class="col-xs-10 col-sm-8 col-md-8 col-lg-8">
                        <div class="bubble reply" data-whatsappid='.$ctwhatsappId.'>';
                    if($quotemessage != ''){
                        $whatsappMessageHTML .= '<div class="sendQuoteMessage"><p style="word-wrap: break-word;">'.$quotemessage.'</p></div>';  
                    }
                    if($isGroup == 'Groups'){
                        $whatsappMessageHTML .= '<span><b>'.$senderName.'</b></span>';
                    }
                    $whatsappMessageHTML .= '<p>'.$messageBody.'<br> '.urldecode($whatsAppFileName).'</p>
                    </div>';
                    $whatsappMessageHTML .= '<div class="dropdown" style="display: inline-block !important;margin: 0px 0px 0px 12px;">
                              <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="true">
                                <i class="fa fa-ellipsis-v icon" style="width: 20px;margin: 10px;cursor: pointer;"></i>
                              </div>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                              <div class="dropdownInnerMenu">';
                    if($isGroup == 'Groups'){
                        $whatsappMessageHTML .= '<a>'.$copyMessage.'</a>';
                    }else{
                        if($notReplyWhatsapp != '0'){
                            if($whatsapp_contactid){
                                $whatsappMessageHTML .= '<a>'.$editFieldHTML.'</a><a>'.$replyMessageHTML.'</a><a>'.$copyMessage.'</a><a>'.$createTaskMessage.'</a>';  
                            }else{
                                $whatsappMessageHTML .= '<a>'.$replyMessageHTML.'</a><a>'.$copyMessage.'</a>';  
                            }

                        }
                    }
                    $whatsappMessageHTML .= '</div></div></div>';
                    $whatsappMessageHTML .= '<span class="chatTime" style="width: 60%; !important"><b>'.$your_number.'</b> - '.$createdTime.'';
                    if($messageReadUnRead == 'Unread'){
                        $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/unread.png"/>';
                    }else{
                        $whatsappMessageHTML .= '<img src="layouts/v7/modules/CTWhatsAppBusiness/image/read.png"/>';
                    }
                    $whatsappMessageHTML .= '</span>&nbsp;</div>';
                    $whatsappMessageHTML .= '<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                    </div>
                    </div>';

                }
            }
        }
        $unreadArray = array('rows' => $rows, 'whatsappMessageHTML' => $whatsappMessageHTML);
        return $unreadArray;
    }

    //function for get module whatsapp record data
    public function getIndividualMessages($recordId){
        global $adb, $current_user;
        $userID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($userID);
        $whatsappQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $setype = VtigerCRMObject::getSEType($recordId);
        if($setype){
            $query = $whatsappQuery.$inNumberQuery.' AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ? ORDER BY vtiger_ctwhatsappbusiness.whatsapp_datetime ASC';
            $queryResult = $adb->pquery($query,  array($recordId));
        }else{
            $recordId = preg_replace('/[^A-Za-z0-9]/', '', $recordId);
            $query = $whatsappQuery.$inNumberQuery.' AND vtiger_ctwhatsappbusiness.whatsapp_withccode LIKE "%'.$recordId.'%"';
            $queryResult = $adb->pquery($query,  array());
        }
        $rows = $adb->num_rows($queryResult);

        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
        $pdfExts = array("pdf");
        $fileExts = array("txt", "php", "zip", "csv", "https");
        $mp3Exts = array("mp3");
        $excelExts = array("xls");
        $wordlExts = array("docx", "doc");

        $whatsappMessages = array();
        for ($i=0; $i < $rows; $i++) { 
            $ctwhatsappId = $adb->query_result($queryResult, $i, 'ctwhatsappid');
            $messageType = $adb->query_result($queryResult, $i, 'message_type');
            $senderName = $adb->query_result($queryResult, $i, 'whatsapp_sendername');
            $messageReadUnRead = $adb->query_result($queryResult, $i, 'whatsapp_unreadread');
            $your_number = $adb->query_result($queryResult, $i, 'your_number');
            $whatsAppFileName = CTWhatsAppBusiness_Record_Model::getFilenameWhatsappMessage($adb->query_result($queryResult, $i, 'message_body'));
            if($ctwhatsappId){
                if($messageReadUnRead == "Unread" && $messageType == 'Recieved'){
                    $recordModel = Vtiger_Record_Model::getInstanceById($ctwhatsappId, 'CTWhatsAppBusiness');
                    $recordModel->set('mode', 'edit');
                    $recordModel->set('id', $ctwhatsappId);
                    $recordModel->set('whatsapp_unreadread', 'Read');
                    $recordModel->save();
                }
            }

            $messageBody = nl2br(preg_replace("#\*([^*]+)\*#", "<b>$1</b>", $adb->query_result($queryResult, $i, 'message_body')));
          
            $urlExt = pathinfo($messageBody, PATHINFO_EXTENSION);
            if (in_array($urlExt, $imgExts)) {
                $messageBody = '<image src="'.$messageBody.'" style="height: 60px !important;cursor: pointer;">';
            }else if(in_array($urlExt, $fileExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/fileicon.png"></a>';
            }else if(in_array($urlExt, $pdfExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/pdficon.png"></a>';
            }else if(in_array($urlExt, $excelExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/excelicon.png"></a>';
            }else if(in_array($urlExt, $wordlExts)){
                $messageBody = '<a href="'.$messageBody.'" target="_black"><img src="layouts/v7/modules/CTWhatsAppBusiness/image/wordicon.jpg"></a>';
            }else if(in_array($urlExt, $mp3Exts)){
                $messageBody = ' <audio controls>
                        <source src="'.$messageBody.'" type="audio/ogg">
                        <source src="'.$messageBody.'" type="audio/mpeg">
                      Your browser does not support the audio element.
                    </audio> ';
            }else{
                $whatsAppFileName = '';
            } 
            $createdTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($queryResult, $i, 'whatsapp_datetime'));
            $whatsappMessages[] = array('messageType' => $messageType, 'messageBody' => $messageBody, 'createdTime' => $createdTime, 'senderName' => $senderName, 'messageReadUnRead' => $messageReadUnRead, 'your_number' => $your_number, 'whatsAppFileName' => $whatsAppFileName);
        }
        return $whatsappMessages;
    }

    //function for check comment module id enable or not
    public function checkCommentModuleEnable($sourceModuleName){
        global $adb;
        $modCommentQuery = $adb->pquery("SELECT * FROM vtiger_tab WHERE name = 'ModComments' AND presence = 0", array());
        $commentRows = $adb->num_rows($modCommentQuery);
        if($commentRows){
            $commentTabid = $adb->query_result($modCommentQuery, 0, 'tabid');
            $sourceModuleTabid = getTabid($sourceModuleName);
            $enableCommentQuery = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE related_tabid = ? AND tabid = ?", array($commentTabid, $sourceModuleTabid));
            $commentModuleEnable = $adb->num_rows($enableCommentQuery);
        }
        return $commentModuleEnable;
    }

    public function getAllConnectedWhatsappNumber($currentUserID){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups");
        $rows = $adb->num_rows($query);
        $whatsappNumbers = array();
        for ($i=0; $i < $rows; $i++) { 
            $multiple_userid = explode(',', $adb->query_result($query, $i, 'multiple_userid'));
            if(in_array($currentUserID, $multiple_userid)){
                $userid = $adb->query_result($query, $i, 'userid');
                $getnumberquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($userid));
                $whatsappno = $adb->query_result($getnumberquery, 0, 'whatsappno');
                $whatsappstatus = $adb->query_result($getnumberquery, 0, 'whatsappstatus');
                if($whatsappno){
                    $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $userid, 'username' => getUserName($userid), 'whatsappstatus' => $whatsappstatus);
                }
            }
        }

        $queryGetGroupId = $adb->pquery("SELECT * FROM vtiger_users2group WHERE userid = ?", array($currentUserID));
        $groupRows = $adb->num_rows($queryGetGroupId);
        for ($k=0; $k < $groupRows; $k++) { 
            $groupid = $adb->query_result($queryGetGroupId, $k, 'groupid');

            $query1 = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups", array());
            $row1 = $adb->num_rows($query1);
            for ($j=0; $j < $row1; $j++) { 
                $multiple_userid = $adb->query_result($query1, $j, 'multiple_userid');
                $allUserId = explode(',', $multiple_userid);
                if(in_array($groupid, $allUserId)){
                    $userid = $adb->query_result($query1, $j, 'userid');
                    $getnumberquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($userid));
                    $whatsappno = $adb->query_result($getnumberquery, 0, 'whatsappno');
                    $whatsappstatus = $adb->query_result($getnumberquery, 0, 'whatsappstatus');
                    if($whatsappno){
                        $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $userid, 'username' => getUserName($userid), 'whatsappstatus' => $whatsappstatus);
                    }
                }
            }
        }

        $mainUserQeury = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups WHERE userid = ?", array($currentUserID));
        $mainUserRows = $adb->num_rows($mainUserQeury);
        if($mainUserRows == 1){
            $getnumbesrquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($currentUserID));
            $whatsappno = $adb->query_result($getnumbesrquery, 0, 'whatsappno');
            $whatsappstatus = $adb->query_result($getnumbesrquery, 0, 'whatsappstatus');
            if($whatsappno){
                $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $currentUserID, 'username' => getUserName($currentUserID), 'whatsappstatus' => $whatsappstatus);
            }
        }else{
            $getnumbesrQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration", array());
            $whatsappno = $adb->query_result($getnumbesrQuery, 0, 'whatsappno');
            $whatsappstatus = $adb->query_result($getnumbesrQuery, 0, 'whatsappstatus');
            if($whatsappno){
                $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $currentUserID, 'username' => getUserName($currentUserID), 'whatsappstatus' => $whatsappstatus);
            }
        }
        $allNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedNumber($whatsappNumbers);
        return $allNumber;
    }

    public function getAllUserWhatsappNumber($currentUserID){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups");
        $rows = $adb->num_rows($query);
        $whatsappNumbers = array();
        for ($i=0; $i < $rows; $i++) { 
            $multiple_userid = explode(',', $adb->query_result($query, $i, 'multiple_userid'));
            if(in_array($currentUserID, $multiple_userid)){
                $userid = $adb->query_result($query, $i, 'userid');
                $getnumberquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ? AND (whatsappstatus = 1 OR whatsappstatus = 2)", array($userid));
                $whatsappno = $adb->query_result($getnumberquery, 0, 'whatsappno');
                $whatsappstatus = $adb->query_result($getnumberquery, 0, 'whatsappstatus');
            if($whatsappno){
                $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $userid, 'whatsappstatus' => $whatsappstatus, 'username' => getUserName($userid));
                }
            }
        }

        $queryGetGroupId = $adb->pquery("SELECT * FROM vtiger_users2group WHERE userid = ?", array($currentUserID));
        $groupRows = $adb->num_rows($queryGetGroupId);
        for ($k=0; $k < $groupRows; $k++) { 
            $groupid = $adb->query_result($queryGetGroupId, $k, 'groupid');

            $query1 = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups", array());
            $row1 = $adb->num_rows($query1);
            for ($j=0; $j < $row1; $j++) { 
                $multiple_userid = $adb->query_result($query1, $j, 'multiple_userid');
                $allUserId = explode(',', $multiple_userid);
                if(in_array($groupid, $allUserId)){
                    $userid = $adb->query_result($query1, $j, 'userid');
                    $getnumberquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ? AND (whatsappstatus = 1 OR whatsappstatus = 2)", array($userid));
                    $whatsappno = $adb->query_result($getnumberquery, 0, 'whatsappno');
                    $whatsappstatus = $adb->query_result($getnumberquery, 0, 'whatsappstatus');
                    if($whatsappno){
                        $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $userid, 'whatsappstatus' => $whatsappstatus, 'username' => getUserName($userid));
                    }
                }
            }
        }

        $mainUserQeury = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups WHERE userid = ?", array($currentUserID));
        $mainUserRows = $adb->num_rows($mainUserQeury);
        if($mainUserRows == 1){
            $getnumbesrquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ? AND (whatsappstatus = 1 OR whatsappstatus = 2)", array($currentUserID));
            $whatsappno = $adb->query_result($getnumbesrquery, 0, 'whatsappno');
            $whatsappstatus = $adb->query_result($getnumbesrquery, 0, 'whatsappstatus');
            if($whatsappno){
                $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $currentUserID, 'whatsappstatus' => $whatsappstatus, 'username' => getUserName($currentUserID));
            }
        }else{
            $getnumbesrQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE (whatsappstatus = 1 OR whatsappstatus = 2) AND customfield5 = ?", array($currentUserID));
            $whatsappno = $adb->query_result($getnumbesrQuery, 0, 'whatsappno');
            $whatsappstatus = $adb->query_result($getnumbesrQuery, 0, 'whatsappstatus');
            if($whatsappno){
                $whatsappNumbers[] = array('whatsappno' => $whatsappno, 'userid' => $currentUserID, 'whatsappstatus' => $whatsappstatus, 'username' => getUserName($currentUserID));
            }
        }
    
        $allNumber = CTWhatsAppBusiness_Record_Model::getAllConnectedNumber($whatsappNumbers);
        return $allNumber;
    }

    public function getAllConnectedNumber($whatsappNumbers){
        $whatsappAllNumbers = array();

        foreach($whatsappNumbers as $Number) {
            $niddle = $Number['whatsappno'];
            if(array_key_exists($niddle, $uniqueHotels)) continue;
                $whatsappAllNumbers[$niddle] = $Number;
        }

        return $whatsappAllNumbers;
    }

    //function for send whatsapp message
    public function sendIndividulMessage($request){
        global $adb, $site_URL, $current_user, $root_directory;
        $moduleName = $request->getModule();
        $whatsappModule = $request->get('whatsappModule');
        $replyMessageId = $request->get('replyMessageId');
        $replymessageText = $request->get('replymessageText');
        $wptemplateid = $request->get('wptemplateid');
        $whatsappNumber = $request->get('whatsappNumber');
        if($whatsappModule == "Groups"){
            $mobileno = $request->get('mobileno');
        }else{
            $mobileno = preg_replace('/[^A-Za-z0-9]/', '', $request->get('mobileno'));
        }
        $msgbody = html_entity_decode($request->get('msgbody'));
        $moduleRecordid = $request->get('moduleRecordid');
        if($moduleRecordid == ''){
            $moduleRecordid = $request->get('module_recordid');
        }
        $base64imagedata = $request->get('base64imagedata');
        $filename = rand().$request->get('filename');
        $filetype = $request->get('filetype');
        $fileURL = '';
        $date_var = date("Y-m-d H:i:s");

        $currenUserID = $current_user->id;
        $getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappNumber));
        $whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
        
        $api_url = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
        $auth_token = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
        $customfield1 = $adb->query_result($getConfigurationDataQuery, 0, 'customfield1');
        $whatsappScanNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappno');
        $whatsappBusinessNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_businessnumber');
        $whatsappStatus = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappstatus');
        $configureUserid = $adb->query_result($getConfigurationDataQuery, 0, 'configureUserid');
        $whatsappAppid = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_appid');
        $whatsappAccountid = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_accountid');

        if($whatsappModule == "Groups"){
            $mobileno = $request->get('mobileno');
        }else{
            $mobileno = CTWhatsAppBusiness_Module_Model::getMobileNumber($mobileno, $customfield1);
        }

        if($mobileno){
            $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        }

        if($base64imagedata != '' || $wptemplateid != ''){
            $whatsappFolderPath = "/modules/CTWhatsAppBusiness/CTWhatsAppBusinessStorage/";
            $year  = date('Y');
            $month = date('F');
            $day   = date('j');
            $week  = '';
            if (!is_dir($root_directory.$whatsappFolderPath)) {
                //create new folder
                mkdir($root_directory.$whatsappFolderPath);
                chmod($root_directory.$whatsappFolderPath, 0777);
            }

            if (!is_dir($root_directory.$whatsappFolderPath . $year)) {
                //create new folder
                mkdir($root_directory.$whatsappFolderPath . $year);
                chmod($root_directory.$whatsappFolderPath . $year, 0777);
            }

            if (!is_dir($root_directory.$whatsappFolderPath . $year . "/" . $month)) {
                //create new folder
                mkdir($root_directory.$whatsappFolderPath . "$year/$month/");
                chmod($root_directory.$whatsappFolderPath . "$year/$month/", 0777);
            }

            if ($day > 0 && $day <= 7)
                $week = 'week1';
            elseif ($day > 7 && $day <= 14)
                $week = 'week2';
            elseif ($day > 14 && $day <= 21)
                $week = 'week3';
            elseif ($day > 21 && $day <= 28)
                $week = 'week4';
            else
                $week = 'week5';  

            if (!is_dir($root_directory.$whatsappFolderPath . $year . "/" . $month . "/" . $week)) {
                //create new folder
                mkdir($root_directory.$whatsappFolderPath . "$year/$month/$week/");
                chmod($root_directory.$whatsappFolderPath . "$year/$month/$week/", 0777);
            }
        }

        if($wptemplateid){
            $getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinesstemplates 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
                WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid = ?", array($wptemplateid));
            $isTemplates = $adb->num_rows($getWhatsappTemplateQuery);

            if($isTemplates){
                $wptemplateText = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_text');
                $imageId = $adb->query_result($getWhatsappTemplateQuery, 0, 'attachmentsid');
                $wptemplate_status = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_status');
                $imagePath = $adb->query_result($getWhatsappTemplateQuery, 0, 'path');
                $imageName = $adb->query_result($getWhatsappTemplateQuery, 0, 'name');
                $wptemplate_title = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_title');
                $filetype = $adb->query_result($getWhatsappTemplateQuery, 0, 'type');
                $wptemplate_language = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_language');
                $attachmentPath = $site_URL.$imagePath.$imageId.'_'.$imageName;
                
                    if($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/png'){
                        $sendMessagetype = "image";
                    }else{
                        $sendMessagetype = "document";
                    }
                    $sendmessageurl = $api_url.$whatsappBusinessNo.'/messages';

                    if($wptemplate_status == 1){
                        $postfields = [
                           "messaging_product" => "whatsapp", 
                           "recipient_type" => "individual", 
                           "to" => $mobileno, 
                           "type" => "template", 
                           "template" => [
                                 "name" => $wptemplate_title, 
                                 "language" => [
                                    "code" => $wptemplate_language 
                                 ], 
                                 "components" => [
                                       [
                                          "type" => "header", 
                                          "parameters" => [
                                             [
                                                "type" => "image", 
                                                "image" => [
                                                   "link" => $attachmentPath
                                                ] 
                                             ] 
                                          ] 
                                       ] 
                                    ] 
                              ] 
                        ];
                    }else{
                        $postfields = array('messaging_product' => "whatsapp",
                                        'recipient_type' => "individual",
                                        'to' => $mobileno,
                                        'type' => $sendMessagetype,
                                            $sendMessagetype => array('link' => $attachmentPath ,'caption' => htmlspecialchars_decode($msgbody, ENT_QUOTES)),
                                        );
                    }
            }else{
                $getWhatsappTemplateData = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinesstemplates 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid 
                    WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusinesstemplates.ctwhatsappbusinesstemplatesid = ?", array($wptemplateid));
                $templatesRows = $adb->num_rows($getWhatsappTemplateData);
                $wptemplateText = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_text');
                $wptemplate_status = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_status');
                $wptemplate_title = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_title');
                $wptemplate_language = $adb->query_result($getWhatsappTemplateData, 0, 'wptemplate_language');
                $sendmessageurl = $api_url.$whatsappBusinessNo.'/messages';

                if($wptemplate_status == 1){
                    //$sendmessageUrl = $api_url.$whatsappBusinessNo.'/messages';
                    $language = array("code" => $wptemplate_language);
                    $postfields = array('messaging_product' => "whatsapp",
                                        'to' => $mobileno,
                                        'type' => "template",
                                        'template' => array('name' => $wptemplate_title, 
                                                            'language' => $language),
                                        );
                }else{
                    $postfields = array('messaging_product' => "whatsapp",
                                        'recipient_type' => "individual",
                                        'to' => $mobileno,
                                        'type' => "text",
                                        'text' => array('preview_url' => false, 
                                                    'body' => htmlspecialchars_decode($msgbody, ENT_QUOTES)),
                                        );
                }
            }
        }else{
            if($base64imagedata != ''){
                $documentpath = 'storage';
                $filepath = 'storage/';
                $year  = date('Y');
                $month = date('F');
                $day   = date('j');
                $week  = '';

                if (!is_dir($root_directory.$filepath . $year)) {
                    //create new folder
                    mkdir($root_directory.$filepath . $year);
                    chmod($root_directory.$filepath . $year, 0777);
                }

                if (!is_dir($root_directory.$filepath . $year . "/" . $month)) {
                    //create new folder
                    mkdir($root_directory.$filepath . "$year/$month");
                    chmod($root_directory.$filepath . "$year/$month", 0777);
                }

                if ($day > 0 && $day <= 7)
                    $week = 'week1';
                elseif ($day > 7 && $day <= 14)
                    $week = 'week2';
                elseif ($day > 14 && $day <= 21)
                    $week = 'week3';
                elseif ($day > 21 && $day <= 28)
                    $week = 'week4';
                else
                    $week = 'week5';

                if (!is_dir($root_directory.$filepath . $year . "/" . $month . "/" . $week)) {
                    //create new folder
                    mkdir($root_directory.$filepath . "$year/$month/$week");
                    chmod($root_directory.$filepath . "$year/$month/$week", 0777);
                }

                $target_file = $root_directory.$filepath.$year.'/'.$month.'/'.$week.'/';

                list($type, $base64imagedata) = explode(';', $base64imagedata);
                list(, $base64imagedata)      = explode(',', $base64imagedata);
                $base64imagedata = base64_decode($base64imagedata);

                $filemove = file_put_contents($target_file.$filename,$base64imagedata);
                    if($filemove){
                    $Document = Vtiger_Record_Model::getCleanInstance('Documents');
                    $Document->set('mode', '');
                    $Document->set('assigned_user_id',$current_user->id);
                    $Document->set('folderid', 1);
                    $Document->set('filelocationtype', 'I');
                    $Document->set('filestatus',1);
                    $Document->set('filename',$filename);
                    $Document->save();
                    $documentid = $Document->getId();
                    $current_id = $adb->getUniqueID("vtiger_crmentity");

                    $sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
                    $params1 = array($current_id, $current_user->id, 1, "Documents Attachment", '', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
                    $adb->pquery($sql1, $params1);
                    rename($target_file.$filename,$target_file.$current_id.'_'.$filename);
                    chmod($target_file, 0777);

                    $sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
                    $params2 = array($current_id, $filename, '', '', $filepath.$year.'/'.$month.'/'.$week.'/');
                    $result = $adb->pquery($sql2, $params2);

                    $sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
                    $adb->pquery($sql3, array($documentid, $current_id));

                    $sql4 = 'insert into vtiger_senotesrel values(?,?)';
                    $adb->pquery($sql4, array($moduleRecordid, $documentid));
                }
                $fileURL = $site_URL.$filepath.$year.'/'.$month.'/'.$week.'/'.$current_id.'_'.$filename;
                $oldfile = $root_directory.$filepath.$year.'/'.$month.'/'.$week.'/'.$current_id.'_'.$filename;

                $filename = str_replace(' ', '', $filename);
                $newfile = $root_directory.$whatsappFolderPath . "$year/$month/$week/".$filename;

                copy($oldfile, $newfile);
                $newFilename = urlencode($filename);
                $newFilename = str_replace('+','%20',$newFilename);
                $newFilename = str_replace('_','%5F',$newFilename);
                $newFilename = str_replace('.','%2E',$newFilename);
                $newFilename = str_replace('-','%2D',$newFilename); 
                $newfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$newFilename;
                $newBodyfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$filename;

                if($filetype == 'image/jpeg' || $filetype == 'image/jpg' || $filetype == 'image/png'){
                    $sendMessagetype = "image";
                }else{
                    $sendMessagetype = "document";
                }

                $sendmessageurl = $api_url.$whatsappBusinessNo.'/messages';
                $postfields = array('messaging_product' => "whatsapp",
                                    'recipient_type' => "individual",
                                    'to' => $mobileno,
                                    'type' => $sendMessagetype,
                                    $sendMessagetype => array('link' => $newfileURL),
                                    );

            }else{
                $sendmessageurl = $api_url.$whatsappBusinessNo.'/messages';
                $postfields = array('messaging_product' => "whatsapp",
                                    'recipient_type' => "individual",
                                    'to' => $mobileno,
                                    'type' => "text",
                                    'text' => array('preview_url' => false, 
                                                    'body' => htmlspecialchars_decode($msgbody, ENT_QUOTES)),
                                    );
                if($replyMessageId){
                    $postfields['context'] = array('message_id' => $replyMessageId);
                }
            }
        }
        
        if($whatsappStatus == 1 || $whatsappStatus == 2){
            //displayname changes
            if($moduleRecordid){
                $setype = VtigerCRMObject::getSEType($moduleRecordid);
                $recordModel = Vtiger_Record_Model::getInstanceById($moduleRecordid, $setype);
                $displayname = $recordModel->get('label');
            }else{
                $displayname = $mobileno;
            }
            //displayname changes

            $getLicenseDetail = CTWhatsAppBusiness_Record_Model::getWhatsAppLicenseDetail();
            $licenseKey = $getLicenseDetail['licenseKey'];
            $getWhatsappAccount = CTWhatsAppBusiness_Record_Model::getWhatsappAccountDetail($licenseKey);
            $oneDayMessages = CTWhatsAppBusiness_Record_Model::getOneDaysMessages();

            $currentusername = $current_user->first_name.' '.$current_user->last_name;

            $whatsappLogQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppLogData($mobileno, $moduleRecordid, $whatsappScanNo);
            $whatsapplogRows = $whatsappLogQuery['rows'];
            if($whatsapplogRows == 0){
                $recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppBusinessLog');
                $recordModel->set('whatsapplog_sendername', $currentusername);
                $recordModel->set('whatsapplog_withccode', $mobileno);
                $recordModel->set('messagelog_type', 'Send');
                if($fileURL){
                    $recordModel->set('messagelog_body', $newBodyfileURL);
                }else{
                    $recordModel->set('messagelog_body', $msgbody);
                }
                $recordModel->set('whatsapplog_displayname', $displayname);
                $recordModel->set('whatsapplog_contactid', $moduleRecordid);
                $recordModel->set('whatsapplog_unreadread', 'Unread');
                $recordModel->set('whatsapplog_important', $getnumberImportant);
                $recordModel->set('whatsapplog_your_number', $whatsappScanNo);
                $recordModel->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
                $recordModel->set('assigned_user_id', $currenUserID);
                $recordModel->set('whatsapplog_quotemessage', $replymessageText);
                if($msgbody != ''){
                    if($getWhatsappAccount->type == 'free' && $oneDayMessages < '100'){
                        $recordModel->save();
                    }else if($getWhatsappAccount->type == 'premium'){
                        $recordModel->save();
                    }
                }
                $whatsappbusinesslogid = $recordModel->getId();
            }else{
                $whatsappbusinesslogid = $whatsappLogQuery['whatsappbusinesslogid'];
                $recordModel = Vtiger_Record_Model::getInstanceById($whatsappbusinesslogid, 'WhatsAppBusinessLog');
                $recordModel->set('mode', 'edit');
                $recordModel->set('id', $whatsappbusinesslogid);
                $recordModel->set('whatsapplog_datetime', $adb->formatDate($date_var, true));
                if($fileURL){
                    $recordModel->set('messagelog_body', $newBodyfileURL);
                }else{
                    $recordModel->set('messagelog_body', $msgbody);
                }
                $recordModel->save();
                $whatsappbusinesslogid = $recordModel->getId();
            }

            $recordModel = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
            $recordModel->set('whatsapp_sendername', $currentusername);
            $recordModel->set('whatsapp_withccode', $mobileno);
            $recordModel->set('message_type', 'Send');

            if($fileURL){
                $recordModel->set('message_body', $newBodyfileURL);
            }else{
                $recordModel->set('message_body', $msgbody);
            }
            $recordModel->set('whatsapp_displayname', $displayname);
            $recordModel->set('whatsapp_contactid', $moduleRecordid);
            $recordModel->set('whatsapp_unreadread', 'Unread');
            $recordModel->set('whatsapp_fromno', $whatsappScanNo);
            $recordModel->set('whatsapp_important', $getnumberImportant);
            $recordModel->set('your_number', $whatsappScanNo);
            $recordModel->set('whatsapp_datetime', $adb->formatDate($date_var, true));
            $recordModel->set('assigned_user_id', $currenUserID);
            $recordModel->set('whatsapp_quotemessage', $replymessageText);
            $requestParam = $url.' ';
            $requestParam .= json_encode($postfields);
            $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
            $whatsapplog = $configurationData['whatsapplog'];
            if($whatsapplog == 1){
                $recordModel->set('whatsapp_request', $requestParam);
            }
            if($msgbody != ''){
                if($getWhatsappAccount->type == 'free' && $oneDayMessages < '100'){
                    $recordModel->save();
                }else if($getWhatsappAccount->type == 'premium'){
                    $recordModel->save();
                }
            }
            $whatsAppModuleId = $recordModel->getId();

            $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($sendmessageurl, $postfields, $auth_token);
            $updateWhatsAppLogMessageId = CTWhatsAppBusiness_Record_Model::updateWhatsAppMessageId('WhatsAppBusinessLog', $whatsappbusinesslogid, $val, $whatsapplog, $tonumbersValue, $whatsappModule);

            $updateWhatsAppMessageId = CTWhatsAppBusiness_Record_Model::updateWhatsAppMessageId('CTWhatsAppBusiness', $whatsAppModuleId, $val, $whatsapplog, $mobileno, $whatsappModule);

            $currenDatTime = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($date_var);
            $resultSendMessage = array('currenDatTime' => $currenDatTime, 'senderName' => $currentusername, 'whatsappid' => $recordModel->getId(), 'numberactive' => $numberactive);
            return $resultSendMessage;
        }
    }

    public function updateWhatsAppMessageId($modulename, $whatsAppModuleId, $val, $whatsapplog, $mobileno, $whatsappModule){
        if($whatsAppModuleId){
            if($modulename == 'CTWhatsAppBusiness'){
                $whatsappRecordModel = Vtiger_Record_Model::getInstanceById($whatsAppModuleId, $modulename);
                $whatsappRecordModel->set('mode', 'edit');
                $whatsappRecordModel->set('id', $whatsAppModuleId);
                if($whatsappModule == "Groups"){
                    $whatsappRecordModel->set('msgid', $mobileno);
                }else{
                    $whatsappRecordModel->set('msgid', $val['messages'][0]['id']);
                }
                if($whatsapplog == 1){
                    $whatsappRecordModel->set('whatsapp_response', json_encode($val));
                }
                $whatsappRecordModel->save();   
            }else{
                $whatsappRecordModel = Vtiger_Record_Model::getInstanceById($whatsAppModuleId, $modulename);
                $whatsappRecordModel->set('mode', 'edit');
                $whatsappRecordModel->set('id', $whatsAppModuleId);
                if($whatsappModule == "Groups"){
                    $whatsappRecordModel->set('whatsapplog_msgid', $mobileno);
                }else{
                    $whatsappRecordModel->set('whatsapplog_msgid', $val['messages'][0]['id']);
                }
                if($whatsapplog == 1){
                    $whatsappRecordModel->set('whatsapplog_response', json_encode($val));
                }
                $whatsappRecordModel->save();   
            }
        }
    }

    public function getWhatsAppLogData($mobileno, $moduleRecordid, $whatsappScanNo){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_whatsappbusinesslog 
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_whatsappbusinesslog.whatsappbusinesslogid 
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_whatsappbusinesslog.whatsapplog_withccode = ? AND vtiger_whatsappbusinesslog.whatsapplog_your_number = ?", array($mobileno, $whatsappScanNo));
        $rows = $adb->num_rows($query);
        $whatsappbusinesslogid = $adb->query_result($query, 0, 'whatsappbusinesslogid');
        $numberactive = $adb->query_result($query, 0, 'whatsapp_numberactive');
        $smownerid = $adb->query_result($query, 0, 'smownerid');
        $result = array("rows" => $rows, 'whatsappbusinesslogid' => $whatsappbusinesslogid, 'numberactive' => $numberactive, 'smownerid' => $smownerid);
        return $result;
    }

    public function getWhatsAppUserData($mobileno, $moduleRecordid, $whatsappScanNo){
        global $adb;
        $query = $adb->pquery("SELECT MAX(vtiger_ctwhatsappbusiness.ctwhatsappid), vtiger_ctwhatsappbusiness.*, vtiger_crmentity.* FROM vtiger_ctwhatsappbusiness 
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.whatsapp_withccode = ? AND vtiger_ctwhatsappbusiness.your_number = ? AND vtiger_ctwhatsappbusiness.message_type = 'Send'", array($mobileno, $whatsappScanNo));
        $rows = $adb->num_rows($query);
        $ctwhatsappid = $adb->query_result($query, 0, 'ctwhatsappid');
        $numberactive = $adb->query_result($query, 0, 'whatsapp_numberactive');
        $smownerid = $adb->query_result($query, 0, 'smownerid');
        $result = array("rows" => $rows, 'ctwhatsappid' => $ctwhatsappid, 'numberactive' => $numberactive, 'smownerid' => $smownerid);
        return $result;
    }

    //function for get module whatsapp id enable or not
    public function getallowToWhatsAppModule($request){
        global $adb, $current_user;
        $moduleName = $request->getModule();
        $sourceModuleName = $request->get('source_module');
        $recordid = $request->get('recordid');

        $allowModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($sourceModuleName);
        $active = $allowModuleData['moduleIconActive'];
        $phoneField = $allowModuleData['phoneField'];

        $whatsappQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();
        $getUreadMsgQuery = $adb->pquery($whatsappQuery." AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ? AND vtiger_ctwhatsappbusiness.message_type = 'Recieved' AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Unread'", array($recordid));
        $unreadmsg = $adb->num_rows($getUreadMsgQuery);
        
        $currenUserID = $current_user->id;
            $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        
        $iconActive = $configurationData['iconactive'];
        $api_url = $configurationData['api_url'];
        $auth_token = $configurationData['authtoken'];
        
        $currentDate = date('Y-m-d');
        $getexpiredate = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_license_setting");
        $expirydate = $adb->query_result($getexpiredate, 0, 'expirydate');
        $licenseKey = $adb->query_result($getexpiredate, 0, 'license_key');
        $date = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');
        
        if(strtotime($currentDate) >= strtotime($date)){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://www.crmtiger.com/whatsapp/checkl.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
                CURLOPT_POSTFIELDS => array('license_key' => $licenseKey),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $resultResponse = json_decode($response,true);
            if(strtotime($resultResponse['expirydate']) >= strtotime($date)){
                $newdate = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::encrypt_decrypt($resultResponse['expirydate'], $action='e');
                $adb->pquery("UPDATE vtiger_ctwhatsappbusiness_license_setting set expirydate = '$newdate'");
            }
        }
    
        $recordModel = Vtiger_Record_Model::getInstanceById($recordid, $source_module);
        $fieldValue = preg_replace('/[^A-Za-z0-9]/', '', $recordModel->get($phoneField));

        $result =  array('iconActive' => $iconActive, 'date' => $date, 'currentDate' => $currentDate, 'active' => $active, 'unreadmsg' => $unreadmsg, 'fieldValue' => $fieldValue);
        return $result;
    }

    //function for get license details
    public function getWhatsAppLicenseDetail(){
        global $adb;
        $getexpiredate = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_license_setting");
        $rows = $adb->num_rows($getexpiredate);
        $expiryDate = $adb->query_result($getexpiredate, 0, 'expirydate');
        $licenseKey = $adb->query_result($getexpiredate, 0, 'license_key');
        $domain = $adb->query_result($getexpiredate, 0, 'domain');
        $licensefield1 = $adb->query_result($getexpiredate, 0, 'licensefield1');
        $licensefield2 = $adb->query_result($getexpiredate, 0, 'licensefield2');
        return $licenseDetail = array('rows' => $rows, 'expiryDate' => $expiryDate, 'licenseKey' => $licenseKey, 'domain' => $domain, 'licensefield1' => $licensefield1, 'licensefield2' => $licensefield2);
    }

    //function for update authentication code when scan qr code
    public function updateAuthCode($request){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $authTokenKey = $request->get('authtokenkey');
        $whatsappuseid = $request->get('whatsappuseid');
        $configurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($whatsappuseid));
        $row = $adb->num_rows($configurationQuery);
        if($row == 0){
            if($authTokenKey){
                $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET auth_token=?",array($authTokenKey));
            }
        }else{
            if($authTokenKey){
              $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET auth_token=? WHERE customfield5 = ?",array($authTokenKey, $whatsappuseid));  
              $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET auth_token=? WHERE customfield5 = ?",array($authTokenKey, $whatsappuseid));   
            }
        }
    }

    //function for get whatsapp status
    public function getWhatsAppStatus($request){
        global $adb, $current_user;
        $currenUserID = $current_user->id;
        $whatsappbot = $request->get('whatsappbot');
        if($whatsappbot == 'yes'){
            $getWhatsappStatusQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield5 = 'whatsappBot'", array());
        }else{
            $getWhatsappStatusQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield5 = ?", array($currenUserID));
            $getWhatsappStatusQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($currenUserID));
        }
        $whatsappStatus = $adb->query_result($getWhatsappStatusQuery, 0, 'whatsappstatus');
        $whatsappNo = $adb->query_result($getWhatsappStatusQuery, 0, 'whatsappno');
        $whatsappStatusArray = array('whatsappStatus' => $whatsappStatus, 'whatsappNo' => $whatsappNo);
        return $whatsappStatusArray;
    }

  //function for get mass message details
    public function getMassMessageData(){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessmassbatch");
        $batch = $adb->query_result($query, 0, 'batch');
        $timeinterval = $adb->query_result($query, 0, 'timeinterval');
        $crondatetime = $adb->query_result($query, 0, 'crondatetime');
        $getMassMessageDetail = array('batch' => $batch, 'timeinterval' => $timeinterval, 'crondatetime' => $crondatetime);
        return $getMassMessageDetail;
    } 

    //function for send mass messages
    public function sendMassMessages($request){
        global $adb, $site_URL, $current_user;
        $moduleName = $request->getModule();
        $source_module = $request->get('source_module');
        $templatesid = $request->get('templatesid');
        $sendNowLater = $request->get('sendNowLater');
        $whatsappUserID = $request->get('whatsappUserID');
        $laterDateTime = explode(' ', $request->get('laterDateTime'));
        $cvid = $request->get('cvid');
        $whatsappModuleFieldsData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($source_module);
        $phoneField = $whatsappModuleFieldsData['phoneField'];

        $selected_ids = $request->get('selected_ids');
        if($selected_ids == 'all'){
            $customViewModel = CustomView_Record_Model::getInstanceById($cvid);
            $customViewModel->set('search_params', $request->get('searchvalue'));
            $selected_ids = $customViewModel->getRecordIds($excludedIds,$source_module);
        }else{
            $selected_ids = $request->get('selected_ids');
        }
        if($templatesid){
            $templatesID = $templatesid; 
        }else{
            $templatesID = '0'; 
        }
        $msg_body = $request->get('msgbody');
        $date_var = date("Y-m-d H:i:s");
        $currentDate = date("Y-m-d H:i:s");

        $currenUserID = $current_user->id;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups", array());
        $row1 = $adb->num_rows($query);
        for ($i=0; $i < $row1; $i++) { 
            $multiple_userid = $adb->query_result($query, $i, 'multiple_userid');
            $allUserId = explode(',', $multiple_userid);
          
            if(in_array($currenUserID, $allUserId)){
                $scanUserId = $adb->query_result($query, $i, 'userid');
                break;
            }
        }

        if($scanUserId == ''){
            $connectUseridquery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups WHERE userid = ?", array($currenUserID));
            $scanUserId = $adb->query_result($connectUseridquery, 0, 'userid');
        }
        if($scanUserId == ''){
            $getConfigurationDatasQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration", array());
            $whatsaAppRow = $adb->num_rows($getConfigurationDatasQuery);
            $scanUserId = $adb->query_result($getConfigurationDatasQuery, 0, 'customfield5');
        }

        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $api_url = $configurationData['api_url'];
        $auth_token = $configurationData['authtoken'];
        $customfield1 = $configurationData['customfield1'];

        $getMassBatchConfiguration = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::getMassBatchConfigurationData();
        $selectBatch = $getMassBatchConfiguration['batch'];
        
        $getScheduleSendMsgidQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessmassmessage ORDER BY massmessageid DESC LIMIT 0,1");
        $schedulesendmsgid = $adb->query_result($getScheduleSendMsgidQuery, 0, 'massmessageid') + 1;

        if($sendNowLater == 'later'){
            $cronDate_Time = $laterDateTime[0].' '.Vtiger_Time_UIType::getTimeValueWithSeconds($laterDateTime[1].' '.$laterDateTime[2]);

            $cronDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($cronDate_Time);
            $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessmassmessage(massmessageid, templatesid, whatsappmessage, sendmessagedate, massmsgdatetime, connectuserid) VALUES (?,?,?,?,?,?)", array($schedulesendmsgid, $templatesID, $msg_body, $currentDate, $cronDateTime, $whatsappUserID));
        }else{
            $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessmassmessage(massmessageid, templatesid, whatsappmessage, sendmessagedate, massmsgdatetime, connectuserid) VALUES (?,?,?,?,?,?)", array($schedulesendmsgid, $templatesID, $msg_body, $currentDate, '', $whatsappUserID));
        }

        foreach ($selected_ids as $key => $value) {
            $scheduleSendmsgQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessschedulesendmsg(schedulesendmsgid, recordid, body, status, send_msg, datesendmessage) VALUES (?,?,?,?,?,?)", array($schedulesendmsgid, $value, $msg_body, 0, 0, ''));
        }
    }

    //function for get whatsapp template data
    public function getWhatsAppTemplateData($request){
        global $adb, $root_directory;
        $moduleName = $request->getModule();
        $templatesid = $request->get('templatesid');
        if($templatesid){
            $recordModel = Vtiger_Record_Model::getInstanceById($templatesid, 'WhatsAppBusinessTemplates');

            $getAttachmentQuery = $adb->pquery("SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
            INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
            WHERE vtiger_seattachmentsrel.crmid = ? AND vtiger_crmentity.deleted = 0", array($templatesid));

            $imageId = $adb->query_result($getAttachmentQuery, 0, 'attachmentsid');
            $imagePath = $adb->query_result($getAttachmentQuery, 0, 'path');
            $fileName = $adb->query_result($getAttachmentQuery, 0, 'name');
            $type = $adb->query_result($getAttachmentQuery, 0, 'type');
            if (strpos($type, 'image') !== false) {
                $fileType = 1;
            }else{
                $fileType = 0;
            }
            $attachmentPath = $imagePath.$imageId.'_'.$fileName;

            $templateData = array('templatesid' => $templatesid,'message' => decode_html($recordModel->get('wptemplate_text')), 'image' => $attachmentPath, 'fileType' => $fileType, 'fileName' => $fileName);
        }
        return $templateData;
    }

    //Function for Create new whatsapp user configuration
    public function createWhatsappUser($currenUserID){
        global $adb, $site_URL;
        if($currenUserID == 'yes'){
            $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = 'whatsappBot'", array());
        }else{
            $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($currenUserID));
        }
        $numrows = $adb->num_rows($query);

        if($numrows == 0){
            $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
            $adminApiUrl = $configurationData['api_url'];
            $contryCode = $configurationData['customfield1'];
            $iconActive = $configurationData['iconactive'];
          
            if($currenUserID == 'yes'){
                $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessusers SET api_url='$adminApiUrl', customfield5='whatsappBot', customfield1='$contryCode', iconactive='$iconActive'", array());
            }else{
                $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessusers SET api_url='$adminApiUrl', customfield5='$currenUserID', customfield1='$contryCode', iconactive='$iconActive'", array());
            }
        }

        $configurationUserData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $apiUrl = $configurationUserData['api_url'];
        $whatsappStatus = $configurationUserData['whatsappstatus'];
        $whatsappNo = $configurationData['whatsappno'];
        if($whatsappNo == ''){
            $whatsappNo = $adb->query_result($query, 0, 'whatsappno');
        }

        $getLicenseDetail = CTWhatsAppBusiness_DashBoard_View::getLicenseDetail();
        $licenseKey = $getLicenseDetail['licenseKey'];

        if($currenUserID){
            $whatsappBotQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_user_groups WHERE userid = ?", array($currenUserID));
            $whatsappbot = $adb->query_result($whatsappBotQuery, 0, 'whatsappbot');
        }

        if($whatsappStatus == 0){
            $qrcodeurl = $apiUrl."/init";
            $fields = array(
                'domain' => $site_URL,
                "licenceKey" => $licenseKey,
                "statusurl" => $site_URL.'/modules/CTWhatsAppBusiness/WhatsappStatus.php?userid='.$currenUserID,
            );
            if($whatsappbot == 1){
                $fields["url"] = $site_URL.'/modules/CTWhatsAppBusiness//CTWhatAppReceiverBot.php?userid='.$currenUserID;
            }else{
                $fields["url"] = $site_URL.'/modules/CTWhatsAppBusiness/CTWhatAppReceiver.php?userid='.$currenUserID;
            } 

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
                CURLOPT_POSTFIELDS => json_encode($fields),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $result = curl_exec($curl);
            $response = json_decode($result);
            curl_close($curl);
            $qrcodeurl = $response->qr;
            $authTokenKey = $response->Authorization;
            $scanMessage = $response->message;
            
            if($authTokenKey){
                if($currenUserID == 'yes'){
                    $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET auth_token=? WHERE customfield5 = 'whatsappBot'",array($authTokenKey));
                }else{

                    $updatequery=$adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET auth_token=? WHERE customfield5 = ?",array($authTokenKey, $currenUserID));
                }
            }
        }
        $qrCodeDetail = array('qrcodeurl' => $qrcodeurl, 'authTokenKey' => $authTokenKey, 'whatsappNo' => $whatsappNo, 'scanMessage' => $scanMessage, 'apiUrl' => $apiUrl);
        return $qrCodeDetail;
    }

    //Function for get All Whatsapp Groups
    public function getWhatsappGroup($groupWhatsappNumber){
    
        global $current_user, $adb;
        $currenUserID = $current_user->id;

        $getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($groupWhatsappNumber));
        $whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
        if($whatsaAppRows == 0){
            $getConfigurationDatasQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($groupWhatsappNumber));
            $whatsaAppRow = $adb->num_rows($getConfigurationDatasQuery);
            if($whatsaAppRow == 1){
                $apiUrl = $adb->query_result($getConfigurationDatasQuery, 0, 'api_url');
                $authToken = $adb->query_result($getConfigurationDatasQuery, 0, 'auth_token');
            }
        }else{
            $apiUrl = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
            $authToken = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
        }

        $url = $apiUrl.'/chatlist';
    
        $postfields = array();

        if($groupWhatsappNumber){
            $getAllGroups = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $authToken);
        }

        $groupsData = array();
        foreach ($getAllGroups as $key => $value) {
            $jid = explode('@', $value['id']);
            if($jid[1] == 'g.us'){
                $recordId = '';
                $label = $value['name'];
                $isReadOnly = $value['isReadOnly'];
                $groupMember = count($value['metadata']['participants']);
                $labelExplode = explode(' ', $label);
                $profileImage = 'layouts/v7/modules/CTWhatsAppBusiness/image/groups.png';
                $groupid = $value['id'];
                $imagetag = 1;
                if($label){
                    $groupsData[$groupid] = array('recordId' => $recordId, 'label' => $label, 'profileImage' => $profileImage, 'imagetag' => $imagetag, 'groupid' => $groupid, 'isReadOnly' => $isReadOnly, 'groupMember' => $groupMember);
                }
            }
        }
        return $groupsData;
    }

    public function updateWhatsappRecords($request){
        global $adb;
        $phone = $request->get('phone');
        $recordId = $request->get('recordId');
        $task = $request->get('task');
        $whatsappid = $request->get('whatsappid');
        if($task == 'yes'){
            $updateQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusiness SET whatsapp_chatid = ? WHERE ctwhatsappid = ?", array($recordId, $whatsappid));
        }else{
            $updateQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusiness SET whatsapp_contactid = ? WHERE whatsapp_withccode = ?", array($recordId, $phone));

            $updateLogQuery = $adb->pquery("UPDATE vtiger_whatsappbusinesslog SET whatsapplog_contactid = ? WHERE whatsapplog_withccode = ?", array($recordId, $phone));
        }
    }

    public function getWhatsappTemplates(){
        global $adb;
        $wpTemplateViewQuery = $adb->pquery("SELECT * FROM vtiger_customview WHERE entitytype = 'WhatsAppBusinessTemplates' AND viewname = 'All'");
        $viewid = $adb->query_result($wpTemplateViewQuery, 0, 'cvid');
        $listViewModel = Vtiger_ListView_Model::getInstance('WhatsAppBusinessTemplates', $viewid);
        $queryGenerator = $listViewModel->get('query_generator');
        $listQuery = $queryGenerator->getQuery();
        // $queryData = explode(' ', $listQuery, 2);
        // $query = $queryData[0];

        $getWhatsappTemplateQuery = $adb->pquery($listQuery, array());

        $whatsappTemplateRows = $adb->num_rows($getWhatsappTemplateQuery);
        $templatesArray = array();
        for ($j=0; $j < $whatsappTemplateRows; $j++) { 
            $templatesID = $adb->query_result($getWhatsappTemplateQuery, $j, 'ctwhatsappbusinesstemplatesid');
            $templateTitle = $adb->query_result($getWhatsappTemplateQuery, $j, 'wptemplate_title');
            $templatesArray[$templatesID] = $templateTitle;
        }
        return $templatesArray;
    }

    public function getRelatedToId($mobileno){
        global $adb;
        $whatsappModuleQuery = $adb->pquery("SELECT * FROM vtiger_ctwharsappallow_whatsappmodule WHERE active = 1");
        $rows = $adb->num_rows($whatsappModuleQuery);
    
        $whatsaappModule = array();
        for ($i=0; $i < $rows; $i++) { 
            $modulename = $adb->query_result($whatsappModuleQuery, $i, 'module');     
            $moduleIsEnable = CTWhatsAppBusiness_Record_Model::getmoduleIsEnable($modulename);

            if($modulename == 'Leads'){
                $leadQuery = " AND vtiger_leaddetails.converted = 0";
            }else{
              $leadQuery = "";
            }

            if($moduleIsEnable == 0){
                $whatsaappModuleData = CTWhatsAppBusiness_Record_Model::getWhatsappAllowModuleFields($modulename);
                $phoneField = $whatsaappModuleData['phoneField'];
          
                $moduleModel = CRMEntity::getInstance($modulename);
                $moduleInstance = Vtiger_Module::getInstance($modulename);
                $baseTable = $moduleInstance->basetable;
                $baseTableid = $moduleInstance->basetableid;

                $mainTable = 0;
                $query = "SELECT * FROM ".$baseTable;
                foreach ($moduleModel->tab_name_index as $key => $value) {
                    $mainTable = $mainTable + 1;
                    if($mainTable != 2){
                        if($key != 'vtiger_seproductsrel' && $key != 'vtiger_producttaxrel'){
                          $query .= " INNER JOIN ".$key." ON ".$key.".".$value." = ".$baseTable.".".$baseTableid;
                      }
                    }
                }

                $query .= " WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(".$phoneField.", ')', ''), '(', ''),'-',''),' ',''),'.','') LIKE '%".$mobileno."' AND vtiger_crmentity.deleted=0";
                $query .= $leadQuery;
           
                $queryResult = $adb->pquery($query);
                $row = $adb->num_rows($queryResult);
                if($row){
                    $relatedTo = $adb->query_result($queryResult, 0, $baseTableid);
                    $smownerid = $adb->query_result($queryResult, 0, 'smownerid');
                    $displayname = $adb->query_result($queryResult, 0, 'label');
                }
            }
        }
        $resultData = array('relatedTo' => $relatedTo, 'smownerid' => $smownerid, 'displayname' => $displayname);
        return $resultData;
    }

    public function getWhatsAppStoragePath(){
        global $root_directory;
        $year  = date('Y');
        $month = date('F');
        $day   = date('j');
        $week  = '';
        
        $whatsappfolderpath = "modules/CTWhatsAppBusiness/CTWhatsAppBusinessStorage/";
        
        if (!is_dir($root_directory.$whatsappfolderpath)) {
          //create new folder
          mkdir($root_directory.$whatsappfolderpath);
          chmod($root_directory.$whatsappfolderpath, 0777);
        }
        
        if (!is_dir($root_directory.$whatsappfolderpath . $year)) {
          //create new folder
          mkdir($root_directory.$whatsappfolderpath . $year);
          chmod($root_directory.$whatsappfolderpath . $year, 0777);
        }

        if (!is_dir($root_directory.$whatsappfolderpath . $year . "/" . $month)) {
          //create new folder
          mkdir($root_directory.$whatsappfolderpath . "$year/$month/");
          chmod($root_directory.$whatsappfolderpath . "$year/$month/", 0777);
        }
    
        if ($day > 0 && $day <= 7)
          $week = 'week1';
        elseif ($day > 7 && $day <= 14)
          $week = 'week2';
        elseif ($day > 14 && $day <= 21)
          $week = 'week3';
        elseif ($day > 21 && $day <= 28)
          $week = 'week4';
        else
          $week = 'week5';  
      
        if (!is_dir($root_directory.$whatsappfolderpath . $year . "/" . $month . "/" . $week)) {
            //create new folder
            mkdir($root_directory.$whatsappfolderpath . "$year/$month/$week/");
            chmod($root_directory.$whatsappfolderpath . "$year/$month/$week/", 0777);
        }
        $target_file = $root_directory.$whatsappfolderpath . "$year/$month/$week/";
        return $target_file;
    }

    public function getWhatsAppTemplatesData($recordid){
        global $adb;
        $getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsapptemplates 
        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid 
        INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid 
        INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
        WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid = ?", array($recordid));
        $isTemplates = $adb->num_rows($getWhatsappTemplateQuery);
        if($isTemplates){
            $templatesID = $adb->query_result($getWhatsappTemplateQuery, 0, 'ctwhatsapptemplatesid');
            $message = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_text');
            $templateMsg = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_msg');
            $wptemplateImage = $adb->query_result($getWhatsappTemplateQuery, 0, 'storedname');
            $wptemplateTitle = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_title');
        }
        $whatsAppTemplateData = array('templatesID' => $templatesID, 'message' => $message, 'templateMsg' => $templateMsg, 'wptemplateImage' => $wptemplateImage, 'wptemplateTitle' => $wptemplateTitle);
        return $whatsAppTemplateData;
    }

    public function getAttachmentData($templatesID){
        global $adb;
        $getAttachmentQuery = $adb->pquery("SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
        INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
        WHERE vtiger_seattachmentsrel.crmid = ? AND vtiger_crmentity.deleted = 0", array($templatesID));

        $imageId = $adb->query_result($getAttachmentQuery, 0, 'attachmentsid');
        $imagePath = $adb->query_result($getAttachmentQuery, 0, 'path');
        $imageName = $adb->query_result($getAttachmentQuery, 0, 'storedname');

        $attachmentData = array('imageId' => $imageId, 'imagePath' => $imagePath, 'imageName' => $imageName);
        return $attachmentData;
    }

    public function getWhatsAppRecordQuery($startdate, $enddate){
        $whatsappQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();

        $query = $whatsappQuery." AND vtiger_ctwhatsappbusiness.whatsapp_contactid = ? AND DATE(vtiger_crmentity.createdtime) BETWEEN '$startdate' AND '$enddate'";
        return $query;
    }

    public function getPhoneFieldLabel($tabid, $phoneField){
        global $adb;
        $getFieldsLabel = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid=? AND fieldname=?", array($tabid, $phoneField));
        $phonefield = $adb->query_result($getFieldsLabel, 0, 'fieldlabel');
        return $phonefield;
    }

    public function userConfigurationData($currenUserID){
        global $adb;
        $queryUserExist = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield3 LIKE '%".$currenUserID."%'", array());
        $numRowsUsers = $adb->num_rows($queryUserExist);

        if($numRowsUsers == 0){
            $queryGetGroupId = $adb->pquery("SELECT * FROM vtiger_group2role INNER JOIN vtiger_user2role ON vtiger_user2role.roleid = vtiger_group2role.roleid WHERE vtiger_user2role.userid = ?", array($currenUserID));
            $groupid = $adb->query_result($queryGetGroupId, 0, 'groupid');
            if($groupid != ''){
                $queryGroupExist = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield3 LIKE '%".$groupid."%'", array());
            }
            $numRowsUsers = $adb->num_rows($queryGroupExist);
        }
        return $numRowsUsers;
    }

    public function updateWhatsAppSatatus($currenUserID){
        global $adb;
        $udpateStatusQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusinessconfiguration SET whatsappstatus = 0 WHERE customfield5 = ?",array($currenUserID));
        $udpateStatusQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusinessusers SET whatsappstatus = 0 WHERE customfield5 = ?",array($currenUserID));
    }

    public function createNewUser($currenUserID, $adminApiUrl, $contryCode, $iconActive){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield5 = ?", array($currenUserID));
        $numrows = $adb->num_rows($query);
        if($numrows == 0){
            $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusinessconfiguration SET api_url='$adminApiUrl', customfield5='$currenUserID', customfield1='$contryCode', iconactive='$iconActive'", array());
        }
    }

    public function getLastMessageDataTime($massMessageid){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessschedulesendmsg WHERE schedulesendmsgid = $massMessageid AND vtiger_ctwhatsappbusinessschedulesendmsg.status = 1 AND send_msg = 1 AND datesendmessage != '' ORDER BY datesendmessage DESC LIMIT 0,1");
        $lastMessageDataTime = $adb->query_result($query, 0, 'datesendmessage');
        return $lastMessageDataTime;
    }

    public function getSendQueueMessages($request){
        global $adb;
        $progress = $request->get('progress');
        if($progress == 'Completed'){
            $progressMessages = ' AND status = 1';
        }else if($progress == 'InProgress'){
            $progressMessages = ' AND status = 0';
        }else if($progress == 'Hold'){
            $progressMessages = ' AND status = 2';
        }else if($progress == 'All'){
            $progressMessages = ' AND status IN (0,1,2)';
        }

        $date_var = date("Y-m-d H:i:s");
        $currenDateTime = $adb->formatDate($date_var, true);

        $getMassMessageDetail = CTWhatsAppBusiness_DashBoard_View::getMassMessageDetail();
        $batch = $getMassMessageDetail['batch'];
        $timeinterval = $getMassMessageDetail['timeinterval'];

        $periodData = CTWhatsAppBusiness_DashBoard_View::getPeriodDataQuery($request, 'sendmessagedate');
        $selectPeriodData = $request->get('periodData');
        if($selectPeriodData == 'alltime'){
            $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessmassmessage ORDER BY massmessageid DESC");
        }else{
            $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessmassmessage WHERE ".$periodData." ORDER BY massmessageid DESC LIMIT 0,5");
        }

        $rows = $adb->num_rows($query);
        $sendqueueMessages = array();
        for ($i=0; $i < $rows; $i++) { 
            $status = '';
            $expcompdate = '';
            $readRows = 0;
            $massMessageid = $adb->query_result($query, $i, 'massmessageid');
            $whatsappMessage = $adb->query_result($query, $i, 'whatsappmessage');
            $massmsgdatetime = $adb->query_result($query, $i, 'massmsgdatetime');
            $templatesid = $adb->query_result($query, $i, 'templatesid');
            $connectuserid = $adb->query_result($query, $i, 'connectuserid');

            $connectWPNumberQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE customfield5 = ?", array($connectuserid));
            $connctWPNumber = $adb->num_rows($connectWPNumberQuery);
            if($connctWPNumber == 1){
                $whatsappno = $adb->query_result($connectWPNumberQuery, 0, 'whatsappno');
            }else{
                $connectWPNumberQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield5 = ?", array($connectuserid));
              $connctWPNumber = $adb->num_rows($connectWPNumberQuery);
                if($connctWPNumber == 1){
                    $whatsappno = $adb->query_result($connectWPNumberQuery, 0, 'whatsappno');
                }
            }

            $sendMessageDate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($adb->query_result($query, $i, 'sendmessagedate'));

            $getLastMessageDataTime = CTWhatsAppBusiness_Record_Model::getLastMessageDataTime($massMessageid);
            if($getLastMessageDataTime != ''){
                $lastMessageDate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($getLastMessageDataTime);
            }else{
                $lastMessageDate = '-';
            }

            $queryScheduleSendmsg = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessschedulesendmsg WHERE schedulesendmsgid = ? $progressMessages ORDER BY recordid DESC LIMIT 0,1", array($massMessageid));
            $sendMessageStatus = $adb->query_result($queryScheduleSendmsg, 0, 'status');
            $whatsappRecordid = $adb->query_result($queryScheduleSendmsg, 0, 'recordid');
            $rowScheduleSendmsg = $adb->num_rows($queryScheduleSendmsg);
            $scheduleSendmsgID = $adb->query_result($queryScheduleSendmsg, 0, 'schedulesendmsgid');

            
            $getReadQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN 
              vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
              WHERE vtiger_crmentity.deleted = 0 
              AND vtiger_ctwhatsappbusiness.message_type = 'Mass Message' 
              AND vtiger_ctwhatsappbusiness.whatsapp_unreadread = 'Read'
              AND vtiger_ctwhatsappbusiness.msgid !='' 
              AND vtiger_ctwhatsappbusiness.whatsapp_withoccode = ?", array($scheduleSendmsgID));
            $readRows = $adb->num_rows($getReadQuery);

            $deleteCheckQuery = $adb->pquery("SELECT * FROM vtiger_crmentity WHERE crmid = ?", array($whatsappRecordid));
            if($adb->query_result($deleteCheckQuery, 0, 'deleted') == 0){
                $setype = VtigerCRMObject::getSEType($whatsappRecordid);
            }
      
            $querytotalmsg = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessschedulesendmsg WHERE schedulesendmsgid = ?", array($scheduleSendmsgID));
            
            $totalmsg = $adb->num_rows($querytotalmsg);
            $querytotalsendmsg = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessschedulesendmsg WHERE schedulesendmsgid = ? AND send_msg = ?", array($scheduleSendmsgID,1));
            $totalSend = $adb->num_rows($querytotalsendmsg);
            $totalQueue = $totalmsg - $totalSend;

            if($totalQueue != 0){
                $total = $rowScheduleSendmsg/$batch;
                if($massmsgdatetime){
                    $currentdatetime = $massmsgdatetime;
                }else{
                    $currentdatetime = date('Y-m-d H:i:s');
                }

                if($total <= 1){
                    $expexteddatetime = strtotime($currentdatetime.' + '.$timeinterval.' minute');
                }else{
                    $totalminutes = $total * $timeinterval;
                    $expexteddatetime = strtotime($currentdatetime.' + '.round($totalminutes).' minute');
                }
            }
            
            if($sendMessageStatus == 2){
                $status = 'Hold';
                $expcompdate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $expexteddatetime));
            }else if($sendMessageStatus == 1){
                if($totalQueue == 0){
                    $status = 'Completed';
                    $expcompdate = '-';
                }else{
                    $status = 'InProgress'; 
                    $expcompdate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $expexteddatetime));
                }
            }else{
                $status = 'InProgress';
                $expcompdate = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $expexteddatetime));
            }

            $getWhatsappTemplateQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsapptemplates INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsapptemplates.ctwhatsapptemplatesid = ?", array($templatesid));
            $isTemplates = $adb->num_rows($getWhatsappTemplateQuery);

            if($isTemplates){
                $whatsappMessage = '';
                $whatsappMessage = $adb->query_result($getWhatsappTemplateQuery, 0, 'wptemplate_text');
                $templatesId = $adb->query_result($getWhatsappTemplateQuery, 0, 'ctwhatsapptemplatesid');
                $getAttachmentQuery = $adb->pquery("SELECT * FROM vtiger_attachments
                    INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
                    WHERE vtiger_seattachmentsrel.crmid = ? AND vtiger_crmentity.deleted = 0", array($templatesId));
                
                $imageId = $adb->query_result($getAttachmentQuery, 0, 'attachmentsid');
                $imagePath = $adb->query_result($getAttachmentQuery, 0, 'path');
                $imageName = $adb->query_result($getAttachmentQuery, 0, 'storedname');
                $type = explode('/', $adb->query_result($getAttachmentQuery, 0, 'type'));
                $attachmentPath = $imagePath.$imageId.'_'.$imageName;
            
                if($type[0] == 'image'){
                    $whatsappMessage .= '<br><img src="'.$attachmentPath.'" style="width: 50px;">';
                }else{
                    $whatsappMessage .= '<br><a href="'.$attachmentPath.'">'.$imageName.'</a>';
                }
            }

            $totalMessages = $totalSend + $totalQueue;
            if($rowScheduleSendmsg != ''){
                if($status == $progress){
                    $sendqueueMessages[] = array('massMessageid' => $massMessageid, 'date' => $sendMessageDate, 'whatsappmessage' => $whatsappMessage, 'totalSend' => $totalSend, 'totalQueue' => $totalQueue, 'totalMessages' => $totalMessages, 'lastMessageDate' => $lastMessageDate, 'readRows' => $readRows, 'status' => $status, 'expcompdate' => $expcompdate, 'sendMessageStatus' => $sendMessageStatus, 'setype' => vtranslate($setype, $setype), 'whatsappno' => $whatsappno);
                }else{
                    $sendqueueMessages[] = array('massMessageid' => $massMessageid, 'date' => $sendMessageDate, 'whatsappmessage' => $whatsappMessage, 'totalSend' => $totalSend, 'totalQueue' => $totalQueue, 'totalMessages' => $totalMessages, 'lastMessageDate' => $lastMessageDate, 'readRows' => $readRows, 'status' => $status, 'expcompdate' => $expcompdate, 'sendMessageStatus' => $sendMessageStatus, 'setype' => vtranslate($setype, $setype), 'whatsappno' => $whatsappno);
                }
            }
        }
        return $sendqueueMessages;
    }

    public function getWhatsAppReportData($period, $format, $periodData, $reportChart, $scanUsers, $startdate, $endtdate, $whatsAppBot){
        global $adb, $current_user;
        $currentUserID = $current_user->id;
        $inNumberQuery = CTWhatsAppBusiness_Record_Model::getInNumberQuery($currentUserID);

        if($scanUsers == 'All' || $scanUsers == ''){
            $multipleWhatsappNumber = CTWhatsAppBusiness_Record_Model::getConnectedWhatsAppNumber();
            $inNumber = '';
            foreach ($multipleWhatsappNumber as $key => $value) {
                $inNumber .= "'".$value['whatsappno']."',";
            }
            $allnumber = rtrim($inNumber, ',');
            $inNumbersQuery = ' AND cw.your_number IN ('.$allnumber.') ';
            $inNumbersBroadcastQuery = ' AND vtiger_ctwhatsappbusiness.your_number IN ('.$allnumber.') ';
            $inNumbersChatbotQuery = ' AND vtiger_ctwhatsappbusiness.your_number IN ('.$allnumber.') ';
        }else{
            $inNumbersQuery = ' AND cw.your_number IN ('.$scanUsers.') ';
            $inNumbersBroadcastQuery = ' AND vtiger_ctwhatsappbusiness.your_number IN ('.$scanUsers.') ';
            $inNumbersChatbotQuery = ' AND vtiger_ctwhatsappbusiness.your_number IN ('.$scanUsers.') ';
        }

        $arrayData = array();
        $yAxisData1 = array();
        $yAxisData2 = array();
        $yAxisData3 = array();

        $send = 0;
        $received = 0;
        $totalMessages = 0;
        $totalMassMessages = 0;
        $readMassMessages = 0;
        $unreadMassMessages = 0;
        $finishedChat = 0;
        $pendingChat = 0;
        $sendBotMessage = 0;
        $receivedBotMessage = 0;
        $botMessage = 0;
        $activeBotChat = 0;
        $finishBotChat = 0;

        foreach($period as $key => $date) {
            $conditionDate = $date->format($format);
            
            $customeQuery = '';
            $customeMassQuery = '';
            if($periodData == 'alltime'){
                $customeQuery = " AND YEAR(vtiger_crmentity.createdtime) = ?";
                $customeMassQuery = " AND YEAR(cr.createdtime) = ?";
            }else{
                $customeQuery = " AND DATE(vtiger_crmentity.createdtime) = ?";
                $customeMassQuery = " AND DATE(cr.createdtime) = ?";
            }
            if($reportChart == 'BroadcastStatistics'){
                $whatsappMessageQuery = "SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 ".$inNumberQuery." AND vtiger_ctwhatsappbusiness.message_type IN('Mass Message') AND vtiger_ctwhatsappbusiness.whatsapp_botid = '' ";
                $query = $adb->pquery($whatsappMessageQuery.$customeQuery, array($conditionDate));

            }else if($reportChart == 'SendReceiveStatistics'){
                $whatsappMessageQuery = "SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 ".$inNumbersBroadcastQuery." AND vtiger_ctwhatsappbusiness.message_type IN('Send','Recieved') AND vtiger_ctwhatsappbusiness.message_type != 'Mass Message' AND vtiger_ctwhatsappbusiness.whatsapp_botid = ''";
                $query = $adb->pquery($whatsappMessageQuery.$customeQuery, array($conditionDate));

                /*$whatsappMessageCustomQuery = "SELECT * FROM vtiger_ctwhatsappbusiness cw 
                    INNER JOIN vtiger_crmentity cr ON cr.crmid = cw.ctwhatsappid 
                    AND cr.deleted = 0 ".$customeMassQuery." ".$inNumbersQuery." AND cw.message_type  != 'Mass Message' AND cw.whatsapp_withccode != '' 
                    INNER JOIN (
                        SELECT ct2.whatsapp_withccode , MAX(ct2.ctwhatsappid) AS maxid FROM vtiger_ctwhatsappbusiness ct2 WHERE ct2.message_type != 'Mass Message'
                       GROUP BY ct2.whatsapp_withccode
                    ) ct3 ON (cw.ctwhatsappid = ct3.maxid) ORDER BY cw.whatsapp_datetime DESC";

                $query = $adb->pquery($whatsappMessageCustomQuery, array($conditionDate));*/
                $pendingfinishedRows = $adb->num_rows($query);

                for ($j=0; $j < $pendingfinishedRows; $j++) { 
                    $message_type = $adb->query_result($query, $j, 'message_type');
                    if($message_type == 'Send'){
                        $finishedChat = $finishedChat + 1;
                    }else if($message_type == 'Recieved'){
                        $pendingChat = $pendingChat + 1;
                    }
                }

            }else if($reportChart == 'ChatbotStatistics'){
                if($whatsAppBot == 'All' || $whatsAppBot == ''){
                    $whatsAppBot = '';
                    $whatsAppBotQuery = " AND vtiger_ctwhatsappbusiness.whatsapp_botid != '' ";
                }else{
                    $whatsAppBotQuery = ' AND vtiger_ctwhatsappbusiness.whatsapp_botid = '.$whatsAppBot.' ';
                }
                
                $whatsappMessageQuery = "SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 ".$inNumberQuery.$whatsAppBotQuery;
                
                $query = $adb->pquery($whatsappMessageQuery.$customeQuery, array($conditionDate));
                
                $botQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 ".$inNumberQuery." ".$whatsAppBotQuery.$customeQuery." GROUP BY vtiger_ctwhatsappbusiness.whatsapp_withccode", array($conditionDate));

                $allBotMessageRows = $adb->num_rows($botQuery);
                for ($i=0; $i < $allBotMessageRows; $i++) { 
                    $customerNumber = $adb->query_result($botQuery, $i, 'whatsapp_withccode');
                    $activeFinishQuery = $adb->pquery("SELECT * FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($customerNumber));
                    $activeFinishRows = $adb->num_rows($activeFinishQuery);
                    if($activeFinishRows == 1){
                        $activeBotChat = $activeBotChat + 1;
                    }else{
                        $finishBotChat = $finishBotChat + 1;
                    }
                }
            }

            $sent = 0;
            $read = 0;
            
            $rows = $adb->num_rows($query);
            $totalMessages = $totalMessages + $rows;
            $totalMassMessages = $totalMassMessages + $rows;
            $botMessage = $botMessage + $rows;
            for ($i=0; $i < $rows; $i++) { 
                $messageType = $adb->query_result($query, $i, 'message_type');
                if($messageType != ""){
                    $sent = $sent + 1;
                }
                if($messageType == 'Send'){
                    $send = $send + 1;
                    $sendBotMessage = $sendBotMessage + 1;
                }else if($messageType == 'Recieved'){
                    $received = $received + 1;
                    $receivedBotMessage = $receivedBotMessage + 1;
                }

                $messageReadUpRead = $adb->query_result($query, $i, 'whatsapp_unreadread');
                if($messageReadUpRead == "Read"){
                    $read = $read + 1;
                }

                if($messageReadUpRead == "Read"){
                    $readMassMessages = $readMassMessages + 1;
                }else{
                    $unreadMassMessages = $unreadMassMessages + 1;
                }
            }

            $yAxisData1['count'][] = $sent;
            $yAxisData2['count'][] = $read;
        }
        $arrayData['Sent'] = $yAxisData1; 
        $arrayData['Read'] = $yAxisData2;

        $dateFilter = ',["createdtime","bw","'.$startdate.','.$endtdate.'"]';
        if($scanUsers == 'All' || $scanUsers == ''){
            $youPhoneFilter = '';
        }else{
            $youPhoneFilter = ',["your_number","e","'.$scanUsers.'"]';
        }

        if($reportChart == 'BroadcastStatistics'){
            $totalMassMessages = $totalMassMessages;
            $totalMassMessagesURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Mass Message"]'.$dateFilter.']]';
            $totalReadMassMessages = $readMassMessages;
            $totalReadMassMessagesURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Mass Message"],["whatsapp_unreadread","e","Read"]'.$dateFilter.']]';
            $totalUnReadMassMessages = $unreadMassMessages;
            $totalUnReadMassMessagesURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Mass Message"],["whatsapp_unreadread","e","Unread"]'.$dateFilter.']]';


            $totalMessage = 0;
            $totalSentMessage = 0;
            $totalReceivedMessage = 0;
            $totalFinishedChat = 0;
            $totalPendingChat = 0;

            $totalBotMessages = 0;
            $totalSendBotMessages = 0;
            $totalReceivedBotMessage = 0;

            $totalactiveBotChat = 0;
            $totalfinishBotChat = 0;

        }else if($reportChart == 'SendReceiveStatistics'){
            $totalMessage = $totalMessages;
            $totalMessageURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Send,Recieved"]'.$dateFilter.$youPhoneFilter.']]';
            $totalSentMessage = $send;
            $totalSentMessageURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Send"]'.$dateFilter.$youPhoneFilter.']]';
            $totalReceivedMessage = $received;    
            $totalReceivedMessageURL = 'index.php?module=CTWhatsAppBusiness&view=List&search_params=[[["message_type","e","Recieved"]'.$dateFilter.$youPhoneFilter.']]';

            $totalFinishedChat = $finishedChat;
            $totalPendingChat = $pendingChat;

            $totalMassMessages = 0;
            $totalReadMassMessages = 0;
            $totalUnReadMassMessages = 0;

            $totalBotMessages = 0;
            $totalSendBotMessages = 0;
            $totalReceivedBotMessage = 0;

            $totalactiveBotChat = 0;
            $totalfinishBotChat = 0;


        }else if($reportChart == 'ChatbotStatistics'){
            $totalBotMessages = $botMessage;
            $totalSendBotMessages = $sendBotMessage;
            $totalReceivedBotMessage = $receivedBotMessage;

            $totalactiveBotChat = $activeBotChat;
            $totalfinishBotChat = $finishBotChat;

            $totalMassMessages = 0;
            $totalReadMassMessages = 0;
            $totalUnReadMassMessages = 0;

            $totalMessage = 0;
            $totalSentMessage = 0;
            $totalReceivedMessage = 0; 
            $totalFinishedChat = 0;
            $totalPendingChat = 0;
        }

        $result = array('arrayData' => $arrayData, 'totalMessage' => $totalMessage, 'totalSentMessage' => $totalSentMessage, 'totalReceivedMessage' => $totalReceivedMessage, 'totalMassMessages' => $totalMassMessages, 'totalReadMassMessages' => $totalReadMassMessages, 'totalUnReadMassMessages' => $totalUnReadMassMessages, 'totalFinishedChat' => $totalFinishedChat, 'totalPendingChat' => $totalPendingChat, 'totalSendBotMessages' => $totalSendBotMessages, 'totalReceivedBotMessage' => $totalReceivedBotMessage, 'totalBotMessages' => $totalBotMessages, 'totalactiveBotChat' => $totalactiveBotChat, 'totalfinishBotChat' => $totalfinishBotChat, 'totalMessageURL' => $totalMessageURL, 'totalSentMessageURL' => $totalSentMessageURL, 'totalReceivedMessageURL' => $totalReceivedMessageURL, 'totalMassMessagesURL' => $totalMassMessagesURL, 'totalReadMassMessagesURL' => $totalReadMassMessagesURL, 'totalUnReadMassMessagesURL' => $totalUnReadMassMessagesURL);

        return $result;
    }

    public function massMessagePauseResume($status, $recordid){
        global $adb;
        $updateStatusQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusinessschedulesendmsg SET status = ? WHERE schedulesendmsgid = ? AND send_msg = 0", array($status, $recordid));
    }

    public function massMessageDelete($recordid){
        global $adb;
        $massDeleteQuery = $adb->pquery("DELETE FROM vtiger_ctwhatsappbusinessmassmessage WHERE massmessageid = ?", array($recordid));
        $massMessageDeleteQuery = $adb->pquery("DELETE FROM vtiger_ctwhatsappbusinessschedulesendmsg WHERE schedulesendmsgid = ?", array($recordid));
    }

    public function getWhatsappTheme(){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE theme_view != ''", array());
        $theme_view = $adb->query_result($query, 0, 'theme_view');
        return $theme_view;
    }

    public function getModulefields($tabid, $sourceModuleName, $moduleRecordId){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? AND quickcreate IN('0','2') AND uitype NOT IN('56','51','10','15','5','33','57')",array($tabid));
        $num_rows = $adb->num_rows($query);
        $recordModel = Vtiger_Record_Model::getInstanceById($moduleRecordId, $sourceModuleName);

        $fieldLabelValue = array();
        for ($i=0; $i < $num_rows; $i++) { 
            $fieldname = $adb->query_result($query, $i, 'fieldname');
            $fieldlabel = $adb->query_result($query, $i, 'fieldlabel');
            $fieldValue = $recordModel->get($fieldname);
            $fieldLabelValue[$fieldlabel] = array('fieldName' => $fieldname, 'fieldValue' => $fieldValue);
        }
        return $fieldLabelValue;
    }

    public function getUserScanWhatsAppAllData($userid){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield5 = ?", array($userid));
        $row = $adb->num_rows($query);
        $api_url = $adb->query_result($query, 0, 'api_url');
        $auth_token = $adb->query_result($query, 0, 'auth_token');
        $customfield1 = $adb->query_result($query, 0, 'customfield1');
        $whatsappno = $adb->query_result($query, 0, 'whatsappno');
        $whatsappStatus = $adb->query_result($query, 0, 'whatsappstatus');
        $autoResponder = $adb->query_result($query, 0, 'customfield6');
        $autoResponderText = $adb->query_result($query, 0, 'customfield7');
        $userScanWhatsAppData = array('row' => $row, 'api_url' => $api_url, 'auth_token' => $auth_token, 'customfield1' => $customfield1, 'whatsappno' => $whatsappno, 'whatsappStatus' => $whatsappStatus, 'autoResponder' => $autoResponder, 'autoResponderText' => $autoResponderText);
        return $userScanWhatsAppData;
    }

    public function checkMessageId($messageid){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.msgid = ?", array($messageid));
        $row = $adb->num_rows($query);
        return $row;
    }

    public function getScanNumberUserId($scanNumber){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($scanNumber));
        $row = $adb->num_rows($query);
        if($row){
            $scanUserId = $adb->query_result($query, 0, 'customfield5');
            $userScanUsersData = array('scanUserId' => $scanUserId);
        }
        return $userScanUsersData;
    }

    public function getAdmminScanDetail(){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE customfield4 != ''", array());
        $row = $adb->num_rows($query);
        if($row){
            $showunknownmsg = $adb->query_result($query, 0, 'showunknownmsg');
            $customfield3 = $adb->query_result($query, 0, 'customfield3');
            $api_url = $adb->query_result($query, 0, 'api_url');
            $whatsappno = $adb->query_result($query, 0, 'whatsappno');
            $whatsappUserManagemnt = $adb->query_result($query, 0, 'customfield4');
            $admminScanDetail = array('showunknownmsg' => $showunknownmsg, 'multipleuser' => $customfield3, 'api_url' => $api_url, 'whatsappUserManagemnt' => $whatsappUserManagemnt, 'whatsappno' => $whatsappno);
        }
        return $admminScanDetail;
    }

    public function getOneDaysMessages(){
        global $adb;
        $todayDate = date("Y-m-d");
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN  vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.message_type IN ('Send','Mass Message') AND DATE(vtiger_crmentity.createdtime) = '".$todayDate."'");
        $rows = $adb->num_rows($query);
        return $rows;
    }

    public function getWhatsappAccountDetail($licenseKey){
        $apiURL = 'https://www.crmtiger.com/whatsapp/checklifromapi.php?license_key='.$licenseKey;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
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
        ));
        $result = curl_exec($curl);
        $response = json_decode($result);
        curl_close($curl);
        return $response;
    }

    public function getWhatsappNumberImportant($mobileno){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_ctwhatsappbusiness.whatsapp_withccode = ? AND vtiger_crmentity.deleted = 0 LIMIT 0,1", array($mobileno));
        $important = $adb->query_result($query, 0, 'whatsapp_important');
        return $important;
    }

    public function getlastMessageDateTime(){
        global $adb;
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.msgid != '' ORDER BY vtiger_ctwhatsappbusiness.ctwhatsappid DESC LIMIT 0,1", array());
        $whatsappDateTime = $adb->query_result($query, 0, 'whatsapp_datetime');
        return $whatsappDateTime;
    }

    public function assignAllMessage($request){
        global $adb;
        $moduleName = $request->getModule();
        $sourceModule = $request->get('sourceModule');
        $moduleRecordId = $request->get('moduleRecordId');
        $moduleRecordSearch = $request->get('moduleRecordSearch');
        $whatsappNumber = $request->get('phonenumber');
        $adb->pquery("UPDATE vtiger_ctwhatsappbusiness SET whatsapp_contactid = ? WHERE whatsapp_withccode = ?", array($moduleRecordId, $whatsappNumber));
        $adb->pquery("UPDATE vtiger_whatsappbusinesslog SET whatsapplog_contactid = ? WHERE whatsapplog_withccode = ?", array($moduleRecordId, $whatsappNumber));
    }

    public function getWhatsAppDetailWithMobileNo($whatsappNumber){
        global $adb;
        $getConfigurationDataQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappNumber));
        $whatsaAppRows = $adb->num_rows($getConfigurationDataQuery);
        if($whatsaAppRows == 0){
            $getConfigurationDatasQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappNumber));
            $whatsaAppRow = $adb->num_rows($getConfigurationDatasQuery);
            $api_url = $adb->query_result($getConfigurationDatasQuery, 0, 'api_url');
            $auth_token = $adb->query_result($getConfigurationDatasQuery, 0, 'auth_token');
            $customfield1 = $adb->query_result($getConfigurationDatasQuery, 0, 'customfield1');
            $whatsappScanNo = $adb->query_result($getConfigurationDatasQuery, 0, 'whatsappno');
            $whatsappStatus = $adb->query_result($getConfigurationDatasQuery, 0, 'whatsappstatus');
            $configureUserid = $adb->query_result($getConfigurationDatasQuery, 0, 'customfield5');
            $whatsapp_businessnumber = $adb->query_result($getConfigurationDatasQuery, 0, 'whatsapp_businessnumber');
        }else{
            $api_url = $adb->query_result($getConfigurationDataQuery, 0, 'api_url');
            $auth_token = $adb->query_result($getConfigurationDataQuery, 0, 'auth_token');
            $customfield1 = $adb->query_result($getConfigurationDataQuery, 0, 'customfield1');
            $whatsappScanNo = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappno');
            $whatsappStatus = $adb->query_result($getConfigurationDataQuery, 0, 'whatsappstatus');
            $configureUserid = $adb->query_result($getConfigurationDataQuery, 0, 'customfield5');
            $whatsapp_businessnumber = $adb->query_result($getConfigurationDataQuery, 0, 'whatsapp_businessnumber');
        }
        $whatsappDetails = array('api_url' => $api_url, 'auth_token' => $auth_token, 'customfield1' => $customfield1, 'whatsappScanNo' => $whatsappScanNo, 'whatsappStatus' => $whatsappStatus, 'configureUserid' => $configureUserid, 'username' => getUserName($configureUserid), 'whatsapp_businessnumber' => $whatsapp_businessnumber);
        return $whatsappDetails;
    }

    public function getWhatsappHistory($phone, $whatsappNumber){
        global $adb;
        $getCursor = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_history WHERE mobile_no = ? AND scanwpnumber = ?", array($phone, $whatsappNumber));
        $cursorRows = $adb->num_rows($getCursor);
        $history_id = $adb->query_result($getCursor, 0, 'history_id');
        $historyFromme = $adb->query_result($getCursor, 0, 'history_fromme');
        $remotjid = $adb->query_result($getCursor, 0, 'remotjid');
        $whatsappHistory = array('cursorRows' => $cursorRows, 'history_id' => $history_id, 'historyFromme' => $historyFromme, 'remotjid' => $remotjid);
        return $whatsappHistory;
    }

    public function insertWhatsappHistory($phone, $cursor, $history_fromme, $remoteJid, $whatsappNumber){
        global $adb;
        $phoneQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_history WHERE mobile_no = ? AND scanwpnumber = ?", array($phone, $whatsappNumber));
        $row = $adb->num_rows($phoneQuery);
        if($row == 1){
            $adb->pquery("UPDATE vtiger_ctwhatsappbusiness_history set history_id=?, history_fromme=?, remotjid=? WHERE mobile_no=?", array($cursor, $history_fromme, $remoteJid, $phone));
        }else{
            $adb->pquery('INSERT INTO vtiger_ctwhatsappbusiness_history (scanwpnumber, mobile_no, history_id, history_fromme, remotjid) values(?,?,?,?,?)', array($whatsappNumber, $phone, $cursor, $history_fromme, $remoteJid));
        }
    }

    public function saveWhatsAppHistoryData($request){
        global $adb;
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $whatsappNumber = $request->get('whatsappNumber');
        $history_status = $request->get('history_status');
        $firsttimehistory = $request->get('firsttimehistory');
        if($firsttimehistory == 1){
          $history_status = 1;
        }else{
          $history_status = $request->get('history_status');
        }
        
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_contacts_history WHERE whatsappnumber = ?", array($whatsappNumber));
        $row = $adb->num_rows($query);
        if($row == 1){
            if($start_date != ''){
                $updateQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusiness_contacts_history SET startdate = ?, enddate = ?, status = ? WHERE whatsappnumber = ?", array($start_date, $end_date, $history_status, $whatsappNumber));
            }else{
                $updateQuery = $adb->pquery("UPDATE vtiger_ctwhatsappbusiness_contacts_history SET status = ? WHERE whatsappnumber = ?", array($history_status, $whatsappNumber));
            }
        }else{
            $insertQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusiness_contacts_history(whatsappnumber, startdate, enddate, status) values(?, ?, ?, ?)", array($whatsappNumber, $start_date, $end_date, $history_status));
        }

        $configData = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);
        $apiUrl = $configData['api_url'];
        $auth_token = $configData['auth_token'];

        $url = $apiUrl.'/chatlist';
        $postfields = array();
        $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
        if($val){
            $adb->pquery("DELETE FROM vtiger_ctwhatsappbusiness_allcontacts WHERE scannedwhatsappnumber = ?", array($whatsappNumber));
            $adb->pquery("DELETE FROM vtiger_ctwhatsappbusiness_history WHERE scanwpnumber = ?", array($whatsappNumber));
        }
        foreach($val as $key => $value){
            $jid = $value['id'];
            if (strpos($jid, '@s.whatsapp.net') !== false) {
                $explodejid = explode('@', $jid);
                $customerWhatsAppNumber = $explodejid[0];
                $insertHistoryQuery = $adb->pquery("INSERT INTO vtiger_ctwhatsappbusiness_allcontacts(scannedwhatsappnumber, customerwhatsappnumber, lastmessageid, syncstatus) values(?, ?, ?, ?)", array($whatsappNumber, $customerWhatsAppNumber, '', 0));
            }
        }
    }

    public function getWhatsApphistoryDetail($request){
        global $adb;
        $whatsappNumber = $request->get('multiWPNumber');
        $query = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness_contacts_history WHERE whatsappnumber = ?", array($whatsappNumber));
        $status = $adb->query_result($query, 0, 'status');
        $startdate = $adb->query_result($query, 0, 'startdate');
        $enddate = $adb->query_result($query, 0, 'enddate');
        $result = array('status' => $status, 'startdate' => $startdate, 'enddate' => $enddate);
        return $result;
    }

    public function getConnectedWhatsAppNumber(){
        global $adb;
        $query = $adb->pquery("SELECT your_number FROM vtiger_ctwhatsappbusiness
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
            WHERE vtiger_crmentity.deleted = 0 GROUP BY vtiger_ctwhatsappbusiness.your_number", array());
        $rows = $adb->num_rows($query);
        $connectedWhatsAppNumber = array();
        for ($i=0; $i < $rows; $i++) { 
            $your_number = $adb->query_result($query, $i, 'your_number');
            $connectedWhatsAppNumber[$your_number] = array('whatsappno' => $your_number);
        }
        return $connectedWhatsAppNumber;
    }

    public function getAllWhatsAppBot(){
        global $adb;
        $query = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE remove = 0 AND botname != '' ORDER BY botid DESC", array());
        $rows = $adb->num_rows($query);

        $whatsAppBot = array();
        for ($i=0; $i < $rows; $i++) {
            $botid = $adb->query_result($query, $i, 'botid');
            $botname = $adb->query_result($query, $i, 'botname');
            $whatsAppBot[$botid] = $botname;
        }
        return $whatsAppBot;
    }
}

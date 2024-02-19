<?php
/*+*******************************************************************************
 * The content of this file is subject to the CRMTiger Pro license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is vTiger
 * The Modified Code of the Original Code owned by https://crmtiger.com/
 * Portions created by CRMTiger.com are Copyright(C) CRMTiger.com
 * All Rights Reserved.
  ***************************************************************************** */

chdir(dirname(__FILE__). '/../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php'; 

global $current_user, $adb, $root_directory, $site_URL;
$current_user = Users::getActiveAdminUser(); 
$currentusername = $current_user->first_name.' '.$current_user->last_name;

$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

if ($verify_token === 'abc123') {
  echo $challenge;exit;
}

$data = json_decode(file_get_contents("php://input"));
$msg_status = $data->entry[0]->changes[0]->value->statuses[0]->status;
if($msg_status == 'read'){
	$msg_id = $data->entry[0]->changes[0]->value->statuses[0]->id;
	$adb->pquery("UPDATE vtiger_ctwhatsappbusiness SET whatsapp_unreadread = 'Read' where msgid = ?", array($msg_id));
}
$display_phone_number = $data->entry[0]->changes[0]->value->metadata->display_phone_number;
$whatsAppBotQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers
    INNER JOIN vtiger_ctwhatsappbusiness_user_groups ON vtiger_ctwhatsappbusiness_user_groups.userid = vtiger_ctwhatsappbusinessusers.customfield5 WHERE vtiger_ctwhatsappbusinessusers.whatsappno = ?", array($display_phone_number));
$whatsAppBotRows = $adb->num_rows($whatsAppBotQuery);
$whatsappbot = $adb->query_result($whatsAppBotQuery, 0, 'whatsappbot');

if($whatsappbot == 0){
    $mediaFile = $data->entry[0]->changes[0]->value->messages[0]->type;
    if($data->entry[0]->changes[0]->value->messages){
        $body = $data->entry[0]->changes[0]->value->messages[0]->text->body;

        $recievedMessageId = $data->entry[0]->changes[0]->value->messages[0]->id;

        $whatsappScanNo = $data->entry[0]->changes[0]->value->metadata->phone_number_id;
        $participant = $data->entry[0]->changes[0]->value->contacts[0]->profile->name;
        
        $time = $data->entry[0]->changes[0]->value->messages[0]->timestamp;
        $getMessageDateTime = date("Y-m-d H:i:s",$time);

        $mobileno = $data->entry[0]->changes[0]->value->messages[0]->from;
                
        $quotMessage = $data->entry[0]->changes[0]->value->messages[0]->context->id;
        if($quotMessage){
            $replyMessageTextQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
                WHERE vtiger_ctwhatsappbusiness.your_number = ? AND vtiger_ctwhatsappbusiness.msgid = ?", array($display_phone_number, $quotMessage));
            $rows = $adb->num_rows($replyMessageTextQuery);
            if($rows){
                $quotebody = $adb->query_result($replyMessageTextQuery, 0, 'message_body');
            }
        }

        $relatedTo = '';
        $currenUserID = $current_user->id;

        $recordModel = Vtiger_Record_Model::getInstanceById($currenUserID, 'Users');
        $senderName = $recordModel->get('first_name').' '.$recordModel->get('last_name');
                
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $customfield1 = $configurationData['customfield1'];
        $authtoken = $configurationData['authtoken'];
        $iconactive = $configurationData['iconactive'];

        $configurationAdminData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
        $customfield1 = $configurationAdminData['customfield1'];
        $autoResponder = $configurationAdminData['autoResponder'];
        $autoResponderText = $configurationAdminData['autoResponderText'];

        $messageType = 'Recieved';
        $mobileno = $mobileno;
        $messageid = $recievedMessageId;
        $customerNo = $mobileno;

        $mobilenoLen = strlen($mobileno);
        if($mobilenoLen > 10 && $customfield1 !=''){
            $withoutcode = substr($mobileno,-10);
            $mobileno = $withoutcode;
        }else{
            $mobileno = $customfield1.$mobileno;
        }

        $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));
        
        $relatedTo = $relatedToData['relatedTo'];
        $getWhatsappQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppUserData($customerNo, $relatedTo, $display_phone_number);
        $configureUserid = $getWhatsappQuery['smownerid'];

        if($mobileno){
            $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        }

        if($mediaFile != 'text'){
            $mediaFileId = $data->entry[0]->changes[0]->value->messages[0]->image->id;
            if($mediaFileId == ''){
                $mediaFileId = $data->entry[0]->changes[0]->value->messages[0]->document->id;
                if($mediaFileId == ''){
                    $mediaFileId = $data->entry[0]->changes[0]->value->messages[0]->audio->id;
                    if($mediaFileId == ''){
                        $mediaFileId = $data->entry[0]->changes[0]->value->messages[0]->video->id;
                    }
                }
            }
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://graph.facebook.com/v15.0/'.$mediaFileId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$authtoken
                ),
            ));

            $result = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($result, true);

            $mediaFileURL = $response['url'];

            $mime_type = $data->entry[0]->changes[0]->value->messages[0]->image->mime_type;
            if($mime_type == ''){
                $mime_type = $data->entry[0]->changes[0]->value->messages[0]->document->mime_type;
                $pdffilename = $data->entry[0]->changes[0]->value->messages[0]->document->filename;
                if($mime_type == ''){
                    $mime_type = $data->entry[0]->changes[0]->value->messages[0]->audio->mime_type;
                    $pdffilename = $data->entry[0]->changes[0]->value->messages[0]->audio->filename;
                    if($mime_type == ''){
                        $mime_type = $data->entry[0]->changes[0]->value->messages[0]->video->mime_type;
                        $pdffilename = $data->entry[0]->changes[0]->value->messages[0]->video->filename;
                    }
                }
            }

            if($mime_type == 'image/jpeg'){
                $filename = "Image".rand().".jpeg";
            }else if($mime_type == 'application/pdf'){
                $filename = $pdffilename;
            }else if($mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
                $filename = $pdffilename;
            }else if($mime_type == 'audio/mpeg'){
                $filename = $pdffilename;
            }else if($mime_type == 'video/mp4'){
                $filename = "Video".rand().".mp4";
            }

            /*else if($mime_type == 'audio/ogg; codecs=opus'){
                $filename = "Audio".rand().".mp3";
            }*/

            if (file_exists("workflow1.txt")) {
              $userfile = fopen("workflow1.txt", "a+");     
              fwrite($userfile,"\n". print_r($mime_type,true));
              fclose($userfile);  
            } else {
              $userfile = fopen("workflow1.txt", "w+");
              fwrite($userfile, print_r($mime_type,true));
              fclose($userfile);
            }

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
            $target_file = $root_directory.$whatsappfolderpath . "$year/$month/$week/".$filename;

            $curl1 = curl_init();

            curl_setopt_array($curl1, array(
                CURLOPT_URL => $mediaFileURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_USERAGENT => 'curl/7.64.1',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$authtoken
              ),
            ));

            $response1 = curl_exec($curl1);
            curl_close($curl1);

            $newfileURL = $site_URL.$whatsappfolderpath . "$year/$month/$week/".$filename;
            $fp = fopen($target_file, 'wb');
            fwrite($fp, $response1);
            fclose($fp);
            $whatsappBody = $newfileURL;
        }else{
            $whatsappBody = $body;
        }


        $whatsappLogQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppLogData($customerNo, $relatedTo, $display_phone_number);
        $whatsapplogRows = $whatsappLogQuery['rows'];
        if($whatsapplogRows == 0){
            $recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppBusinessLog');
            $recordModel->set('whatsapplog_sendername', $senderName);
            $recordModel->set('messagelog_body', $whatsappBody);
            $recordModel->set('messagelog_type', $messageType);
            
            $recordModel->set('whatsapplog_contactid', $relatedTo);
            if($relatedTo){
                $setype = VtigerCRMObject::getSEType($relatedTo);
                $recordModelData = Vtiger_Record_Model::getInstanceById($relatedTo, $setype);
                $displayname = $recordModelData->get('label');
                $recordModel->set('whatsapplog_displayname', $displayname);
            }else{
                $recordModel->set('whatsapplog_displayname', $customerNo);
            }
            $recordModel->set('whatsapplog_unreadread', 'Unread');

            $recordModel->set('whatsapplog_withccode', $customerNo);
            $recordModel->set('whatsapplog_sendername', $customerNo);
            $recordModel->set('whatsapplog_msgid', $messageid);
            $recordModel->set('whatsapplog_datetime', $getMessageDateTime);
            $recordModel->set('whatsapplog_your_number', $display_phone_number);
            $recordModel->set('assigned_user_id', $configureUserid);
            $recordModel->set('whatsapplog_important', $getnumberImportant);
            $recordModel->save();
        }else{
            $whatsapplogid = $whatsappLogQuery['whatsappbusinesslogid'];
            $recordModel = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppBusinessLog');
            $recordModel->set('mode', 'edit');
            $recordModel->set('id', $whatsapplogid);
            $recordModel->set('whatsapplog_datetime', $getMessageDateTime);
            $recordModel->set('messagelog_body', $whatsappBody);
            $recordModel->set('assigned_user_id', $configureUserid);
            $recordModel->set('whatsapplog_unreadread', 'Unread');
            $recordModel->save();
        }

        $moduleName = 'CTWhatsAppBusiness'; 
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $recordModel->set('whatsapp_sendername', $senderName);
        $recordModel->set('message_body', $whatsappBody);
        $recordModel->set('message_type', $messageType);
        
        $recordModel->set('whatsapp_contactid', $relatedTo);
        if($relatedTo){
            $setype = VtigerCRMObject::getSEType($relatedTo);
            $recordModelData = Vtiger_Record_Model::getInstanceById($relatedTo, $setype);
            $displayname = $recordModelData->get('label');
            $recordModel->set('whatsapp_displayname', $displayname);
        }else{
            $recordModel->set('whatsapp_displayname', $customerNo);
        }
        $recordModel->set('whatsapp_unreadread', 'Unread');

        $recordModel->set('whatsapp_withccode', $customerNo);
        $recordModel->set('whatsapp_sendername', $customerNo);
        $recordModel->set('msgid', $messageid);
        $recordModel->set('whatsapp_datetime', $getMessageDateTime);
        $recordModel->set('your_number', $display_phone_number);
        $recordModel->set('assigned_user_id', $configureUserid);
        $recordModel->set('whatsapp_important', $getnumberImportant);
        $recordModel->set('whatsapp_quotemessage', $quotebody);
        $recordModel->save();

        if($mobileno){
            $userid = $_REQUEST['userid'];
            $api_url = $configurationData['api_url'];
            $auth_token = $configurationData['authtoken'];
            $whatsappBusinessNo = $configurationData['whatsapp_businessnumber'];
            
            if($autoResponder == 1){
                $whatsappQuery = CTWhatsAppBusiness_Record_Model::unreadQuery();

                $getRecordQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusiness 
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid 
                    WHERE vtiger_crmentity.deleted = 0 AND vtiger_ctwhatsappbusiness.whatsapp_withccode = ? AND vtiger_ctwhatsappbusiness.message_type = 'Recieved'", array($mobileno));
                $row = $adb->num_rows($getRecordQuery);

                if($row == 1){
                    $url = $api_url.$whatsappBusinessNo.'/messages';
                    $postfields = array('messaging_product' => "whatsapp",
                                        'recipient_type' => "individual",
                                        'to' => $mobileno,
                                        'type' => "text",
                                        'text' => array('preview_url' => false, 
                                                        'body' => $autoResponderText),
                                        );
                    $date_var = date("Y-m-d H:i:s");

                    $sendResponderMessage = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);

                    $recordModelResponder = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
                    $recordModelResponder->set('whatsapp_sendername', $currentusername);
                    $recordModelResponder->set('msgid', $sendResponderMessage['messages'][0]['id']);
                    $recordModelResponder->set('whatsapp_withccode', $mobileno);
                    $recordModelResponder->set('message_type', 'Send');
                    $recordModelResponder->set('message_body', $autoResponderText);
                    $recordModelResponder->set('whatsapp_contactid', $relatedTo);
                    //displayname changes
                    if($relatedTo){
                        $setype = VtigerCRMObject::getSEType($relatedTo);
                        $recordModelData = Vtiger_Record_Model::getInstanceById($relatedTo, $setype);
                        $displayname = $recordModelData->get('label');
                        $recordModelResponder->set('whatsapp_displayname', $displayname);
                    }else{
                        $recordModelResponder->set('whatsapp_displayname', $customerNo);
                    }
                    //displayname changes
                    $recordModelResponder->set('assigned_user_id', $configureUserid);
                    $recordModelResponder->set('whatsapp_unreadread', 'Unread');
                    $recordModelResponder->set('whatsapp_fromno', $display_phone_number);
                    $recordModelResponder->set('your_number', $display_phone_number);
                    $recordModelResponder->set('whatsapp_datetime', $adb->formatDate($date_var, true));
                    $recordModelResponder->save();
                }
            }
        }
    }
}else{

    $mediaFile = $data->entry[0]->changes[0]->value->messages[0]->type;
    if($data->entry[0]->changes[0]->value->messages){
        if($mediaFile == 'interactive'){
            $body = $data->entry[0]->changes[0]->value->messages[0]->interactive->button_reply->id;
        }else{
            $body = $data->entry[0]->changes[0]->value->messages[0]->text->body;
        }
       
        $recievedMessageId = $data->entry[0]->changes[0]->value->messages[0]->id;
        $whatsappScanNo = $data->entry[0]->changes[0]->value->metadata->display_phone_number;
        $participant = $data->entry[0]->changes[0]->value->contacts[0]->profile->name;
        
        $time = $data->entry[0]->changes[0]->value->messages[0]->timestamp;
        $getMessageDateTime = date("Y-m-d H:i:s",$time);

        $mobileno = $data->entry[0]->changes[0]->value->messages[0]->from;

        if($mobileno){
            $preQuestionData = getPreQuestionDetails($mobileno);
            $preQuestionRows = $preQuestionData['preQuestionRows'];
            $manualDateTimeDate = $preQuestionData['manualdatetime'];
            $currenBotID = $preQuestionData['prebotid'];
            if($manualDateTimeDate){
                $manualdatetime = strtotime($preQuestionData['manualdatetime']);

                $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
                $botidealtime = $configurationData['botidealtime'];
          
                $time = date("Y-m-d H:i:s");

                $messageTimeDiffrence = strtotime($time) - $manualdatetime;
                $minutes = floor(($messageTimeDiffrence / 60) % 60);
                
                if($minutes >= $botidealtime){
                    $adb->pquery("UPDATE whatsappbot_pre_que SET manualtransfer = '0' AND prequemobilenumber = ?", array($mobileno));
                }
            }
        }

        $firstbotid = getCurrentBotActive($whatsappScanNo);
        if($currenBotID == ''){
            $currenBotID = $firstbotid;
        }       

        $botLicenseDetail = CTWhatsAppBusiness_WhatsappBot_View::getBotLicenseDetail();
        $whatsAppLicenseStatus = $botLicenseDetail['status'];
        $sendmessagelimit = $botLicenseDetail['sendmessagelimit'];
        $expirydate = $botLicenseDetail['expirydate'];
        $licenseDate = Settings_CTWhatsAppBusiness_ConfigurationDetail_View::encrypt_decrypt($expirydate, $action='d');
        $currentDateTime = date('Y-m-d');

        $ondaySendMessage = getOneDaysMessages();

        $relatedTo = '';
        $currenUserID = $current_user->id;

        $recordModel = Vtiger_Record_Model::getInstanceById($currenUserID, 'Users');
        $senderName = $recordModel->get('first_name').' '.$recordModel->get('last_name');
                
        $configurationData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationAllDataWithId($currenUserID);
        $customfield1 = $configurationData['customfield1'];
        $authtoken = $configurationData['authtoken'];
        $iconactive = $configurationData['iconactive'];

        $configurationAdminData = Settings_CTWhatsAppBusiness_Record_Model::getUserConfigurationDataWithId();
        $customfield1 = $configurationAdminData['customfield1'];
        $autoResponder = $configurationAdminData['autoResponder'];
        $autoResponderText = $configurationAdminData['autoResponderText'];

        $messageType = 'Recieved';
        $mobileno = $mobileno;
        $messageid = $recievedMessageId;
        $customerNo = $mobileno;

        $mobilenoLen = strlen($mobileno);
        if($mobilenoLen > 10 && $customfield1 !=''){
            $withoutcode = substr($mobileno,-10);
            $mobileno = $withoutcode;
        }else{
            $mobileno = $customfield1.$mobileno;
        }

        $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));
        
        $relatedTo = $relatedToData['relatedTo'];
        $getWhatsappQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppUserData($customerNo, $relatedTo, $display_phone_number);
        $configureUserid = $getWhatsappQuery['smownerid'];

        if($mobileno){
            $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        }

        $whatsappBody = $body;

        $whatsappLogQuery = CTWhatsAppBusiness_Record_Model::getWhatsAppLogData($customerNo, $relatedTo, $display_phone_number);
        $whatsapplogRows = $whatsappLogQuery['rows'];
        if($whatsapplogRows == 0){
            $recordModel = Vtiger_Record_Model::getCleanInstance('WhatsAppBusinessLog');
            $recordModel->set('whatsapplog_sendername', $senderName);
            $recordModel->set('messagelog_body', $whatsappBody);
            $recordModel->set('messagelog_type', $messageType);
            
            $recordModel->set('whatsapplog_contactid', $relatedTo);
            if($relatedTo){
                $setype = VtigerCRMObject::getSEType($relatedTo);
                $recordModelData = Vtiger_Record_Model::getInstanceById($relatedTo, $setype);
                $displayname = $recordModelData->get('label');
                $recordModel->set('whatsapplog_displayname', $displayname);
            }else{
                $recordModel->set('whatsapplog_displayname', $customerNo);
            }
            $recordModel->set('whatsapplog_unreadread', 'Unread');

            $recordModel->set('whatsapplog_withccode', $customerNo);
            $recordModel->set('whatsapplog_sendername', $customerNo);
            $recordModel->set('whatsapplog_msgid', $messageid);
            $recordModel->set('whatsapplog_datetime', $getMessageDateTime);
            $recordModel->set('whatsapplog_your_number', $display_phone_number);
            $recordModel->set('assigned_user_id', $configureUserid);
            $recordModel->set('whatsapplog_important', $getnumberImportant);
            $recordModel->save();
        }else{
            $whatsapplogid = $whatsappLogQuery['whatsappbusinesslogid'];
            $recordModel = Vtiger_Record_Model::getInstanceById($whatsapplogid, 'WhatsAppBusinessLog');
            $recordModel->set('mode', 'edit');
            $recordModel->set('id', $whatsapplogid);
            $recordModel->set('whatsapplog_datetime', $getMessageDateTime);
            $recordModel->set('messagelog_body', $whatsappBody);
            $recordModel->set('assigned_user_id', $configureUserid);
            $recordModel->set('whatsapplog_unreadread', 'Unread');
            $recordModel->save();
        }

        $moduleName = 'CTWhatsAppBusiness'; 
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $recordModel->set('whatsapp_sendername', $senderName);
        $recordModel->set('message_body', $whatsappBody);
        $recordModel->set('message_type', $messageType);
        
        $recordModel->set('whatsapp_contactid', $relatedTo);
        if($relatedTo){
            $setype = VtigerCRMObject::getSEType($relatedTo);
            $recordModelData = Vtiger_Record_Model::getInstanceById($relatedTo, $setype);
            $displayname = $recordModelData->get('label');
            $recordModel->set('whatsapp_displayname', $displayname);
        }else{
            $recordModel->set('whatsapp_displayname', $customerNo);
        }
        $recordModel->set('whatsapp_unreadread', 'Unread');

        $recordModel->set('whatsapp_withccode', $customerNo);
        $recordModel->set('whatsapp_sendername', $customerNo);
        $recordModel->set('msgid', $messageid);
        $recordModel->set('whatsapp_datetime', $getMessageDateTime);
        $recordModel->set('your_number', $display_phone_number);
        $recordModel->set('assigned_user_id', $configureUserid);
        $recordModel->set('whatsapp_important', $getnumberImportant);
        $recordModel->set('whatsapp_quotemessage', $quotebody);
        $recordModel->save();

        $preQuestionData = getPreQuestionDetails($mobileno);

        $manualtransfer = $preQuestionData['manualtransfer'];
        if($manualtransfer != 1){
                if($preQuestionRows == 0){
                    defaultMessage($whatsappBody, $mobileno, $manualtransfer, $whatsappScanNo);
                }else{

                    sendNextQuestionWithOption($whatsappBody, $mobileno, $manualtransfer, $whatsappScanNo);
                }
        }
    }

    
}


function getOneDaysMessages(){
    global $adb;
    $todayDate = date("Y-m-d");
    $query = $adb->pquery("SELECT count(*) as totalrecord
            FROM vtiger_ctwhatsappbusiness
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ctwhatsappbusiness.ctwhatsappid
            WHERE vtiger_ctwhatsappbusiness.message_type = 'Send' AND vtiger_crmentity.deleted = 0 AND MONTH(vtiger_crmentity.createdtime) = MONTH(NOW())
            AND YEAR(vtiger_crmentity.createdtime) = YEAR(NOW())");
    $totalrecord = $adb->query_result($query, 0, 'totalrecord');
    return $totalrecord;
}

function getCurrentBotActive($whatsappScanNo){
    global $adb;
    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }

    $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
    $firstbotid = $adb->query_result($activeBotQuery, 0, 'botid');
    return $firstbotid;
}


function getPreQuestionDetails($mobileno){
    global $adb;
    $getPreQuestionIdQuery = $adb->pquery("SELECT * FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));

    $preQuestionRows = $adb->num_rows($getPreQuestionIdQuery);
    $preQuestionID = $adb->query_result($getPreQuestionIdQuery, 0, 'preque');
    $prebotid = $adb->query_result($getPreQuestionIdQuery, 0, 'prebotid');
    $manualtransfer = $adb->query_result($getPreQuestionIdQuery, 0, 'manualtransfer');
    $manualdatetime = $adb->query_result($getPreQuestionIdQuery, 0, 'manualdatetime');

    $result = array('preQuestionID' => $preQuestionID, 'prebotid' => $prebotid, 'manualtransfer' => $manualtransfer, 'manualdatetime' => $manualdatetime, 'preQuestionRows' => $preQuestionRows);
    return $result;
}

function getSearchModuleQuery($preQuestionID, $prebotid){
    global $adb;
    $query = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE ctwhatsappbot_que_master.next_sequence = ? AND ctwhatsappbot_que_master.botid = ?", array($preQuestionID, $prebotid));

    $searchModule = $adb->query_result($query, 0, 'search_module');
    $searchColumn = $adb->query_result($query, 0, 'search_column');
    $copy_to = $adb->query_result($query, 0, 'copy_to');
    $next_sequence = $adb->query_result($query, 0, 'next_sequence');
    $type = $adb->query_result($query, 0, 'type');
    $que_id = $adb->query_result($query, 0, 'que_id');
    $que_text = $adb->query_result($query, 0, 'que_text');
    $messagetype = $adb->query_result($query, 0, 'messagetype');

    $result = array('searchModule' => $searchModule, 'searchColumn' => $searchColumn, 'copy_to' => $copy_to, 'next_sequence' => $next_sequence, 'type' => $type, 'que_id' => $que_id, 'que_text' => $que_text, 'messagetype' => $messagetype);
    return $result;
}

function getSearchTypeQuestionQuery($preQuestionID, $prebotid){
    global $adb;
    $query = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE ctwhatsappbot_que_master.que_sequence = ? AND ctwhatsappbot_que_master.botid = ?", array($preQuestionID, $prebotid));

    $searchModule = $adb->query_result($query, 0, 'search_module');
    $searchColumn = $adb->query_result($query, 0, 'search_column');
    $copy_to = $adb->query_result($query, 0, 'copy_to');
    $next_sequence = $adb->query_result($query, 0, 'next_sequence');
    $type = $adb->query_result($query, 0, 'type');
    $que_id = $adb->query_result($query, 0, 'que_id');
    $que_text = $adb->query_result($query, 0, 'que_text');
    $varmessagetype = $adb->query_result($query, 0, 'varmessagetype');
    $copy_from = $adb->query_result($query, 0, 'copy_from');
    $responseimg = $adb->query_result($query, 0, 'responseimg');

    $result = array('searchModule' => $searchModule, 'searchColumn' => $searchColumn, 'copy_to' => $copy_to, 'next_sequence' => $next_sequence, 'type' => $type, 'que_id' => $que_id, 'que_text' => $que_text, 'varmessagetype' => $varmessagetype, 'copy_from' => $copy_from, 'responseimg' => $responseimg);
    return $result;
}

function insertResponceValue($mobileno, $body, $copy_to, $prebotid){
    global $adb;
    $adb->pquery("INSERT INTO vtiger_whatsappbot_responcevalue (mobilewhatsappnumber, variablevalue, variablename, bot_id) VALUES ('".$mobileno."', '".$body."', '".$copy_to."', '".$prebotid."')", array());
}

function getActionDetails($que_id) {
    global $adb;
    $crmActionquery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_master WHERE que_id = ?", array($que_id));
    $action = $adb->query_result($crmActionquery, 0, 'action');
    $tabid = $adb->query_result($crmActionquery, 0, 'tabid');
    $crmaction_id = $adb->query_result($crmActionquery, 0, 'crmaction_id');
    $related_tabid = $adb->query_result($crmActionquery, 0, 'related_tabid');

    $result = array('action' => $action, 'tabid' => $tabid, 'crmaction_id' => $crmaction_id, 'related_tabid' => $related_tabid);
    return $result;
}

function getInsertModuleName ($tabid){
    global $adb;
    $getModuleName = $adb->pquery("SELECT * FROM vtiger_tab WHERE tabid = ?", array($tabid));
    return $insertModuleName = $adb->query_result($getModuleName, 0, 'name');
}

function validateDate($date){
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
        return 1;
    }else{
        return 0;
    }
}

function currenAvtivateBot($prebotid){
    global $adb;
    $query = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE botid = ?", array($prebotid));
    $active = $adb->query_result($query, 0, 'deleted');
    return $active;
}

function sendNextQuestionWithOption($body, $mobileno, $manualtransfer, $whatsappScanNo){
    global $adb;
    $currentBotTime = date('Y-m-d H:i:s');
    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $preQuestionID = $preQuestionDetails['preQuestionID'];
    $prebotid = $preQuestionDetails['prebotid'];
    $manualtransfer = $preQuestionDetails['manualtransfer'];

    $currenActiveBot = currenAvtivateBot($prebotid);
    if($currenActiveBot == 1){
        $searchModuleQuery = getSearchTypeQuestionQuery($preQuestionID, $prebotid);

        $searchModule = $searchModuleQuery['searchModule'];
        $searchColumn = $searchModuleQuery['searchColumn'];
        $copy_to = $searchModuleQuery['copy_to'];
        $next_sequence = $searchModuleQuery['next_sequence'];
        $que_text = $searchModuleQuery['que_text'];

        if($manualtransfer != 1){
            if($copy_to){
                if($copy_to == 'email'){
                    if(filter_var($body, FILTER_VALIDATE_EMAIL)) {
                        insertResponceValue($mobileno, $body, $copy_to, $prebotid);
                    }else {
                        $nextQuestionBody = vtranslate('Please enter valid email', 'CTWhatsAppBusiness');
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                        exit;
                    }
                }else if($copy_to == 'number'){
                    if(is_numeric($body)){
                        insertResponceValue($mobileno, $body, $copy_to, $prebotid);
                    }else{
                        $nextQuestionBody = vtranslate('Please enter numeric only', 'CTWhatsAppBusiness');
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                        exit;
                    }
                }else if($copy_to == 'date'){
                    $dateFormate = validateDate($body);
                    if($dateFormate == 1){
                        insertResponceValue($mobileno, $body, $copy_to, $prebotid);
                    }else{
                        $nextQuestionBody = vtranslate('Please enter valid date', 'CTWhatsAppBusiness');
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                        exit;
                    }
                }else{
                    insertResponceValue($mobileno, $body, $copy_to, $prebotid);
                }
            }

            $getNextQuestionOptionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_opt_master 
                INNER JOIN ctwhatsappbot_que_opt_assign ON ctwhatsappbot_que_opt_assign.opt_id = ctwhatsappbot_opt_master.opt_id
                INNER JOIN ctwhatsappbot_que_master ON ctwhatsappbot_que_master.que_id = ctwhatsappbot_que_opt_assign.que_id
                WHERE ctwhatsappbot_opt_master.opt_seq = ? AND ctwhatsappbot_opt_master.botid = ? AND ctwhatsappbot_que_master.que_sequence = ?", array($body, $prebotid, $preQuestionID));
            $row = $adb->num_rows($getNextQuestionOptionQuery);

            if($row){
                $nextQueId = $adb->query_result($getNextQuestionOptionQuery, 0, 'next_que_id');
                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($nextQueId, $prebotid, $mobileno, $manualtransfers, $currentBotTime));

                $getNextQuestionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
                    INNER JOIN ctwhatsappbot_que_opt_assign ON ctwhatsappbot_que_opt_assign.que_id = ctwhatsappbot_que_master.que_id 
                    INNER JOIN ctwhatsappbot_opt_master ON ctwhatsappbot_opt_master.opt_id = ctwhatsappbot_que_opt_assign.opt_id 
                    WHERE ctwhatsappbot_que_master.que_sequence = ? AND ctwhatsappbot_que_opt_assign.botid = ?", array($nextQueId, $prebotid));
                $rows = $adb->num_rows($getNextQuestionQuery);  

                if($rows){
                    $nextQuestionBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text')."\n";
                    $nextQuestionButtonBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text');
                    $next_que_id = $adb->query_result($getNextQuestionQuery, 0, 'next_sequence');
                    $messagetype = $adb->query_result($getNextQuestionQuery, 0, 'messagetype');

                    $optionSequence = 0;
                    for ($i=0; $i < $rows; $i++) { 
                        $opt_text = $adb->query_result($getNextQuestionQuery, $i, 'opt_text');
                        $opt_seq = $adb->query_result($getNextQuestionQuery, $i, 'opt_seq');
                        $buttonArray[$opt_seq] = $opt_text;
                        $nextQuestionBody .= $opt_seq." ".$opt_text."\n";
                    }
                    
                     
                    if($messagetype == 'Regular message'){
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                    }else if($messagetype == 'WhatsApp Button message'){
                        sendButtonMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
                    }else if($messagetype == 'WhatsApp List message'){
                        sendButtonListMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo); 
                    }else if($messagetype == 'chatwithoperator'){
                        convertRegularMode($mobileno);              
                    }else{
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                    }
                }else{
                    $getNextQuestionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master  
                    WHERE ctwhatsappbot_que_master.que_sequence = ? AND ctwhatsappbot_que_master.botid = ?", array($nextQueId, $prebotid));
                    $nextQuestionBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text');
                    $responseimg = $adb->query_result($getNextQuestionQuery, 0, 'responseimg');
                    $next_que_id = $adb->query_result($getNextQuestionQuery, 0, 'next_sequence');
                    $messagetype = $adb->query_result($getNextQuestionQuery, 0, 'messagetype');
                    if($responseimg){
                        //sendMessageWithImage($mobileno, $nextQuestionBody, $responseimg, $whatsappScanNo);
                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                    }else{
                        if($messagetype == 'chatwithoperator'){
                            convertRegularMode($mobileno);
                        }else{
                            sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                        }
                    }
                }
                
            }else{
                if($next_sequence){
                    $typeQuestionQuery = getSearchModuleQuery($next_sequence, $prebotid);

                    $type = $typeQuestionQuery['type'];
                    $que_id = $typeQuestionQuery['que_id'];
                    $nextSequence1 = $typeQuestionQuery['next_sequence'];
                    $nextSequence12 = $typeQuestionQuery['next_sequence'];
                    $que_text = $typeQuestionQuery['que_text'];

                    $typeQueQuery = getSearchTypeQuestionQuery($nextSequence1, $prebotid);

                    $type = $typeQueQuery['type'];
                    $que_id = $typeQueQuery['que_id'];
                    $nextSequence = $typeQueQuery['next_sequence'];
                    $nextSequence12 = $typeQueQuery['next_sequence'];
                    $que_text = $typeQueQuery['que_text'];


                    if($type == "condition-node"){
                        $conditionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_condition_assign WHERE que_id = ?", array($que_id));
                        $conditionRows = $adb->num_rows($conditionQuery);
                        if($conditionRows){
                            $conditions = $adb->query_result($conditionQuery, 0, 'conditions');
                            $variablevalue = $adb->query_result($conditionQuery, 0, 'variablevalue');

                            switch ($conditions) {
                                case "equal":
                                    if($body == $variablevalue){
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }
                                    break;
                                case "not equal":
                                    if($body != $variablevalue){
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }
                                    break;
                                case "grater or equal":
                                    if($body >= $variablevalue){
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }
                                    break;
                                case "less than or equal":
                                    if($body <= $variablevalue){
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }
                                    break;
                                case "like":
                                    if (preg_match('/\b'.$variablevalue.'\b/', $body)) {
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }
                                    break;
                                case "not like":
                                    if (preg_match('/\b'.$variablevalue.'\b/', $body)) {
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id2');
                                    }else{
                                        $next_que_id = $adb->query_result($conditionQuery, 0, 'next_que_id1');
                                    }
                                    break;
                            }
                            
                            $selectQuery1 = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_sequence = ? AND botid = ?", array($next_que_id, $prebotid));
                            $nextQuestionBody = $adb->query_result($selectQuery1, 0, 'que_text');
                            $nextEndsequence = $adb->query_result($selectQuery1, 0, 'next_sequence');
                            $responseimg = $adb->query_result($selectQuery1, 0, 'responseimg');

                            if($responseimg){
                                //sendMessageWithImage($mobileno, $nextQuestionBody, $responseimg, $whatsappScanNo);
                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                            }else{
                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                            }
                            if($nextEndsequence){
                                $endNodQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_sequence = ? AND botid = ?", array($nextEndsequence, $prebotid));
                                $nextEndQuestionBody = $adb->query_result($endNodQuery, 0, 'que_text');
                                if($nextEndQuestionBody == 'End'){
                                    $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                }
                            }
                        }
                    }else if($type == 'crm-action-node'){
                        $actionDetail = getActionDetails($que_id);

                        $action = $actionDetail['action'];
                        $tabid = $actionDetail['tabid'];
                        $crmaction_id = $actionDetail['crmaction_id'];
                        $related_tabid = $actionDetail['related_tabid'];

                        if($tabid){
                            $insertModuleName = getInsertModuleName($tabid);
                        }

                        if($action == 'Insert'){

                            $relatedFieldQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_relatedfieldmapping WHERE botid = ?", array($prebotid));
                            $relatedRows = $adb->num_rows($relatedFieldQuery);
                            
                            $relatedFindQuery = '';
                            for ($m=0; $m < $relatedRows; $m++) { 
                                $relflowbuilderfield = $adb->query_result($relatedFieldQuery, $m, 'relflowbuilderfield');
                                $relvtigerfield = $adb->query_result($relatedFieldQuery, $m, 'relvtigerfield');
                                if($relflowbuilderfield){
                                    $relatedfieldValueQuery = $adb->pquery("SELECT * FROM vtiger_whatsappbot_responcevalue WHERE variablename = ? AND bot_id = ?", array($relflowbuilderfield, $prebotid));
                                    $variablevalue = $adb->query_result($relatedfieldValueQuery, 0, 'variablevalue');

                                    $getUiTypeQuery = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tabid = ?", array($relvtigerfield, $related_tabid));
                                    $vtigerFielduiType = $adb->query_result($getUiTypeQuery, 0, 'uitype');
                                    if($vtigerFielduiType == '11'){
                                        $relatedFindQuery .= '  AND '.$relvtigerfield.'="'.$variablevalue.'"';
                                    }
                                } 
                            }

                            $relatedModuleName = getInsertModuleName($related_tabid);
                            if($relatedModuleName){
                                $moduleModel = CRMEntity::getInstance($relatedModuleName);
                                $moduleInstance = Vtiger_Module::getInstance($relatedModuleName);
                                $baseTable = $moduleInstance->basetable;
                                $baseTableid = $moduleInstance->basetableid;

                                $getSearchRelatedQuery = $adb->pquery("SELECT ".$baseTable.".* FROM ".$baseTable." 
                                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ".$baseTable.".".$baseTableid." WHERE vtiger_crmentity.deleted = 0 ".$relatedFindQuery, array());
                                
                                $relatedRecordRows = $adb->num_rows($getSearchRelatedQuery);
                                if($relatedRecordRows){
                                    $relatedModuleId = $adb->query_result($getSearchRelatedQuery, 0, $baseTableid);
                                }else{
                                    $relatedRecorModel = Vtiger_Record_Model::getCleanInstance($relatedModuleName);

                                    for ($m=0; $m < $relatedRows; $m++) { 
                                        $relflowbuilderfield = $adb->query_result($relatedFieldQuery, $m, 'relflowbuilderfield');
                                        $relvtigerfield = $adb->query_result($relatedFieldQuery, $m, 'relvtigerfield');
                                        if($relflowbuilderfield){
                                            $relatedfieldValueQuery = $adb->pquery("SELECT * FROM vtiger_whatsappbot_responcevalue WHERE variablename = ? AND bot_id = ?", array($relflowbuilderfield, $prebotid));
                                            $variablevalue = $adb->query_result($relatedfieldValueQuery, 0, 'variablevalue');
                                            $relatedFindQuery .= '  AND '.$relvtigerfield.'="'.$variablevalue.'"';

                                            $relatedRecorModel->set($relvtigerfield, $variablevalue);
                                        }
                                    }
                                    $relatedRecorModel->save();
                                    $relatedModuleId = $relatedRecorModel->getId();
                                }
                            }

                            $getMappingFieldQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_fieldmapping WHERE crmaction_id = ? AND maptype = 'request'", array($crmaction_id));
                            $mappingRows = $adb->num_rows($getMappingFieldQuery);

                            $insertRecordModel = Vtiger_Record_Model::getCleanInstance($insertModuleName);
                            $modelData = $insertRecordModel->getData();
                            for ($i=0; $i < $mappingRows; $i++) { 
                                $flowbuilderfield = $adb->query_result($getMappingFieldQuery, $i, 'flowbuilderfield');
                                $vtigerfield = $adb->query_result($getMappingFieldQuery, $i, 'vtigerfield');
                                $defaultvalue = $adb->query_result($getMappingFieldQuery, $i, 'defaultvalue');
                                $fieldValueQuery = $adb->pquery("SELECT * FROM vtiger_whatsappbot_responcevalue WHERE variablename = ? AND mobilewhatsappnumber = ? AND bot_id = ?", array($flowbuilderfield, $mobileno, $prebotid));
                                $variablevalue = $adb->query_result($fieldValueQuery, 0, 'variablevalue');

                                if($tabid){
                                    if($defaultvalue == "undefined" || $defaultvalue == ""){
                                        $insertRecordModel->set($vtigerfield, $variablevalue);
                                    }else{
                                        $insertRecordModel->set($vtigerfield, $defaultvalue);
                                    }
                                }
                            } 

                            $relatedFieldNameQuery = $adb->pquery("SELECT * FROM vtiger_fieldmodulerel 
                                INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid 
                             WHERE vtiger_fieldmodulerel.module = ? AND vtiger_fieldmodulerel.relmodule = ?", array($insertModuleName, $relatedModuleName));
                            $relatedFieldName = $adb->query_result($relatedFieldNameQuery, 0, 'columnname');
                            if($relatedFieldName){
                                $insertRecordModel->set($relatedFieldName, $relatedModuleId);
                            }
                            
                            $insertRecordModel->save();

                            $fieldMappingQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_fieldmapping WHERE crmaction_id = ? AND maptype = 'response'", array($crmaction_id));

                            $vtigerfield = $adb->query_result($fieldMappingQuery, 0, 'vtigerfield');
                            $flowbuilderfield = explode('@@', $adb->query_result($fieldMappingQuery, 0, 'flowbuilderfield'));

                            if($flowbuilderfield){
                                $vtigerfieldValue = $insertRecordModel->get($vtigerfield);

                                $adb->pquery("INSERT INTO vtiger_whatsappbot_responcevalue (mobilewhatsappnumber, variablevalue, variablename, bot_id) VALUES ('".$mobileno."', '".$vtigerfieldValue."', '".$flowbuilderfield[1]."', '".$prebotid."')", array());
                            }
                            
                            if($nextSequence){
                                $questionData = getSearchTypeQuestionQuery($nextSequence, $prebotid);
                                $varmessagetype = $questionData['varmessagetype'];
                                $copy_from = $questionData['copy_from'];
                                $queText = $questionData['que_text'];
                                $responseimg = $questionData['responseimg'];
                                $next_sequenceId = $questionData['next_sequence'];

                                if($varmessagetype == 'WhatsApp Response'){
                                    if($copy_from){
                                        $fieldValueQuery = $adb->pquery("SELECT * FROM vtiger_whatsappbot_responcevalue WHERE variablename = ? AND mobilewhatsappnumber = ? AND bot_id = ?", array($copy_from, $mobileno, $prebotid));
                                        $variablevalue = $adb->query_result($fieldValueQuery, 0, 'variablevalue');
                                        $nextQuestionBody = str_replace("@@".$copy_from,$variablevalue,$queText);

                                        if($next_sequenceId){
                                            $endBotQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_sequence = ? AND botid = ?", array($next_sequenceId, $prebotid));
                                            $endNod = $adb->query_result($endBotQuery, 0, 'que_text');
                                            if($endNod == 'End'){
                                                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                            }
                                        }

                                        if($responseimg){
                                            //sendMessageWithImage($mobileno, $nextQuestionBody, $responseimg, $whatsappScanNo);
                                            sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                        }else{
                                            sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                        }
                                        $adb->pquery("DELETE FROM vtiger_whatsappbot_responcevalue WHERE mobilewhatsappnumber = ?", array($mobileno));
                                    }
                                }else{
                                    if($que_text == ''){
                                        $searchModuleQuery = getSearchModuleQuery($nextSequence, $prebotid);
                                        $nextSequence = $searchModuleQuery['next_sequence'];

                                        $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                        $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($nextSequence, $prebotid, $mobileno, $manualtransfers, $currentBotTime));

                                        $getNextQuestionsQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
                                                INNER JOIN ctwhatsappbot_que_opt_assign ON ctwhatsappbot_que_opt_assign.que_id = ctwhatsappbot_que_master.que_id 
                                                INNER JOIN ctwhatsappbot_opt_master ON ctwhatsappbot_opt_master.opt_id = ctwhatsappbot_que_opt_assign.opt_id 
                                                WHERE ctwhatsappbot_que_master.que_sequence = $nextSequence AND ctwhatsappbot_que_master.botid = $prebotid"); 

                                        $nextQuestionBody = $adb->query_result($getNextQuestionsQuery, 0, 'que_text')."\n";
                                        $optionRow = $adb->num_rows($getNextQuestionsQuery);
                                        if($optionRow){
                                            $nextSequence = $adb->query_result($getNextQuestionsQuery, 0, 'next_sequence');
                                            $nextQuestionButtonBody = $adb->query_result($getNextQuestionsQuery, 0, 'que_text');
                                            $messagetype = $adb->query_result($getNextQuestionsQuery, 0, 'messagetype');
                                            for ($i=0; $i < $optionRow; $i++) { 
                                                $opt_text = $adb->query_result($getNextQuestionsQuery, $i, 'opt_text');
                                                $opt_seq = $adb->query_result($getNextQuestionsQuery, $i, 'opt_seq');
                                                $buttonArray[$opt_seq] = $opt_text;
                                                $nextQuestionBody .= $opt_seq." ".$opt_text."\n";
                                            } 

                                            $endNodQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_sequence = ? AND botid = ?", array($nextSequence, $prebotid));
                                            $nextEndQuestionBody1 = $adb->query_result($endNodQuery, 0, 'que_text');
                                            if($nextEndQuestionBody1 == 'End'){
                                                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                            }

                                            if($messagetype == 'Regular message'){
                                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                            }else if($messagetype == 'WhatsApp Button message'){
                                                sendButtonMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
                                            }else if($messagetype == 'WhatsApp List message'){
                                                sendButtonListMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
                                            }else if($messagetype == 'chatwithoperator'){
                                                convertRegularMode($mobileno);              
                                            }
                                        }else{
                                            $getNextQuestionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
                                                WHERE que_sequence = ? AND botid = ?", array($nextSequence, $prebotid));
                                            $nextQuestionBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text');
                                            $nextSequence = $adb->query_result($getNextQuestionQuery, 0, 'next_sequence');

                                            $endNodQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_sequence = ? AND botid = ?", array($nextSequence, $prebotid));
                                            $nextEndQuestionBody1 = $adb->query_result($endNodQuery, 0, 'que_text');
                                            if($nextEndQuestionBody1 == 'End'){
                                                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                            }
                                            
                                            if($nextQuestionBody != 'End'){
                                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                            }
                                        }
                                    }
                                }
                            }   
                        }else if ($action == 'Search'){
                            $getMappingFieldQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_fieldmapping WHERE crmaction_id = ? AND maptype = 'request'", array($crmaction_id));
                            $requestRows = $adb->num_rows($getMappingFieldQuery);

                            if($requestRows){
                                $vtigerfield = $adb->query_result($getMappingFieldQuery, 0, 'vtigerfield');

                                $getSearchTableQuery = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($tabid, $vtigerfield));
                                $tablename = $adb->query_result($getSearchTableQuery, 0, 'tablename');

                                $moduleModel = CRMEntity::getInstance($insertModuleName);
                                $moduleInstance = Vtiger_Module::getInstance($insertModuleName);
                                $baseTable = $moduleInstance->basetable;
                                $baseTableid = $moduleInstance->basetableid;

                                $getSearchQuery = $adb->pquery("SELECT ".$tablename.".* FROM ".$tablename." 
                                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ".$tablename.".".$baseTableid." WHERE ".$vtigerfield." = ?", array($body));
                                $serachRows = $adb->num_rows($getSearchQuery);
                                if($serachRows){
                                    $getResponceFieldQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_crmaction_fieldmapping WHERE crmaction_id = ? AND maptype = 'response'", array($crmaction_id));

                                    $vtigerResponceField = $adb->query_result($getResponceFieldQuery, 0, 'vtigerfield');

                                    $query = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? AND fieldname = ?", array($tabid, $vtigerResponceField));
                                    $fieldColumnName = $adb->query_result($query, 0, 'columnname');

                                    $fieldValue = $adb->query_result($getSearchQuery, 0, $fieldColumnName);
                                    insertResponceValue($mobileno, $fieldValue, $vtigerResponceField, $prebotid);
                                }

                                $questionData = getSearchTypeQuestionQuery($nextSequence, $prebotid);
                                $que_text = $questionData['que_text'];
                                $copy_from = $questionData['copy_from'];
                                $next_sequence = $questionData['next_sequence'];
                                $responseimg = $questionData['responseimg'];

                                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($next_sequence, $prebotid, $mobileno, $manualtransfers, $currentBotTime));

                                if($copy_from){
                                    $fieldValueQuery = $adb->pquery("SELECT * FROM vtiger_whatsappbot_responcevalue WHERE variablename = ? AND mobilewhatsappnumber = ? AND bot_id = ?", array($vtigerResponceField, $mobileno, $prebotid));

                                    $variablevalue = $adb->query_result($fieldValueQuery, 0, 'variablevalue');
                                    $nextQuestionBody = str_replace("@@".$copy_from,$variablevalue,$que_text);

                                    if($responseimg){
                                        //sendMessageWithImage($mobileno, $nextQuestionBody, $responseimg, $whatsappScanNo);
                                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                    }else{
                                        sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                    }
                                }
                            }
                        }
                    }else{
                        
                        $messagetypeId = $typeQuestionQuery['que_id'];
                        
                        if($messagetypeId){
                            $messageTypequery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE que_id = ?", array($messagetypeId));
                            $messagetype = $adb->query_result($messageTypequery, 0, 'messagetype');

                            if($messagetype == 'Regular message'){
                                $sinumericQuery = " AND opt_seq = ".$body;  
                            }else{
                                $sinumericQuery = "";
                            }
                        }

                        $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                        $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($nextSequence1, $prebotid, $mobileno, $manualtransfers, $currentBotTime));

                        $getNextQuestionsQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
                                INNER JOIN ctwhatsappbot_que_opt_assign ON ctwhatsappbot_que_opt_assign.que_id = ctwhatsappbot_que_master.que_id 
                                INNER JOIN ctwhatsappbot_opt_master ON ctwhatsappbot_opt_master.opt_id = ctwhatsappbot_que_opt_assign.opt_id 
                                WHERE ctwhatsappbot_que_master.que_sequence = $nextSequence1 AND ctwhatsappbot_que_master.botid = $prebotid"); 

                        $nextQuestionBody = $adb->query_result($getNextQuestionsQuery, 0, 'que_text')."\n";
                        $optionRow = $adb->num_rows($getNextQuestionsQuery);
                        if($optionRow){
                            $nextSequence = $adb->query_result($getNextQuestionsQuery, 0, 'next_sequence');
                            $nextQuestionButtonBody = $adb->query_result($getNextQuestionsQuery, 0, 'que_text');
                            $messagetype = $adb->query_result($getNextQuestionsQuery, 0, 'messagetype');
                            for ($i=0; $i < $optionRow; $i++) { 
                                $opt_text = $adb->query_result($getNextQuestionsQuery, $i, 'opt_text');
                                $opt_seq = $adb->query_result($getNextQuestionsQuery, $i, 'opt_seq');
                                $buttonArray[$opt_seq] = $opt_text;
                                $nextQuestionBody .= $opt_seq." ".$opt_text."\n";
                            } 
                            if($messagetype == 'Regular message'){
                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                            }else if($messagetype == 'WhatsApp Button message'){
                                sendButtonMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
                            }else if($messagetype == 'WhatsApp List message'){
                                sendButtonListMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
                            }else if($messagetype == 'chatwithoperator'){
                                convertRegularMode($mobileno);
                            }
                        }else{
                            $getNextQuestionQuery1 = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
                            WHERE que_sequence = ? AND botid = ?".$sinumericQuery, array($nextSequence1, $prebotid));
                            $nxtQuestionRows = $adb->num_rows($getNextQuestionQuery1);
                            if($nxtQuestionRows){
                                $nextQuestionBody = $adb->query_result($getNextQuestionQuery1, 0, 'que_text');

                                $next_que_id = $adb->query_result($getNextQuestionQuery1, 0, 'next_sequence');
                                $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
                                $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($nextSequence1, $prebotid, $mobileno, $manualtransfers, $currentBotTime));
                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                            }else{
                                $nextQuestionBody = vtranslate('Please enter valid input', 'CTWhatsApp');
                                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
}

function defaultMessage($body, $mobileno, $manualtransfer, $whatsappScanNo){
    global $adb;
    $currentBotTime = date('Y-m-d H:i:s');
    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $manualtransfer = $preQuestionDetails['manualtransfer'];

    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }

    //if($manualtransfer != 1){
        $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
        $firstbotid = $adb->query_result($activeBotQuery, 0, 'botid');

        $firstQueQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master WHERE botid = ? ORDER BY ctwhatsappbot_que_master.que_id ASC LIMIT 0,1", array($firstbotid));
        $firstQueId = $adb->query_result($firstQueQuery, 0, 'next_sequence');

        $getNextQuestionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
            INNER JOIN ctwhatsappbot_que_opt_assign ON ctwhatsappbot_que_opt_assign.que_id = ctwhatsappbot_que_master.que_id 
            INNER JOIN ctwhatsappbot_opt_master ON ctwhatsappbot_opt_master.opt_id = ctwhatsappbot_que_opt_assign.opt_id 
            WHERE ctwhatsappbot_que_master.que_sequence = $firstQueId AND ctwhatsappbot_que_master.botid = $firstbotid");
        $rows = $adb->num_rows($getNextQuestionQuery);

        $adb->pquery("DELETE FROM whatsappbot_pre_que WHERE prequemobilenumber = ?", array($mobileno));
        $adb->pquery("INSERT INTO whatsappbot_pre_que (preque, prebotid, prequemobilenumber, manualtransfer, idealdatetime) VALUES (?,?,?,?,?)", array($firstQueId, $firstbotid, $mobileno ,$manualtransfer, $currentBotTime));

        if($rows){
            $nextQuestionBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text')."\n";
            $nextQuestionButtonBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text');
            $que_sequence = $adb->query_result($getNextQuestionQuery, 0, 'que_sequence');
            $messagetype = $adb->query_result($getNextQuestionQuery, 0, 'messagetype');
            
            $buttonArray = array();
            for ($i=0; $i < $rows; $i++) { 
                $opt_text = $adb->query_result($getNextQuestionQuery, $i, 'opt_text');
                $opt_seq = $adb->query_result($getNextQuestionQuery, $i, 'opt_seq');
                $buttonArray[$opt_seq] = $opt_text;
                $nextQuestionBody .= $opt_seq." ".$opt_text."\n";

            }

            if($messagetype == 'Regular message'){
                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
            }else if($messagetype == 'WhatsApp Button message'){
                sendButtonMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
            }else if($messagetype == 'WhatsApp List message'){
                sendButtonListMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo);
            }else if($messagetype == 'chatwithoperator'){
                convertRegularMode($mobileno);
            }else{
                sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo);
            }
        }else{
            $getNextQuestionQuery = $adb->pquery("SELECT * FROM ctwhatsappbot_que_master 
            WHERE ctwhatsappbot_que_master.que_sequence = $firstQueId AND ctwhatsappbot_que_master.botid = $firstbotid");
            $nextQuestionBody = $adb->query_result($getNextQuestionQuery, 0, 'que_text');
            sendMessage($mobileno, $nextQuestionBody);
        }
    //}
}

function sendButtonListMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo){
    global $current_user, $adb;
    $currenUserID = $current_user->id;

    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $currentBotId = $preQuestionDetails['prebotid'];

    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }

    $confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $num_rows = $adb->num_rows($confugurationQuery);
    if($num_rows){
        $api_url = $adb->query_result($confugurationQuery, 0, 'api_url');
    }

    $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
    $whatsappNumber = $adb->query_result($activeBotQuery, 0, 'scanwhatsapp_number');

    $configData = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);
    $apiUrl = $configData['api_url'];
    $auth_token = $configData['auth_token'];

    if($nextQuestionBody != ""){
        $url = $apiUrl.'/sendbutton';

        $sendButtonListValue['title'] = '';
        foreach ($buttonArray as $key => $value) {
            $sendButtonListValue['rows'][] = array("title" => $value, "rowId" => $key);
        }

        $postfields = array(
            'number' => $mobileno,
            'message' => array(
                "text" => html_entity_decode(htmlspecialchars_decode($nextQuestionButtonBody, ENT_QUOTES)),
                "footer" => "",
                "title" => "",
                "buttonText" => "Required, text on the button to view the list",
                "sections" => array($sendButtonListValue)
            )
        );

        $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
        $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        if($val){
            $currentusername = $current_user->first_name.' '.$current_user->last_name;
            $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));         
            $relatedTo = $relatedToData['relatedTo'];
            $displayname = $relatedToData['displayname'];
            $date_var = date("Y-m-d H:i:s");

            $recordModelResponder = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
            $recordModelResponder->set('whatsapp_sendername', $currentusername);
            $recordModelResponder->set('msgid', $val['key']['id']);
            $recordModelResponder->set('whatsapp_withccode', $mobileno);
            $recordModelResponder->set('message_type', 'Send');
            $recordModelResponder->set('message_body', $nextQuestionBody);
            //displayname changes
            if($displayname){
                $recordModelResponder->set('whatsapp_displayname', $displayname);
            }
            //displayname changes
            $recordModelResponder->set('whatsapp_contactid', $relatedTo);
            $recordModelResponder->set('whatsapp_unreadread', 'Unread');
            $recordModelResponder->set('whatsapp_fromno', $whatsappNumber);
            $recordModelResponder->set('your_number', $whatsappNumber);
            $recordModelResponder->set('whatsapp_important', $getnumberImportant);
            $recordModelResponder->set('whatsapp_botid', $currentBotId);
            $recordModelResponder->set('whatsapp_datetime', $adb->formatDate($date_var, true));
            $recordModelResponder->save();
        }
    }
}

function sendButtonMessage($mobileno, $nextQuestionButtonBody, $buttonArray, $nextQuestionBody, $whatsappScanNo){
    global $current_user, $adb;
    $currenUserID = $current_user->id;

    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $currentBotId = $preQuestionDetails['prebotid'];

    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
        $whatsappBusinessNo = $adb->query_result($getUserIdQuery, 0, 'whatsapp_businessnumber');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
        $whatsappBusinessNo = '';
    }

    $confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $num_rows = $adb->num_rows($confugurationQuery);
    if($num_rows){
        $api_url = $adb->query_result($confugurationQuery, 0, 'api_url');
    }

    $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
    $whatsappNumber = $adb->query_result($activeBotQuery, 0, 'scanwhatsapp_number');

    $configData = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);
    $apiUrl = $configData['api_url'];
    $auth_token = $configData['auth_token'];

    if($nextQuestionBody != ""){
        $sendButtonValue = array();
        foreach ($buttonArray as $key => $value) {
            $sendButtonValue[] = array("type" => "reply", "reply" => array("id" => $key, "title" => html_entity_decode(htmlspecialchars_decode($value, ENT_QUOTES))));
        }

        $url = $apiUrl.$whatsappBusinessNo.'/messages';
        $postfields = array('messaging_product' => "whatsapp",
                            'recipient_type' => "individual",
                            'to' => $mobileno,
                            'type' => "interactive",
                            'interactive' => array(
                                'type' => "button", 
                                'body' => array('text' => htmlspecialchars_decode($nextQuestionBody, ENT_QUOTES)),
                                'action' => array('buttons' => $sendButtonValue)),
                            );
        
        $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
        
        $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        if($val){
            $currentusername = $current_user->first_name.' '.$current_user->last_name;
            $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));         
            $relatedTo = $relatedToData['relatedTo'];
            $displayname = $relatedToData['displayname'];
            $date_var = date("Y-m-d H:i:s");

            $recordModelResponder = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
            $recordModelResponder->set('whatsapp_sendername', $currentusername);
            $recordModelResponder->set('msgid', $val['key']['id']);
            $recordModelResponder->set('whatsapp_withccode', $mobileno);
            $recordModelResponder->set('message_type', 'Send');
            $recordModelResponder->set('message_body', $nextQuestionBody);
            //displayname changes
            if($displayname){
                $recordModelResponder->set('whatsapp_displayname', $displayname);
            }
            //displayname changes
            $recordModelResponder->set('whatsapp_contactid', $relatedTo);
            $recordModelResponder->set('whatsapp_unreadread', 'Unread');
            $recordModelResponder->set('whatsapp_fromno', $whatsappNumber);
            $recordModelResponder->set('your_number', $whatsappNumber);
            $recordModelResponder->set('whatsapp_important', $getnumberImportant);
            $recordModelResponder->set('whatsapp_botid', $currentBotId);
            $recordModelResponder->set('whatsapp_datetime', $adb->formatDate($date_var, true));
            $recordModelResponder->save();
        }
    }
}

function sendMessage($mobileno, $nextQuestionBody, $whatsappScanNo){
    global $current_user, $adb;
    $currenUserID = $current_user->id;

    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $currentBotId = $preQuestionDetails['prebotid'];

    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }

    $confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $num_rows = $adb->num_rows($confugurationQuery);
    if($num_rows){
        $api_url = $adb->query_result($confugurationQuery, 0, 'api_url');
    }

    $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
    $whatsappNumber = $adb->query_result($activeBotQuery, 0, 'scanwhatsapp_number');

    $configData = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);

    $apiUrl = $configData['api_url'];
    $auth_token = $configData['auth_token'];
    $whatsappBusinessNo = $configData['whatsapp_businessnumber'];

    if($nextQuestionBody != ""){
        $url = $apiUrl.$whatsappBusinessNo.'/messages';
        $postfields = array('messaging_product' => "whatsapp",
                            'recipient_type' => "individual",
                            'to' => $mobileno,
                            'type' => "text",
                            'text' => array('preview_url' => false, 
                                            'body' => htmlspecialchars_decode($nextQuestionBody, ENT_QUOTES)),
                            );

        $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
        
        $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        if($val){
            $currentusername = $current_user->first_name.' '.$current_user->last_name;
            $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));         
            $relatedTo = $relatedToData['relatedTo'];
            $displayname = $relatedToData['displayname'];
            $date_var = date("Y-m-d H:i:s");

            $recordModelResponder = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
            $recordModelResponder->set('whatsapp_sendername', $currentusername);
            $recordModelResponder->set('msgid', $val['messages'][0]['id']);
            $recordModelResponder->set('whatsapp_withccode', $mobileno);
            $recordModelResponder->set('message_type', 'Send');
            $recordModelResponder->set('message_body', $nextQuestionBody);
            //displayname changes
            if($displayname){
                $recordModelResponder->set('whatsapp_displayname', $displayname);
            }
            //displayname changes
            $recordModelResponder->set('whatsapp_contactid', $relatedTo);
            $recordModelResponder->set('whatsapp_unreadread', 'Unread');
            $recordModelResponder->set('whatsapp_fromno', $whatsappNumber);
            $recordModelResponder->set('your_number', $whatsappNumber);
            $recordModelResponder->set('whatsapp_important', $getnumberImportant);
            $recordModelResponder->set('whatsapp_botid', $currentBotId);
            $recordModelResponder->set('whatsapp_datetime', $adb->formatDate($date_var, true));
            $recordModelResponder->save();
        }
    }
}

function sendMessageWithImage($mobileno, $nextQuestionBody, $responseimg, $whatsappScanNo){
    global $current_user, $adb, $site_URL;
    $currenUserID = $current_user->id;

    $preQuestionDetails = getPreQuestionDetails($mobileno);
    $currentBotId = $preQuestionDetails['prebotid'];

    $getUserGrous = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $multipleWahtsapp = $adb->query_result($getUserGrous, 0, 'customfield4');
    if($multipleWahtsapp == 'multipleWhatsapp'){
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessusers WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }else{
        $getUserIdQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration WHERE whatsappno = ?", array($whatsappScanNo));
        $userid = $adb->query_result($getUserIdQuery, 0, 'customfield5');
    }

    $confugurationQuery = $adb->pquery("SELECT * FROM vtiger_ctwhatsappbusinessconfiguration");
    $num_rows = $adb->num_rows($confugurationQuery);
    if($num_rows){
        $api_url = $adb->query_result($confugurationQuery, 0, 'api_url');
    }

    $activeBotQuery = $adb->pquery("SELECT * FROM ctwhatsapp_bots WHERE deleted = 1 AND assignuserid = ?", array($userid));
    $whatsappNumber = $adb->query_result($activeBotQuery, 0, 'scanwhatsapp_number');

    $configData = CTWhatsAppBusiness_Record_Model::getWhatsAppDetailWithMobileNo($whatsappNumber);
    $apiUrl = $configData['api_url'];
    $auth_token = $configData['auth_token'];

    $filenameExplode = explode('/', $responseimg);
    $newFilename = urlencode($filenameExplode[6]);
    $newFilename = str_replace('+','%20',$newFilename);
    $newFilename = str_replace('_','%5F',$newFilename);
    $newFilename = str_replace('.','%2E',$newFilename);
    $newFilename = str_replace('-','%2D',$newFilename);

    $newImageFileName = $filenameExplode[0].'/'.$filenameExplode[1].'/'.$filenameExplode[2].'/'.$filenameExplode[3].'/'.$filenameExplode[4].'/'.$filenameExplode[5].'/'.$newFilename;

    if($nextQuestionBody != ""){
        $url = $api_url.'/sendfileurl';
        $postfields = array(
            'number' => $mobileno,
            'url' => $site_URL.'/'.$newImageFileName,
            'filetype' => 'imageMessage',
            'caption' => html_entity_decode(htmlspecialchars_decode($nextQuestionBody, ENT_QUOTES))
        );

        $val = CTWhatsAppBusiness_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
        $getnumberImportant = CTWhatsAppBusiness_Record_Model::getWhatsappNumberImportant($mobileno);
        if($val){
            $currentusername = $current_user->first_name.' '.$current_user->last_name;
            $relatedToData = CTWhatsAppBusiness_Record_Model::getRelatedToId(substr($mobileno,-9));         
            $relatedTo = $relatedToData['relatedTo'];
            $displayname = $relatedToData['displayname'];
            $date_var = date("Y-m-d H:i:s");

            $recordModelResponder = Vtiger_Record_Model::getCleanInstance('CTWhatsAppBusiness');
            $recordModelResponder->set('whatsapp_sendername', $currentusername);
            $recordModelResponder->set('msgid', $val['key']['id']);
            $recordModelResponder->set('whatsapp_withccode', $mobileno);
            $recordModelResponder->set('message_type', 'Send');
            $recordModelResponder->set('message_body', $nextQuestionBody);
            //displayname changes
            if($displayname){
                $recordModelResponder->set('whatsapp_displayname', $displayname);
            }
            //displayname changes
            $recordModelResponder->set('whatsapp_contactid', $relatedTo);
            $recordModelResponder->set('whatsapp_unreadread', 'Unread');
            $recordModelResponder->set('whatsapp_fromno', $whatsappNumber);
            $recordModelResponder->set('your_number', $whatsappNumber);
            $recordModelResponder->set('whatsapp_important', $getnumberImportant);
            $recordModelResponder->set('whatsapp_botid', $currentBotId);
            $recordModelResponder->set('whatsapp_datetime', $adb->formatDate($date_var, true));
            $recordModelResponder->save();
        }
    }
}

function convertRegularMode($customerMobileNo){
    global $adb;
    $adb->pquery("UPDATE whatsappbot_pre_que SET manualtransfer = '1' WHERE prequemobilenumber = ?", array($customerMobileNo));
}


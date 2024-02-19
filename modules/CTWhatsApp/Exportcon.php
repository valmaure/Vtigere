<?php
chdir(dirname(__FILE__). '/../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php'; 

global $current_user, $adb, $root_directory, $log, $site_URL;
$whatsappuseid = $_REQUEST['whatsappuseid'];
if($whatsappuseid == ''){
	$whatsappuseid = $_REQUEST['currentUserID'];
}
$groupMember = $_REQUEST['groupMember'];
$groupid = $_REQUEST['groupid'];
$configurationData = Settings_CTWhatsApp_Record_Model::getUserConfigurationAllDataWithId($whatsappuseid);
$apiUrl = $configurationData['api_url'];
$auth_token = $configurationData['authtoken'];
$whatsappNo =$configurationData['whatsappno'];

$contents = 'Name,Number';
$contents.="\n";
header("Content-Disposition:attachment;filename=contacts.csv");
header("Content-Type:application/csv; charset=UTF-8");
header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header("Cache-Control: post-check=0, pre-check=0", false );
$data_array = array();

if($groupMember == 1){
	$url = $apiUrl.'/groupinfo';
	$postfields = array(
		'jid' => $groupid
	);

	$response = CTWhatsApp_WhatsappChat_View::callCURL($url, $postfields, $auth_token);
	for($i = 0;$i<count($response['metadata']['participants']);$i++){
		$jid = explode('@', $response['metadata']['participants'][$i]['id']);
		$contents.= $data_array['name'] = $response['metadata']['participants'][$i]['name'].",";
		//$contents.= $data_array['name'] = '-'.",";
		$contents.= $data_array['number'] = $jid[0]."\n";
	}
}else{
	$url = $apiUrl.'/contactlist';
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
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
		CURLOPT_HTTPHEADER => array(
	    	'Authorization: '.$auth_token,
	    	'Content-Type: application/json'
	  	),
	));
	$result = curl_exec($curl);
	$response = json_decode($result, true);
	for($i = 0;$i<count($response);$i++){
		$jid = explode('@', $response[$i]['id']);
		$contents.= $data_array['notify'] = $response[$i]['notify'].",";
		$contents.= $data_array['number'] = $jid[0]."\n";
	}
}
echo $contents;

function encrypt_decrypt( $string, $action = 'e' ) {
	// you may change these values to your own
	$secret_key = 'variance12*';
	$secret_iv = 'variance12*';

	$output = false;
	$encrypt_method = "AES-256-CBC";
	$key = hash( 'sha256', $secret_key );
	$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if( $action == 'e' ) {
	$output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
	}
	else if( $action == 'd' ){
	$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}
?>

<?php  
error_reporting(0);
require_once('include/utils/utils.php');
include_once 'includes/main/WebUI.php'; 
global $adb, $current_user,$site_URL;

if (isset($_FILES)) {
	$name="responseImg";
	$filename = $_FILES[$name]['name'];
		$currenUserID = $current_user->id;
 		if($filename){
			$whatsappFolderPath = "modules/CTWhatsApp/CTWhatsAppStorage/";
			$year  = date('Y');
			$month = date('F');
			$day   = date('j');
			$week  = '';
			if (!is_dir($whatsappFolderPath)) {
				//create new folder
				mkdir($whatsappFolderPath);
				chmod($whatsappFolderPath, 0777);
			}

			if (!is_dir($whatsappFolderPath . $year)) {
				//create new folder
				mkdir($whatsappFolderPath . $year);
				chmod($whatsappFolderPath . $year, 0777);
			}

			if (!is_dir($whatsappFolderPath . $year . "/" . $month)) {
				//create new folder
				mkdir($whatsappFolderPath . "$year/$month/");
				chmod($whatsappFolderPath . "$year/$month/", 0777);
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
				
			if (!is_dir($whatsappFolderPath . $year . "/" . $month . "/" . $week)) {
					//create new folder
					mkdir($whatsappFolderPath . "$year/$month/$week/");
					chmod($whatsappFolderPath . "$year/$month/$week/", 0777);
			}
			$time=time();
			$target_file = $whatsappFolderPath.$year.'/'.$month.'/'.$week.'/';
			 
			 $filemove = move_uploaded_file($_FILES[$name]['tmp_name'], $target_file.$time.$filename);
			echo  $newfileURL = $site_URL.$whatsappFolderPath . "$year/$month/$week/".$time.$filename;

		}
 }

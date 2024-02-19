<?php 

$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Module.php');

$whatsappmodule = Vtiger_Module::getInstance('CTWhatsApp');
$whatsapplabel = 'WhatsApp';

$accountmodule = Vtiger_Module::getInstance('Accounts');
$accountmodule->setrelatedlist($whatsappmodule, $whatsapplabel, array(), 'get_dependents_list');

$contactsmodule = Vtiger_Module::getInstance('Contacts');
$contactsmodule->setrelatedlist($whatsappmodule, $whatsapplabel, array(), 'get_dependents_list');

$leadsmodule = Vtiger_Module::getInstance('Leads');
$leadsmodule->setrelatedlist($whatsappmodule, $whatsapplabel, array(), 'get_dependents_list');

$vendorsmodule = Vtiger_Module::getInstance('Vendors');
$vendorsmodule->setrelatedlist($whatsappmodule, $whatsapplabel, array(), 'get_dependents_list');


?>


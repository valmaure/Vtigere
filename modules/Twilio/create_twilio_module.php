<?php
require_once 'include/Webservices/init_webservice.php';

// Fonction pour créer le module Twilio Messages
function createTwilioMessagesModule()
{
    // Vérifier si le module existe déjà
    if (Vtiger_Module::getInstance('TwilioMessages')) {
        return; // Le module existe déjà, ne pas créer à nouveau
    }

    // Créer une instance du gestionnaire de module
    $moduleInstance = new Vtiger_Module();
    $moduleInstance->parent_name = 'Vtiger';
    $moduleInstance->name = 'TwilioMessages';
    $moduleInstance->save();

    // Ajouter des champs personnalisés au module
    addCustomField($moduleInstance, 'twilio_message_body', 'Message Body', '255', 'V~O', '1');
    addCustomField($moduleInstance, 'twilio_phone_number', 'Phone Number', '20', 'V~O', '2');
    // Ajoutez d'autres champs personnalisés au besoin

    // Activer le module pour tous les profils
    enableModuleForAllProfiles($moduleInstance->id);

    echo 'Module "Twilio Messages" créé avec succès.';
}

// Fonction pour ajouter un champ personnalisé
function addCustomField($moduleInstance, $fieldName, $fieldLabel, $fieldLength, $fieldDataType, $sequence)
{
    $fieldInstance = new Vtiger_Field();
    $fieldInstance->name = $fieldName;
    $fieldInstance->label = $fieldLabel;
    $fieldInstance->table = $moduleInstance->basetable;
    $fieldInstance->columntype = "VARCHAR($fieldLength)";
    $fieldInstance->typeofdata = $fieldDataType;
    $fieldInstance->sequence = $sequence;
    $fieldInstance->save($moduleInstance->id);
}

// Fonction pour activer le module pour tous les profils
function enableModuleForAllProfiles($moduleId)
{
    $profiles = Vtiger_Profile::getAll();
    foreach ($profiles as $profile) {
        $profile->setRelatedList(array('TwilioMessages'), 'ADD');
    }
}

// Appeler la fonction pour créer le module
createTwilioMessagesModule();
?>

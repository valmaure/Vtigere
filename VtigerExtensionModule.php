<?php

include_once 'modules/Vtiger/Zend/Json.php';

class VtigerExtensionModule {

    function vtlib_handler($moduleName, $eventType) {
        if ($eventType == 'beforeSave') {
            $this->handleBeforeSave();
        }
    }

    function handleBeforeSave($entityData) {
        // Assurez-vous que l'ID de l'enregistrement est présent dans les données
        if (isset($entityData['id'])) {
            $recordId = $entityData['id'];
    
            // Utilisez l'API de Vtiger CRM pour obtenir les données de l'enregistrement mis à jour
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordData = $recordModel->getData();
    
            // Code pour appeler votre serveur Express.js
            $url = 'http://localhost:3000/webhook';
            $data = array(
                'recordId' => $recordId,
                'recordData' => $recordData
            );
    
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ),
            );
    
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
    
            if ($result === FALSE) {
                // Gérez les erreurs
                echo "Erreur lors de l'appel du serveur Express.js";
            } else {
                // Gérez la réponse si nécessaire
                echo $result;
            }
        }
    }
    
    // Appel de la fonction avec les données de l'entité
    $entityData = // Logique pour obtenir les données de l'entité avant la sauvegarde
    handleBeforeSave($entityData);
    
}

?>

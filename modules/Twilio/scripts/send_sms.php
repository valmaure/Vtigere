<?php
// Charger le service Twilio
require_once 'modules/Twilio/TwilioService.php';

// Configurer les informations d'identification Twilio
$accountSid = 'ACa94aad198da847162cc490452aeba527';
$authToken = 'a0645b865843c47886d62a8291946f8f';
$twilioNumber = '+14807250732';

// Instancier le service Twilio
$twilioService = new TwilioService($accountSid, $authToken, $twilioNumber);

// Utiliser le service pour envoyer un SMS
$to = '+242069343152';
$messageBody = 'Bonjour et bienvenue chez top center';

// Appeler la méthode sendMessage du service Twilio
if ($twilioService->sendMessage($to, $messageBody)) {
    echo 'Message envoyé avec succès.';
} else {
    echo 'Échec de l\'envoi du message.';
}
?>

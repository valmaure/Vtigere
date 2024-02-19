const express = require('express');
const bodyParser = require('body-parser');
const twilio = require('twilio');

const app = express();
const port = process.env.PORT || 3000;

// Vos informations Twilio
const accountSid = 'YOUR_TWILIO_ACCOUNT_SID';
const authToken = 'YOUR_TWILIO_AUTH_TOKEN';
const twilioPhoneNumber = 'YOUR_TWILIO_PHONE_NUMBER';

const client = twilio(accountSid, authToken);

app.use(bodyParser.urlencoded({ extended: true }));

// Endpoint pour recevoir les messages WhatsApp
app.post('/webhook', (req, res) => {
  const messageBody = req.body.Body;
  const senderNumber = req.body.From;

  // Traitement du message (vous pouvez le personnaliser selon vos besoins)
  const responseMessage = `Vous avez dit : ${messageBody}`;

  // Envoyer une réponse à l'utilisateur
  client.messages.create({
    body: responseMessage,
    from: `whatsapp:${twilioPhoneNumber}`,
    to: `whatsapp:${senderNumber}`
  })
  .then(message => console.log(`Réponse envoyée avec le SID : ${message.sid}`))
  .catch(error => console.error(`Erreur lors de l'envoi de la réponse : ${error.message}`));

  res.sendStatus(200);
});

app.listen(port, () => {
  console.log(`Serveur en cours d'exécution sur le port ${port}`);
});

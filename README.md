# KandMailer PHP

![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-Pest-orange)

Client PHP léger pour KandMailer

## Installation

```bash
composer require supportkbz/kandmailer
```

## Usage
### Config
```php

use KandMailer\MailerClient;

$client = new MailerClient('your_api_key', 'https://exemple.com');

```

### Tips

#### Send - Envoi de messages

Utilisez des objets `Recipient` pour une meilleure flexibilité et type-safety :

```php
use KandMailer\Models\Recipient;

// Envoi simple
$client->template('welcome_email')
       ->sendTo(new Recipient(
           email: 'john@example.com',
           firstName: 'John',
           lastName: 'Doe',
           options: ['lang' => 'en', 'priority' => 'high']
       ));

// Envoi multiple avec des options différentes pour chaque destinataire
$recipients = [
    new Recipient(
        email: 'john@example.com',
        firstName: 'John',
        options: ['lang' => 'en', 'crm' => '111']
    ),
    new Recipient(
        email: 'jane@example.com',
        firstName: 'Jane',
        options: ['lang' => 'fr', 'crm' => '222']
    ),
    new Recipient(
        phone: '+33612345678',
        firstName: 'Bob',
        options: ['lang' => 'en', 'crm' => '333']
    ),
];

$client->template('welcome_email')
       ->sendToMultiple($recipients);
```

#### Send transactionnel — content, from, user_email, sleep

```php
// Single : override de contenu + From (Letsignit) + trace déclencheur
$client->template('pli-huissier-reexpedition')
       ->email('contact@example.com')
       ->firstName('Jean')
       ->content('Corps override {{firstName}} {{signature}}')
       ->from('evreux@kandbaz.com')
       ->userEmail('prout@kandbaz.com')
       ->sendSingle();

// List : pacing via header X-Kandmail-Sleep (ms entre deux envois)
$client->template('pli-huissier-reexpedition')
       ->email(['a@example.com', 'b@example.com'])
       ->from('evreux@kandbaz.com')
       ->userEmail('prout@kandbaz.com')
       ->sleep(2000) // 2 s entre chaque job côté API
       ->sendMultiple();
```

- `from` : adresse SMTP From + signature Letsignit (allowlist API)
- `userEmail` : qui a déclenché l’envoi (`messages.user_email`) — ne sert pas à la signature
- `content` : override du template si `editable = true`
- `sleep` : uniquement avec `sendMultiple()` / `sendToMultiple()`

#### Add - Approche Classique

```php
// Set value
$client->scenario('welcome_scenario');
$client->firstName('John');
$client->lastName('Doe');
$client->email('john@example.com');
$client->phone('+33612345678');
$client->accountId('12345');

// Options, remove, exists
$client->options(['lang' => 'en']);
$client->setRemove(['old_tag']);
$client->setExists(['check_tag']);

// Call
$client->add();

// Chaining
$client->scenario('welcome')
       ->firstName('John')
       ->lastName('Doe')
       ->email('john@example.com')
       ->phone('+33612345678')
       ->add();
```

#### Remove - Approche Classique

```php
// Set scenario (required) and email
$client->scenario('welcome_scenario');
$client->email('john@example.com');
$client->remove();

// Set scenario (required) and phone
$client->scenario('welcome_scenario');
$client->phone('+33612345678');
$client->remove();

// Chaining
$client->scenario('welcome_scenario')->email('john@example.com')->remove();
```

#### Remove - Approche Orientée Objet

```php
// Suppression d'un contact
$client->removeFrom(new Recipient(
    email: 'john@example.com',
    scenario: 'welcome_sequence'
));
```

#### Méthodes avancées

##### Reset - Réutiliser le client
```php
// Envoyer à plusieurs destinataires avec la même instance
$client->template('newsletter')
       ->email('john@example.com')
       ->options(['lang' => 'en'])
       ->sendSingle();

// Réinitialiser pour un nouvel envoi
$client->reset();

$client->template('newsletter')
       ->email('jane@example.com')
       ->options(['lang' => 'fr'])
       ->sendSingle();
```

### Exigences
- PHP 8.3+
- Extension `curl`

### Licence
MIT. Voir le fichier `LICENSE`.
# KandMailer PHP

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
#### Send
```php
// Set single
$client->toEmail('john@example.com');
$client->toPhone('+33612345678');
$client->option('lang', 'en');

// Set multiple
$client->toEmail(['john@example.com', 'jane@example.com']);
$client->toPhone(['+33612345678', '+33612345679']);
$client->options(['lang' => 'en', 'priority' => 'high']);

// Call
$client->send();

// Chaining
$client->toEmail('john@example.com')->options(['lang' => 'en', 'priority' => 'high'])->send();
```

#### Add
```php
// Set value
$client->scenario('welcome_scenario');
$client->firstName('John');
$client->lastName('Doe');
$client->toEmail('john@example.com');
$client->toPhone('+33612345678');
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
       ->toEmail('john@example.com')
       ->toPhone('+33612345678')
       ->add();
```

#### Remove
```php
// Set scenario (required) and email
$client->scenario('welcome_scenario');
$client->toEmail('john@example.com');
$client->remove();

// Set scenario (required) and phone
$client->scenario('welcome_scenario');
$client->toPhone('+33612345678');
$client->remove();

// Chaining
$client->scenario('welcome_scenario')->toEmail('john@example.com')->remove();
```

### Exigences
- PHP 8.4+
- Extension `curl`

### Licence
MIT. Voir le fichier `LICENSE`.
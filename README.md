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
$client->email('john@example.com');
$client->phone('+33612345678');
$client->option('lang', 'en');

// Set multiple
$client->email(['john@example.com', 'jane@example.com']);
$client->email(['+33612345678', '+33612345679']);
$client->firstName(['John', 'Jane']);
$client->lastName(['Doe', 'White']);
$client->options(['lang' => 'en', 'priority' => 'high']);

// Call
$client->send();

// Chaining
$client->email('john@example.com')->options(['lang' => 'en', 'priority' => 'high'])->send();
```

#### Add
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

#### Remove
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

### Exigences
- PHP 8.4+
- Extension `curl`

### Licence
MIT. Voir le fichier `LICENSE`.
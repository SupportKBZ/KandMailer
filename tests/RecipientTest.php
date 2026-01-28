<?php

use KandMailer\Models\Recipient;
use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Recipient Model', function () {
    it('Create a recipient with email only', function () {
        $recipient = new Recipient(email: 'john@example.com');
        
        expect($recipient->email)->toBe('john@example.com');
        expect($recipient->phone)->toBeNull();
        expect($recipient->firstName)->toBeNull();
        expect($recipient->lastName)->toBeNull();
        expect($recipient->options)->toBe([]);
    });

    it('Create a recipient with all fields', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            phone: '+33628361721',
            firstName: 'John',
            lastName: 'Doe',
            options: ['crm' => '123456', 'customField' => 'value'],
            scenario: 'welcome',
            accountId: 'acc-123'
        );
        
        expect($recipient->email)->toBe('john@example.com');
        expect($recipient->phone)->toBe('+33628361721');
        expect($recipient->firstName)->toBe('John');
        expect($recipient->lastName)->toBe('Doe');
        expect($recipient->options)->toBe(['crm' => '123456', 'customField' => 'value']);
        expect($recipient->scenario)->toBe('welcome');
        expect($recipient->accountId)->toBe('acc-123');
    });

    it('Validate email format', function () {
        expect(fn() => new Recipient(email: 'invalid-email'))
            ->toThrow(InvalidArgumentException::class, 'Email invalide: invalid-email');
    });

    it('Validate phone format', function () {
        expect(fn() => new Recipient(phone: '123'))
            ->toThrow(InvalidArgumentException::class, 'Numéro de téléphone invalide: 123');
    });

    it('Require at least email or phone', function () {
        expect(fn() => new Recipient())
            ->toThrow(InvalidArgumentException::class, 'Au moins un email ou un téléphone doit être fourni.');
    });

    it('Create recipient from array', function () {
        $recipient = Recipient::fromArray([
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'options' => ['crm' => '123']
        ]);
        
        expect($recipient->email)->toBe('john@example.com');
        expect($recipient->firstName)->toBe('John');
        expect($recipient->lastName)->toBe('Doe');
        expect($recipient->options)->toBe(['crm' => '123']);
    });

    it('Convert recipient to array', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            firstName: 'John',
            options: ['crm' => '123']
        );
        
        $array = $recipient->toArray();
        
        expect($array)->toBe([
            'email' => 'john@example.com',
            'firstName' => 'John',
            'options' => ['crm' => '123']
        ]);
    });
});

describe('Send with Recipient', function () {
    it('Send to a single recipient', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            options: ['crm' => '123456']
        );

        $this->mailer->template('welcome');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendTo($recipient);

        expect($result)->toBeString();
        $result = json_decode($result, true);
        expect($result['status'])->toBe('success');

        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/single');
        expect($payload['template'])->toBe('welcome');
        expect($payload['email'])->toBe('john@example.com');
        expect($payload['firstName'])->toBe('John');
        expect($payload['lastName'])->toBe('Doe');
        expect($payload['options']['crm'])->toBe('123456');
    });

    it('Send to multiple recipients with different options', function () {
        $recipients = [
            new Recipient(
                email: 'john@example.com',
                firstName: 'John',
                lastName: 'Doe',
                options: ['crm' => '111', 'plan' => 'premium']
            ),
            new Recipient(
                email: 'jane@example.com',
                firstName: 'Jane',
                lastName: 'Smith',
                options: ['crm' => '222', 'plan' => 'basic']
            ),
            new Recipient(
                email: 'bob@example.com',
                phone: '+33628361721',
                firstName: 'Bob',
                options: ['crm' => '333']
            ),
        ];

        $this->mailer->template('welcome');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendToMultiple($recipients);

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        expect($payload)->toHaveCount(3);

        expect($payload[0]['email'])->toBe('john@example.com');
        expect($payload[0]['firstName'])->toBe('John');
        expect($payload[0]['lastName'])->toBe('Doe');
        expect($payload[0]['options']['crm'])->toBe('111');
        expect($payload[0]['options']['plan'])->toBe('premium');

        expect($payload[1]['email'])->toBe('jane@example.com');
        expect($payload[1]['firstName'])->toBe('Jane');
        expect($payload[1]['lastName'])->toBe('Smith');
        expect($payload[1]['options']['crm'])->toBe('222');
        expect($payload[1]['options']['plan'])->toBe('basic');

        expect($payload[2]['email'])->toBe('bob@example.com');
        expect($payload[2]['phone'])->toBe('+33628361721');
        expect($payload[2]['firstName'])->toBe('Bob');
        expect($payload[2]['options']['crm'])->toBe('333');
    });

    it('Merge global options with recipient options', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            options: ['crm' => '123', 'plan' => 'premium']
        );

        $this->mailer
            ->template('welcome')
            ->option('globalKey', 'globalValue')
            ->option('crm', 'will-be-overridden');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['options']['globalKey'])->toBe('globalValue');
        expect($payload['options']['crm'])->toBe('123');
        expect($payload['options']['plan'])->toBe('premium');
    });

    it('Use recipient scenario over client scenario', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'recipient-scenario'
        );

        $this->mailer
            ->template('welcome')
            ->scenario('client-scenario');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['scenario'])->toBe('recipient-scenario');
    });

    it('Use client scenario if recipient has none', function () {
        $recipient = new Recipient(email: 'john@example.com');

        $this->mailer
            ->template('welcome')
            ->scenario('client-scenario');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['scenario'])->toBe('client-scenario');
    });

    it('Send with phone only recipient', function () {
        $recipient = new Recipient(
            phone: '+33628361721',
            firstName: 'John',
            options: ['smsKey' => 'value']
        );

        $this->mailer->template('sms');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['phone'])->toBe('+33628361721');
        expect($payload['firstName'])->toBe('John');
        expect($payload['options']['smsKey'])->toBe('value');
        expect($payload)->not->toHaveKey('email');
    });

    it('Send to multiple recipients with mixed email and phone', function () {
        $recipients = [
            new Recipient(
                email: 'john@example.com',
                options: ['type' => 'email']
            ),
            new Recipient(
                phone: '+33628361721',
                options: ['type' => 'sms']
            ),
            new Recipient(
                email: 'jane@example.com',
                phone: '+33628361722',
                options: ['type' => 'both']
            ),
        ];

        $this->mailer->template('notification');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendToMultiple($recipients);

        $payload = $mockHttp->getLastPayload();

        expect($payload)->toHaveCount(3);

        expect($payload[0]['email'])->toBe('john@example.com');
        expect($payload[0])->not->toHaveKey('phone');
        expect($payload[0]['options']['type'])->toBe('email');

        expect($payload[1]['phone'])->toBe('+33628361721');
        expect($payload[1])->not->toHaveKey('email');
        expect($payload[1]['options']['type'])->toBe('sms');

        expect($payload[2]['email'])->toBe('jane@example.com');
        expect($payload[2]['phone'])->toBe('+33628361722');
        expect($payload[2]['options']['type'])->toBe('both');
    });

    it('Use recipient createdAt if provided', function () {
        $date = new \DateTime('2024-01-15 10:30:00');
        $recipient = new Recipient(
            email: 'john@example.com',
            createdAt: $date
        );

        $this->mailer->template('welcome');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['created_at'])->toBe($date->format(\DateTimeInterface::ATOM));
    });
});

describe('Add Contact with Recipient', function () {
    test('it Add a contact using addTo', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            scenario: 'welcome',
            options: ['lang' => 'en']
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->addTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['email'])->toBe('john@example.com');
        expect($payload['firstName'])->toBe('John');
        expect($payload['lastName'])->toBe('Doe');
        expect($payload['scenario'])->toBe('welcome');
        expect($payload['options'])->toBe(['lang' => 'en']);
    });

    test('it Merge global options with recipient options in addTo', function () {
        $this->mailer->options(['source' => 'website', 'priority' => 'low']);

        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'welcome',
            options: ['priority' => 'high', 'campaign' => 'summer']
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->addTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['options']['source'])->toBe('website');
        expect($payload['options']['priority'])->toBe('high');
        expect($payload['options']['campaign'])->toBe('summer');
    });

    test('it Add a contact with accountId', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            firstName: 'John',
            scenario: 'welcome',
            accountId: 'acc-67890'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->addTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['email'])->toBe('john@example.com');
        expect($payload['firstName'])->toBe('John');
        expect($payload['scenario'])->toBe('welcome');
        expect($payload['account_id'])->toBe('acc-67890');
    });

    test('it Use recipient accountId over client accountId in addTo', function () {
        $this->mailer->accountId('client-account-id');

        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'welcome',
            accountId: 'recipient-account-id'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->addTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['account_id'])->toBe('recipient-account-id');
    });

    test('it Use client accountId if recipient has none in addTo', function () {
        $this->mailer->accountId('client-account-id')->scenario('welcome');

        $recipient = new Recipient(
            email: 'john@example.com',
            firstName: 'John'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->addTo($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['account_id'])->toBe('client-account-id');
    });
});

describe('Remove Contact with Recipient', function () {
    test('it Remove a contact using removeFrom', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'welcome'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->removeFrom($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['email'])->toBe('john@example.com');
        expect($payload['scenario'])->toBe('welcome');
    });

    test('it Use client scenario if recipient has none in removeFrom', function () {
        $this->mailer->scenario('default_scenario');

        $recipient = new Recipient(
            email: 'john@example.com'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->removeFrom($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['scenario'])->toBe('default_scenario');
    });

    test('it Remove a contact with accountId', function () {
        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'welcome',
            accountId: 'acc-12345'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->removeFrom($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['email'])->toBe('john@example.com');
        expect($payload['scenario'])->toBe('welcome');
        expect($payload['account_id'])->toBe('acc-12345');
    });

    test('it Use recipient accountId over client accountId', function () {
        $this->mailer->accountId('client-account-id');

        $recipient = new Recipient(
            email: 'john@example.com',
            scenario: 'welcome',
            accountId: 'recipient-account-id'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->removeFrom($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['account_id'])->toBe('recipient-account-id');
    });

    test('it Use client accountId if recipient has none', function () {
        $this->mailer->accountId('client-account-id')->scenario('welcome');

        $recipient = new Recipient(
            email: 'john@example.com'
        );

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->removeFrom($recipient);

        $payload = $mockHttp->getLastPayload();

        expect($payload['account_id'])->toBe('client-account-id');
    });
});

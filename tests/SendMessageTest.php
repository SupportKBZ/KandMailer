<?php

use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Send Message', function () {
    it('Send phone invalid - client validation', function () {
        expect(fn() => $this->mailer
            ->template('sms')
            ->toPhone('invalid')
        )->toThrow(InvalidArgumentException::class, 'Numéro de téléphone invalide: invalid');
    });

    it('Send email invalid', function () {
        expect(fn() => $this->mailer
            ->template('welcome')
            ->toEmail('invalid')
        )->toThrow(InvalidArgumentException::class, 'Email invalide: invalid');
    });

    it('Send phone invalid - API validation', function () {
        $this->mailer
            ->template('sms')
            ->toPhone('12345678'); // Passe la validation client (8 chiffres) mais rejeté par l'API

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'Invalid phone format'], 422);

        expect(fn() => $this->mailer->send())->toThrow(RuntimeException::class);
        
        $payload = $mockHttp->getLastPayload();
        expect($payload['phone'])->toBe('12345678');
    });

    it('Send phone valid', function () {
        $this->mailer
            ->template('sms')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->send();
        
        $payload = $mockHttp->getLastPayload();
        expect($payload['phone'])->toBe('+33628361721');
    });

    it('Send a single email', function () {
        $this->mailer
            ->template('welcome')
            ->toEmail('john@example.com')
            ->firstName('John')
            ->lastName('Doe')
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->send();

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

    it('Send a multiple emails', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $this->mailer
            ->template('welcome')
            ->toEmail($emails)
            ->firstName('John')
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->send();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload['email'])->toBe($emails);
    });
});
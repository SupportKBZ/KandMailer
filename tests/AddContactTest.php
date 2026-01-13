<?php

use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Add Contact', function () {
    it('Add contact with all required fields', function () {
        $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toEmail('john@example.com')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success', 'contact_id' => '12345'], 200);

        $result = $this->mailer->add();

        expect($result)->toBeString();
        $result = json_decode($result, true);
        expect($result['status'])->toBe('success');

        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/contact/add');
        expect($payload['scenario'])->toBe('newsletter');
        expect($payload['firstName'])->toBe('John');
        expect($payload['lastName'])->toBe('Doe');
        expect($payload['email'])->toBe('john@example.com');
        expect($payload['phone'])->toBe('+33628361721');
    });

    it('Add contact with all fields', function () {
        $this->mailer
            ->scenario('welcome')
            ->firstName('Jane')
            ->lastName('Smith')
            ->toEmail('jane@example.com')
            ->toPhone('+33612345678')
            ->accountId('ACC-123')
            ->setRemove(['old_email', 'temp_phone'])
            ->setExists(['verified_email', 'active_subscription']);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success', 'contact_id' => '67890'], 200);

        $result = $this->mailer->add();

        expect($result)->toBeString();
        $result = json_decode($result, true);
        expect($result['status'])->toBe('success');

        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/contact/add');
        expect($payload['scenario'])->toBe('welcome');
        expect($payload['firstName'])->toBe('Jane');
        expect($payload['lastName'])->toBe('Smith');
        expect($payload['email'])->toBe('jane@example.com');
        expect($payload['phone'])->toBe('+33612345678');
        expect($payload['account_id'])->toBe('ACC-123');
        expect($payload['remove'])->toBe(['old_email', 'temp_phone']);
        expect($payload['exists'])->toBe(['verified_email', 'active_subscription']);
    });

    it('Add contact missing scenario - API error', function () {
        $this->mailer
            ->firstName('John')
            ->lastName('Doe')
            ->toEmail('john@example.com')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'The scenario field is required.'], 422);

        expect(fn() => $this->mailer->add())->toThrow(RuntimeException::class);
    });

    it('Add contact missing firstName - API error', function () {
        $this->mailer
            ->scenario('newsletter')
            ->lastName('Doe')
            ->toEmail('john@example.com')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'The firstName field is required.'], 422);

        expect(fn() => $this->mailer->add())->toThrow(RuntimeException::class);
    });

    it('Add contact missing email - API error', function () {
        $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'The email field is required.'], 422);

        expect(fn() => $this->mailer->add())->toThrow(RuntimeException::class);
    });

    it('Add contact with invalid email - client validation', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toEmail('invalid-email')
            ->toPhone('+33628361721')
        )->toThrow(InvalidArgumentException::class, 'Email invalide: invalid-email');
    });

    it('Add contact with invalid phone - client validation', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toEmail('john@example.com')
            ->toPhone('123')
        )->toThrow(InvalidArgumentException::class, 'Numéro de téléphone invalide: 123');
    });

    it('Add contact with multiple emails - should throw exception', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toEmail(['john@example.com', 'jane@example.com'])
            ->add()
        )->toThrow(
            InvalidArgumentException::class, 
            'add() ne peut être utilisé qu\'avec un seul destinataire. Utilisez email($email) ou phone($phone) avec une chaîne, pas un tableau.'
        );
    });

    it('Add contact with multiple phones - should throw exception', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->firstName('John')
            ->lastName('Doe')
            ->toPhone(['+33628361721', '+33612345678'])
            ->add()
        )->toThrow(
            InvalidArgumentException::class, 
            'add() ne peut être utilisé qu\'avec un seul destinataire. Utilisez email($email) ou phone($phone) avec une chaîne, pas un tableau.'
        );
    });
});
<?php

use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Remove Contact', function () {
    it('Remove contact missing scenario - API error', function () {
        $this->mailer
            ->toEmail('john@example.com')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'The scenario field is required.'], 422);

        expect(fn() => $this->mailer->remove())->toThrow(RuntimeException::class);
    });
    
    it('Remove contact with invalid email - client validation', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->toEmail('invalid-email')
        )->toThrow(InvalidArgumentException::class, 'Email invalide: invalid-email');
    });

    it('Add contact with invalid phone - client validation', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->toPhone('123')
        )->toThrow(InvalidArgumentException::class, 'Numéro de téléphone invalide: 123');
    });

    it('Remove contact with phone - API success', function () {
        $this->mailer
            ->scenario('newsletter')
            ->toPhone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->remove();
        expect($result)->toBeString();
        $result = json_decode($result, true);
        expect($result['status'])->toBe('success');

        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/contact/remove');
        expect($payload['scenario'])->toBe('newsletter');
        expect($payload['phone'])->toBe('+33628361721');
    });

    it('Remove contact with email - API success', function () {
        $this->mailer
            ->scenario('newsletter')
            ->toEmail('john@example.com');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->remove();
        expect($result)->toBeString();
        $result = json_decode($result, true);
        expect($result['status'])->toBe('success');

        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/contact/remove');
        expect($payload['scenario'])->toBe('newsletter');
        expect($payload['email'])->toBe('john@example.com');
    });

    it('Remove contact with multiple emails - should throw exception', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->toEmail(['john@example.com', 'jane@example.com'])
            ->remove()
        )->toThrow(
            InvalidArgumentException::class, 
            'remove() ne peut être utilisé qu\'avec un seul destinataire. Utilisez email($email) ou phone($phone) avec une chaîne, pas un tableau.'
        );
    });

    it('Remove contact with multiple phones - should throw exception', function () {
        expect(fn() => $this->mailer
            ->scenario('newsletter')
            ->toPhone(['+33628361721', '+33612345678'])
            ->remove()
        )->toThrow(
            InvalidArgumentException::class, 
            'remove() ne peut être utilisé qu\'avec un seul destinataire. Utilisez email($email) ou phone($phone) avec une chaîne, pas un tableau.'
        );
    });
});
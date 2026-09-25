<?php

use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Send Message', function () {
    it('Send phone invalid - client validation', function () {
        expect(fn() => $this->mailer
            ->template('sms')
            ->phone('invalid')
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
            ->phone('12345678'); // Passe la validation client (8 chiffres) mais rejeté par l'API

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['message' => 'Invalid phone format'], 422);

        expect(fn() => $this->mailer->sendSingle())->toThrow(RuntimeException::class);
        
        $payload = $mockHttp->getLastPayload();
        expect($payload['phone'])->toBe('12345678');
    });

    it('Send phone valid', function () {
        $this->mailer
            ->template('sms')
            ->phone('+33628361721');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendSingle();
        
        $payload = $mockHttp->getLastPayload();
        expect($payload['phone'])->toBe('+33628361721');
    });

    it('Send multiple phone valid', function () {
        $phones = ['+33628361721', '+33628361722'];
        $this->mailer
            ->template('sms')
            ->phone($phones);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendMultiple();
        
        $payload = $mockHttp->getLastPayload();
        expect($payload)->toBeArray();
        foreach ($payload as $key => $value) {
            expect($value['phone'])->toBe($phones[$key]);
        }
    });

    it('Send a single email', function () {
        $this->mailer
            ->template('welcome')
            ->email('john@example.com')
            ->firstName('John')
            ->lastName('Doe')
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendSingle();

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

    it('Send a single email with content override', function () {
        $content = "Bonjour {{firstName}},\nMessage personnalisé.";

        $this->mailer
            ->template('relance-impaye')
            ->email('john@example.com')
            ->firstName('John')
            ->content($content);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendSingle();

        $payload = $mockHttp->getLastPayload();
        expect($payload['content'])->toBe($content);
        expect($payload)->not->toHaveKey('options');
    });

    it('includes content in multiple send payload', function () {
        $content = 'Contenu partagé';

        $this->mailer
            ->template('relance-impaye')
            ->email(['a@example.com', 'b@example.com'])
            ->content($content);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendMultiple();

        $payload = $mockHttp->getLastPayload();
        expect($payload)->toBeArray();
        foreach ($payload as $item) {
            expect($item['content'])->toBe($content);
        }
    });

    it('reset clears content', function () {
        $this->mailer
            ->template('relance-impaye')
            ->content('Hello')
            ->reset();

        expect($this->mailer->getContent())->toBeNull();
    });

    it('Send a single email with user_email and from', function () {
        $this->mailer
            ->template('relance-impaye')
            ->email('client@example.com')
            ->from('evreux@kandbaz.com')
            ->userEmail('prout@kandbaz.com');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $this->mailer->sendSingle();

        $payload = $mockHttp->getLastPayload();
        expect($payload['from'])->toBe('evreux@kandbaz.com')
            ->and($payload['user_email'])->toBe('prout@kandbaz.com');
    });

    it('reset clears userEmail and from', function () {
        $this->mailer
            ->from('evreux@kandbaz.com')
            ->userEmail('prout@kandbaz.com')
            ->reset();

        expect($this->mailer->getFrom())->toBeNull()
            ->and($this->mailer->getUserEmail())->toBeNull();
    });

    it('Send a multiple emails', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $this->mailer
            ->template('welcome')
            ->email($emails)
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        foreach ($payload as $key => $value) {
            expect($value['email'])->toBe($emails[$key]);
            expect($value['options']['crm'])->toBe('123456');
        }
    });

    it('Send a multiple emails with firstName and lastName', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $firstNames = ['John', 'Jane'];
        $lastNames = ['Doe', 'Smith'];
        $this->mailer
            ->template('welcome')
            ->email($emails)
            ->firstName(['John', 'Jane'])
            ->lastName(['Doe', 'Smith'])
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        foreach ($payload as $key => $value) {
            expect($value['email'])->toBe($emails[$key]);
            expect($value['firstName'])->toBe($firstNames[$key]);
            expect($value['lastName'])->toBe($lastNames[$key]);
            expect($value['options']['crm'])->toBe('123456');
        }
    });

    it('Send a multiple emails with no provided firstName and lastName', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $firstNames = ['John', 'Jane'];
        $lastNames = ['Doe', null];
        $this->mailer
            ->template('welcome')
            ->email($emails)
            ->firstName($firstNames)
            ->lastName($lastNames)
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        foreach ($payload as $key => $value) {
            expect($value['email'])->toBe($emails[$key]);
            expect($value['firstName'])->toBe($firstNames[$key]);
            if ($key == 1) {
                expect($value)->not->toHaveKey('lastName');
            } else {
                expect($value['lastName'])->toBe($lastNames[$key]);
            }   
            expect($value['options']['crm'])->toBe('123456');
        }
    });

    it('Send a multiple emails with email and phone is array', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $phones = ['+33628361721', '+33628361722'];
        $this->mailer
            ->template('welcome')
            ->email($emails)
            ->phone($phones)
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        foreach ($payload as $key => $value) {
            expect($value['email'])->toBe($emails[$key]);
            expect($value['phone'])->toBe($phones[$key]);
            expect($value['options']['crm'])->toBe('123456');
        }
    });

    it('Send a multiple emails with email and phone is array and one phone miss', function () {
        $emails = ['john@example.com', 'jane@example.com'];
        $phones = ['+33628361721'];
        $this->mailer
            ->template('welcome')
            ->email($emails)
            ->phone($phones)
            ->option('crm', '123456');

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        foreach ($payload as $key => $value) {
            expect($value['email'])->toBe($emails[$key]);
            if ($key == 1) {
                expect($value)->not->toHaveKey('phone');
            } else {
                expect($value['phone'])->toBe($phones[$key]);
            }   
            expect($value['options']['crm'])->toBe('123456');
        }
    });

    it('Send multiple emails with multiOptions', function () {
        $emails = ['1@one.com', '2@two.com'];
        $this->mailer
            ->template('kbis-0')
            ->email($emails)
            ->multiOptions([
                ['opt' => '1'],
                ['opt' => '2']
            ]);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        expect($payload)->toHaveCount(2);
        
        expect($payload[0]['template'])->toBe('kbis-0');
        expect($payload[0]['email'])->toBe('1@one.com');
        expect($payload[0]['options']['opt'])->toBe('1');
        
        expect($payload[1]['template'])->toBe('kbis-0');
        expect($payload[1]['email'])->toBe('2@two.com');
        expect($payload[1]['options']['opt'])->toBe('2');
    });

    it('Send multiple emails with multiOptions and one option is missing', function () {
        $emails = ['1@one.com', '2@two.com', '3@three.com'];
        $this->mailer
            ->template('kbis-0')
            ->email($emails)
            ->multiOptions([
                ['opt' => '1'],
                ['opt' => '2']
            ]);

        $mockHttp = getMockHttp($this->mailer);
        $mockHttp->setResponse(['status' => 'success'], 200);

        $result = $this->mailer->sendMultiple();

        expect($result)->toBeString();
        
        $url = $mockHttp->getLastUrl();
        $payload = $mockHttp->getLastPayload();

        expect($url)->toContain('/send/list');
        expect($payload)->toBeArray();
        expect($payload)->toHaveCount(3);

        expect($payload[0]['options']['opt'])->toBe('1');
        expect($payload[1]['options']['opt'])->toBe('2');
        expect($payload[2])->not->toHaveKey('options');
    });

    it('Throw error when using multiOptions with sendSingle', function () {
        expect(fn() => $this->mailer
            ->template('welcome')
            ->email('test@example.com')
            ->multiOptions([['opt' => '1']])
            ->sendSingle()
        )->toThrow(
            InvalidArgumentException::class,
            'multiOptions() ne peut être utilisé qu\'avec sendMultiple()'
        );
    });
});
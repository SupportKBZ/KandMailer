<?php

declare(strict_types=1);

namespace KandMailer\Models;

use KandMailer\MailerClient;

/**
 * Data Transfer Object for recipient information.
 * Encapsulates contact details to simplify method signatures.
 */
class RecipientData
{
    public function __construct(
        public readonly string|array|null $email = null,
        public readonly string|array|null $phone = null,
        public readonly string|array|null $firstName = null,
        public readonly string|array|null $lastName = null,
        public readonly string|array|null $scenario = null,
        public readonly string|array|null $accountId = null,
        public readonly \DateTimeInterface|null $createdAt = null,
        public readonly array|null $options = null,
    ) {}
    
    /**
     * Create a RecipientData instance from an array or MailerClient.
     *
     * @param array<string, mixed>|MailerClient $data
     */
    public static function from(array|MailerClient $data): self
    {
        if ($data instanceof MailerClient) {
            $data = [
                'email' => $data->getEmail(),
                'phone' => $data->getPhone(),
                'firstName' => $data->getFirstName(),
                'lastName' => $data->getLastName(),
                'scenario' => $data->getScenario(),
                'accountId' => $data->getAccountId(),
            ];
        }
        
        return new self(
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            scenario: $data['scenario'] ?? null,
            accountId: $data['accountId'] ?? null
        );
    }
}

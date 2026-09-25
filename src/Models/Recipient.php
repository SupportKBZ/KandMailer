<?php

declare(strict_types=1);

namespace KandMailer\Models;

use InvalidArgumentException;

class Recipient
{
    /**
     * Create a new Recipient instance.
     *
     * @param string|null $email Email address
     * @param string|null $phone Phone number
     * @param string|null $firstName First name
     * @param string|null $lastName Last name
     * @param array<string,mixed> $options Custom options for this recipient
     * @param string|null $scenario Scenario identifier
     * @param string|null $accountId Account identifier
     * @param \DateTimeInterface|null $createdAt Creation date
     * @param string|null $content Content override for editable templates
     * @param string|null $from SMTP From address (Letsignit)
     * @param string|null $userEmail Email of the user who triggered the send
     *
     * @throws InvalidArgumentException If email or phone format is invalid
     */
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly array $options = [],
        public readonly ?string $scenario = null,
        public readonly ?string $accountId = null,
        public readonly ?\DateTimeInterface $createdAt = null,
        public readonly ?string $content = null,
        public readonly ?string $from = null,
        public readonly ?string $userEmail = null,
    ) {
        $this->validate();
    }

    /**
     * Validate recipient data.
     *
     * @throws InvalidArgumentException If validation fails
     */
    private function validate(): void
    {
        if ($this->email === null && $this->phone === null) {
            throw new InvalidArgumentException(
                'Au moins un email ou un téléphone doit être fourni.'
            );
        }

        if ($this->email !== null) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Email invalide: {$this->email}");
            }
        }

        if ($this->phone !== null) {
            $digits = preg_replace('/\D/', '', $this->phone);
            if (strlen($digits) < 8) {
                throw new InvalidArgumentException("Numéro de téléphone invalide: {$this->phone}");
            }
        }

        if ($this->from !== null) {
            if (!filter_var($this->from, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Email invalide: {$this->from}");
            }
        }

        if ($this->userEmail !== null) {
            if (!filter_var($this->userEmail, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Email invalide: {$this->userEmail}");
            }
        }
    }

    /**
     * Create a Recipient from an array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            options: $data['options'] ?? [],
            scenario: $data['scenario'] ?? null,
            accountId: $data['accountId'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            content: $data['content'] ?? null,
            from: $data['from'] ?? null,
            userEmail: $data['userEmail'] ?? $data['user_email'] ?? null,
        );
    }

    /**
     * Convert recipient to array format.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        if ($this->firstName !== null) {
            $data['firstName'] = $this->firstName;
        }

        if ($this->lastName !== null) {
            $data['lastName'] = $this->lastName;
        }

        if (!empty($this->options)) {
            $data['options'] = $this->options;
        }

        if ($this->scenario !== null) {
            $data['scenario'] = $this->scenario;
        }

        if ($this->accountId !== null) {
            $data['accountId'] = $this->accountId;
        }

        if ($this->createdAt !== null) {
            $data['createdAt'] = $this->createdAt;
        }

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->from !== null) {
            $data['from'] = $this->from;
        }

        if ($this->userEmail !== null) {
            $data['userEmail'] = $this->userEmail;
        }

        return $data;
    }
}

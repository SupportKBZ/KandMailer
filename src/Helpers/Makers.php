<?php

declare(strict_types=1);

namespace KandMailer\Helpers;

use RuntimeException;

use KandMailer\MailerClient;
use KandMailer\Models\File;
use KandMailer\Models\RecipientData;
use KandMailer\Models\Recipient;

class Makers
{
    /**
     * Constructor.
     *
     * @param MailerClient $client The client.
     * @param string|null $method The method.
     * @param string|null $path The path.
     */
    public function __construct(
        private readonly MailerClient $client,
        private readonly ?string $method = null,
        private readonly ?string $path = null
    ) {}

    /**
     * Send a request for a single recipient.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function requestSingle(MailerClient $client, string $path, string $method = 'POST'): string
    {
        return (new self($client, $method, $path))->executeSingle();
    }

    /**
     * Send a request for multiple recipients.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function requestMultiple(MailerClient $client, string $path, string $method = 'POST'): string
    {
        return (new self($client, $method, $path))->executeMultiple();
    }

    /**
     * Execute request for a single recipient.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeSingle(): string
    {
        return $this->executeRequest($this->buildPayloadSingle());
    }

    /**
     * Execute request for multiple recipients.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeMultiple(): string
    {
        return $this->executeRequest($this->buildMultiplePayload());
    }

    /**
     * Execute request for a single recipient using a Recipient object.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeWithRecipient(Recipient $recipient): string
    {
        return $this->executeRequest($this->buildPayloadFromRecipient($recipient));
    }

    /**
     * Execute request for multiple recipients using Recipient objects.
     *
     * @param array<Recipient> $recipients
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public function executeWithRecipients(array $recipients): string
    {
        return $this->executeRequest($this->buildPayloadFromRecipients($recipients));
    }

    /**
     * Build payload for a single recipient.
     *
     * @return array<string,mixed>
     */
    public function buildPayloadSingle(): array
    {
        $recipient = RecipientData::from($this->client);
        
        return $this->buildBasePayload(
            $recipient->email,
            $recipient->phone,
            $recipient->firstName ?? $this->client->getFirstName(),
            $recipient->lastName ?? $this->client->getLastName(),
            null,
            null
        );
    }

    /**
     * Build payload for multiple recipients.
     *
     * @return array<int,array<string,mixed>>
     */
    public function buildMultiplePayload(): array
    {
        $emails = is_array($this->client->getEmail()) ? $this->client->getEmail() : [$this->client->getEmail()];
        $phones = is_array($this->client->getPhone()) ? $this->client->getPhone() : [$this->client->getPhone()];
        $firstNames = is_array($this->client->getFirstName()) ? $this->client->getFirstName() : [$this->client->getFirstName()];
        $lastNames = is_array($this->client->getLastName()) ? $this->client->getLastName() : [$this->client->getLastName()];
        $multiOptions = $this->client->getMultiOptions();
        
        $maxCount = max(count($emails), count($phones), count($firstNames), count($lastNames));
        
        $payloads = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $recipient = RecipientData::from([
                'email' => $emails[$i] ?? null,
                'phone' => $phones[$i] ?? null,
                'firstName' => $firstNames[$i] ?? null,
                'lastName' => $lastNames[$i] ?? null,
            ]);
            
            $options = !empty($multiOptions) ? ($multiOptions[$i] ?? null) : null;
            
            $payloads[] = $this->buildMultipleItemPayload($recipient, $options);
        }

        return $payloads;
    }

    /**
     * Build payload for a single item in a multiple send.
     *
     * @param array<string,mixed>|null $recipientOptions
     * @return array<string,mixed>
     */
    private function buildMultipleItemPayload(
        RecipientData $recipient,
        ?array $recipientOptions
    ): array
    {
        return $this->buildBasePayload(
            $recipient->email,
            $recipient->phone,
            $recipient->firstName,
            $recipient->lastName,
            $recipient->scenario,
            $recipient->accountId,
            $recipient->createdAt,
            $recipientOptions
        );
    }

    /**
     * Build payload from a Recipient object.
     *
     * @return array<string,mixed>
     */
    private function buildPayloadFromRecipient(Recipient $recipient): array
    {
        return $this->buildBasePayload(
            $recipient->email,
            $recipient->phone,
            $recipient->firstName,
            $recipient->lastName,
            $recipient->scenario,
            $recipient->accountId,
            $recipient->createdAt,
            array_merge($this->client->getOptions(), $recipient->options)
        );
    }

    /**
     * Build payload from multiple Recipient objects.
     *
     * @param array<Recipient> $recipients
     * @return array<int,array<string,mixed>>
     */
    private function buildPayloadFromRecipients(array $recipients): array
    {
        return array_map(
            fn(Recipient $recipient) => $this->buildPayloadFromRecipient($recipient),
            $recipients
        );
    }

    /**
     * Build base payload with common fields.
     *
     * @param array<string,mixed>|null $customOptions
     * @return array<string,mixed>
     */
    private function buildBasePayload(
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $scenario = null,
        ?string $accountId = null,
        ?\DateTimeInterface $createdAt = null,
        ?array $customOptions = null
    ): array
    {
        $payload = [];

        $this->addIfSet($payload, 'template', $this->client->getTemplate());
        $this->addIfSet($payload, 'firstName', $firstName);
        $this->addIfSet($payload, 'lastName', $lastName);
        $this->addIfSet($payload, 'email', $email);
        $this->addIfSet($payload, 'phone', $phone);
        $this->addIfSet($payload, 'scenario', $scenario ?? $this->client->getScenario());
        $this->addIfSet($payload, 'account_id', $accountId ?? $this->client->getAccountId());

        $finalCreatedAt = $createdAt ?? $this->client->getCreatedAt();
        if ($finalCreatedAt !== null) {
            $payload['created_at'] = $finalCreatedAt->format(\DateTimeInterface::ATOM);
        }

        $options = $customOptions ?? $this->client->getOptions();
        if (!empty($options)) {
            $payload['options'] = $options;
        }

        if (!empty($this->client->getFiles())) {
            $payload['files'] = array_map(fn(File $f) => $f->toArray(), $this->client->getFiles());
        }

        if (!empty($this->client->getRemove())) {
            $payload['remove'] = $this->client->getRemove();
        }

        if (!empty($this->client->getExists())) {
            $payload['exists'] = $this->client->getExists();
        }

        return $payload;
    }

    /**
     * Add a value to the payload if it is set.
     */
    private function addIfSet(array &$payload, string $key, mixed $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }

    /**
     * Execute the HTTP request.
     *
     * @param array<string,mixed>|array<int,array<string,mixed>> $payload
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    private function executeRequest(
        array $payload
    ): string
    {
        $url = $this->client->getEndpoint() . $this->path;

        $headers = [
            'Authorization: Bearer ' . $this->client->getApiKey(),
            'Content-Type: application/json',
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $response = $this->client->getHttpClient()->request($this->method, $url, $headers, $body);

        if (!$response->isSuccessful()) {
            throw new RuntimeException(
                'API error, code ' . $response->getStatusCode() . ': ' . $response->getBody()
            );
        }

        return $response->getBody();
    }
}
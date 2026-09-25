<?php

declare(strict_types=1);

namespace KandMailer\Traits;

use KandMailer\Models\File;
use KandMailer\Http\HttpClientInterface;

trait GetDatas
{
    /**
     * Get the API key.
     * 
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the endpoint.
     * 
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Get the template.
     * 
     * @return ?string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Get the email.
     * 
     * @return string|array<string>|null
     */
    public function getEmail(): string|array|null
    {
        return $this->email;
    }

    /**
     * Get the phone.
     * 
     * @return string|array<string>|null
     */
    public function getPhone(): string|array|null
    {
        return $this->phone;
    }
    /**
     * Get the first name.
     * 
     * @return string|array|null
     */
    public function getFirstName(): string|array|null
    {
        return $this->firstName;
    }

    /**
     * Get the last name.
     * 
     * @return string|array|null
     */
    public function getLastName(): string|array|null
    {
        return $this->lastName;
    }

    /**
     * Get the scenario.
     * 
     * @return ?string
     */
    public function getScenario(): ?string
    {
        return $this->scenario;
    }

    /**
     * Get the account ID.
     * 
     * @return ?string
     */
    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    /**
     * Get the created at date.
     * 
     * @return ?\DateTimeInterface
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Get all options.
     *
     * @return array<string,mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get a specific option.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Get multiple options.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getMultiOptions(): array
    {
        return $this->multiOptions;
    }

    /**
     * Get all files.
     *
     * @return array<File>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get keys to remove.
     *
     * @return array<string>
     */
    public function getRemove(): array
    {
        return $this->remove;
    }

    /**
     * Get keys to check.
     *
     * @return array<string>
     */
    public function getExists(): array
    {
        return $this->exists;
    }

    /**
     * Get content override.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Get SMTP From address.
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Get user email (send trigger trace).
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * Get pacing delay in milliseconds (X-Kandmail-Sleep).
     */
    public function getSleep(): ?int
    {
        return $this->sleep;
    }

    /**
     * Get the HTTP client.
     * 
     * @internal This method is reserved for internal testing purposes
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get the payload for a single recipient.
     *
     * @return array<string,mixed>
     */
    public function getPayload(): array
    {
        $maker = new \KandMailer\Helpers\Makers($this);
        return $maker->buildPayloadSingle();
    }

    /**
     * Get the payload for multiple recipients.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getPayloadMultiple(): array
    {
        $maker = new \KandMailer\Helpers\Makers($this);
        return $maker->buildMultiplePayload();
    }
}
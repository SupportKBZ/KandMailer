<?php

declare(strict_types=1);

namespace KandMailer\Traits;

use KandMailer\Models\File;

trait SetDatas
{
    /**
     * Set the template.
     * 
     * @return self
     */
    public function template(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Set the recipient to an email.
     * 
     * @param string|array<string> $emails
     * @return self
     * @throws \InvalidArgumentException If email is invalid
     */
    public function toEmail(string|array $emails): self
    {
        $this->validateEmails($emails);
        $this->email = $emails;
        return $this;
    }

    /**
     * Set the recipient to a phone.
     * 
     * @param string|array<string> $phones
     * @return self
     * @throws \InvalidArgumentException If phone is invalid
     */
    public function toPhone(string|array $phones): self
    {
        $this->validatePhones($phones);
        $this->phone = $phones;
        return $this;
    }

    /**
     * Set the recipient to a firstName.
     * 
     * @param string $firstName
     * @return self
     * @throws \InvalidArgumentException If phone is invalid
     */
    public function toFirstName(string|array $firstName): self
    {
        $this->firstName = is_array($firstName) ? $firstName : $firstName;
        return $this;
    }

    /**
     * Set the recipient to a lastName.
     * 
     * @return self
     */
    public function toLastName(string|array $lastName): self
    {
        $this->lastName = is_array($lastName) ? $lastName : $lastName;
        return $this;
    }

    /**
     * Set the email (alias for toEmail, kept for compatibility).
     * 
     * @return self
     * @throws \InvalidArgumentException If email is invalid
     */
    public function email(string|array $email): self
    {
        return $this->toEmail($email);
    }

    /**
     * Set the phone (alias for toPhone, kept for compatibility).
     * 
     * @return self
     * @throws \InvalidArgumentException If phone is invalid
     */
    public function phone(string|array $phone): self
    {
        return $this->toPhone($phone);
    }

    /**
     * Set the first name.
     * 
     * @return self
     */
    public function firstName(string|array $firstName): self
    {
        return $this->toFirstName($firstName);
    }

    /**
     * Set the last name.
     * 
     * @return self
     */
    public function lastName(string|array $lastName): self
    {
        return $this->toLastName($lastName);
    }

    /**
     * Set the scenario.
     * 
     * @return self
     */
    public function scenario(string $scenario): self
    {
        $this->scenario = $scenario;
        return $this;
    }

    /**
     * Set the account ID.
     * 
     * @return self
     */
    public function accountId(string $accountId): self
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Set the created at date.
     * 
     * @return self
     */
    public function createdAt(\DateTimeInterface $date): self
    {
        $this->createdAt = $date;
        return $this;
    }

    /**
     * Override the template content (editable templates only).
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the user email that triggered the send (stored on message for tracing).
     */
    public function userEmail(string $userEmail): self
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * Set the SMTP From address (must be allowlisted server-side).
     */
    public function from(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Add an option.
     */
    public function option(string $key, mixed $value): self
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Set all options at once.
     *
     * @param array<string,mixed> $options
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set multiple options for sendMultiple.
     *
     * @param array<int,array<string,mixed>> $options
     */
    public function multiOptions(array $options): self
    {
        $this->multiOptions = $options;
        return $this;
    }

    /**
     * Add a file.
     */
    public function file(string $label, string $publicId, string $secretId): self
    {
        $this->files[] = new File($label, $publicId, $secretId);
        return $this;
    }

    /**
     * Add multiple files.
     *
     * @param array<File> $files
     */
    public function files(array $files): self
    {
        $this->files = array_merge($this->files, $files);
        return $this;
    }

    /**
     * Keys to remove.
     *
     * @param array<string> $keys
     */
    public function setRemove(array $keys): self
    {
        $this->remove = $keys;
        return $this;
    }

    /**
     * Keys to check.
     *
     * @param array<string> $keys
     */
    public function setExists(array $keys): self
    {
        $this->exists = $keys;
        return $this;
    }

    /**
     * Reset the client for a new send (keep apiKey and endpoint).
     */
    public function reset(): self
    {
        $this->template = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->email = null;
        $this->phone = null;
        $this->scenario = null;
        $this->accountId = null;
        $this->createdAt = null;
        $this->content = null;
        $this->userEmail = null;
        $this->from = null;
        $this->options = [];
        $this->multiOptions = [];
        $this->files = [];
        $this->remove = [];
        $this->exists = [];

        return $this;
    }
}
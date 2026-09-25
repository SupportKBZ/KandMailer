<?php

declare(strict_types=1);

namespace KandMailer\Traits;

trait ValidateDatas
{
    /**
     * Validate email(s).
     * 
     * @param string|array<string> $emails
     * @throws \InvalidArgumentException
     */
    private function validateEmails(string|array $emails): void
    {
        $this->validateRecipients($emails, 'email');
    }

    /**
     * Validate phone(s).
     * 
     * @param string|array<string> $phones
     * @throws \InvalidArgumentException
     */
    private function validatePhones(string|array $phones): void
    {
        $this->validateRecipients($phones, 'phone');
    }

    /**
     * Validate that we have a single recipient (not multiple).
     *
     * @param string $method Method name for error message
     * @throws \InvalidArgumentException If validation fails
     */
    private function validateSingleRecipient(string $method): void
    {
        if ($this->email === null && $this->phone === null) {
            throw new \InvalidArgumentException(
                "{$method}() nécessite un email et/ou un téléphone."
            );
        }

        if (is_array($this->email) || is_array($this->phone)) {
            throw new \InvalidArgumentException(
                "{$method}() ne peut être utilisé qu'avec un seul destinataire. " .
                "Utilisez email(\$email) ou phone(\$phone) avec une chaîne, pas un tableau."
            );
        }
    }

    /**
     * Validate that multiOptions is not used with single recipient methods.
     *
     * @throws \InvalidArgumentException If multiOptions is set
     */
    private function validateNoMultiOptions(): void
    {
        if (!empty($this->multiOptions)) {
            throw new \InvalidArgumentException(
                'multiOptions() ne peut être utilisé qu\'avec sendMultiple(). ' .
                'Pour sendSingle(), utilisez option() ou options().'
            );
        }
    }

    /**
     * Validate that sleep is not used with single send methods.
     *
     * @throws \InvalidArgumentException If sleep is set
     */
    private function validateNoSleep(): void
    {
        if ($this->sleep !== null) {
            throw new \InvalidArgumentException(
                'sleep() ne peut être utilisé qu\'avec sendMultiple() ou sendToMultiple().'
            );
        }
    }

    /**
     * Generic validation for emails or phones.
     * 
     * @param string|array<string> $values
     * @param string $type 'email' or 'phone'
     * @throws \InvalidArgumentException
     */
    private function validateRecipients(string|array $values, string $type): void
    {
        $valueList = is_array($values) ? $values : [$values];
        $emptyMessage = $type === 'email' ? 'L\'email ne peut pas être vide.' : 'Le numéro de téléphone ne peut pas être vide.';
        
        foreach ($valueList as $value) {
            if (!is_string($value) || empty(trim($value))) {
                throw new \InvalidArgumentException($emptyMessage);
            }
            
            if ($type === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException("Email invalide: {$value}");
                }
            } else {
                // Format basique: doit contenir au moins 8 chiffres
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 8) {
                    throw new \InvalidArgumentException("Numéro de téléphone invalide: {$value}");
                }
            }
        }
    }
}
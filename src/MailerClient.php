<?php

declare(strict_types=1);

namespace KandMailer;

use KandMailer\Http\HttpClientInterface;
use KandMailer\Http\CurlHttpClient;
use KandMailer\Helpers\Makers;

use KandMailer\Models\File;
use KandMailer\Models\Recipient;

class MailerClient
{
    use \KandMailer\Traits\SetDatas;
    use \KandMailer\Traits\GetDatas;
    use \KandMailer\Traits\ValidateDatas;

    private string $apiKey;
    private string $endpoint;
    private HttpClientInterface $httpClient;

    private ?string $template = null;
    private string|array|null $firstName = null;
    private string|array|null $lastName = null;
    private string|array|null $email = null;
    private string|array|null $phone = null;
    private ?string $scenario = null;
    private ?string $accountId = null;
    private ?\DateTimeInterface $createdAt = null;

    /** @var array<string,mixed> */
    private array $options = [];

    /** @var array<int,array<string,mixed>> */
    private array $multiOptions = [];

    /** @var array<File> */
    private array $files = [];

    /** @var array<string> */
    private array $remove = [];

    /** @var array<string> */
    private array $exists = [];

    private ?string $content = null;
    private ?string $from = null;
    private ?string $userEmail = null;
    private ?int $sleep = null;

    /**
     * Constructor.
     * 
     * @param string $apiKey The API key.
     * @param string $endpoint The endpoint.
     * @param HttpClientInterface|null $httpClient (Internal: for testing only)
     * @internal The $httpClient parameter is reserved for internal testing purposes
     */
    public function __construct(
        string $apiKey,
        string $endpoint,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->endpoint = rtrim($endpoint, '/');
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Send a message to a single recipient.
     *
     * @throws \InvalidArgumentException If multiOptions is used
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendSingle(): string
    {
        $this->validateNoMultiOptions();
        $this->validateNoSleep();
        return (new Makers($this, 'POST', '/send/single'))->executeSingle();
    }

    /**
     * Send a message to multiple recipients.
     *
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendMultiple(): string
    {
        return (new Makers($this, 'POST', '/send/list'))->executeMultiple();
    }

    /**
     * Add a contact.
     *
     * @throws \InvalidArgumentException If recipient is multiple or not properly set
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function add(): string
    {
        $this->validateSingleRecipient('add');

        return (new Makers($this, 'POST', '/contact/add'))->executeSingle();
    }

    /**
     * Remove a contact.
     *
     * @throws \InvalidArgumentException If recipient is multiple or not properly set
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function remove(): string
    {
        $this->validateSingleRecipient('remove');

        return (new Makers($this, 'POST', '/contact/remove'))->executeSingle();
    }

    /**
     * Send a message to a single recipient using a Recipient object.
     *
     * @param Recipient $recipient The recipient to send to
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendTo(Recipient $recipient): string
    {
        $this->validateNoSleep();
        return (new Makers($this, 'POST', '/send/single'))->executeWithRecipient($recipient);
    }

    /**
     * Send messages to multiple recipients using Recipient objects.
     *
     * @param array<Recipient> $recipients Array of recipients to send to
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendToMultiple(array $recipients): string
    {
        return (new Makers($this, 'POST', '/send/list'))->executeWithRecipients($recipients);
    }

    /**
     * Add a contact using a Recipient object.
     *
     * @param Recipient $recipient The recipient to add
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function addTo(Recipient $recipient): string
    {
        return (new Makers($this, 'POST', '/contact/add'))->executeWithRecipient($recipient);
    }

    /**
     * Add multiple contacts using Recipient objects.
     *
     * @param array<Recipient> $recipients Array of recipients to add
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function addToMultiple(array $recipients): string
    {
        return (new Makers($this, 'POST', '/contact/add/list'))->executeWithRecipients($recipients);
    }

    /**
     * Remove a contact using a Recipient object.
     *
     * @param Recipient $recipient The recipient to remove
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function removeFrom(Recipient $recipient): string
    {
        return (new Makers($this, 'POST', '/contact/remove'))->executeWithRecipient($recipient);
    }
}
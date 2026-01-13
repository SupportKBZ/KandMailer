<?php

declare(strict_types=1);

namespace KandMailer;

use KandMailer\Http\HttpClientInterface;
use KandMailer\Http\CurlHttpClient;
use KandMailer\Helpers\Makers;

use KandMailer\Models\File;

class MailerClient
{
    use \KandMailer\Traits\SetDatas;
    use \KandMailer\Traits\GetDatas;
    use \KandMailer\Traits\ValidateDatas;

    private string $apiKey;
    private string $endpoint;
    private HttpClientInterface $httpClient;

    private ?string $template = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private string|array|null $email = null;
    private string|array|null $phone = null;
    private ?string $scenario = null;
    private ?string $accountId = null;
    private ?\DateTimeInterface $createdAt = null;

    /** @var array<string,mixed> */
    private array $options = [];

    /** @var array<File> */
    private array $files = [];

    /** @var array<string> */
    private array $remove = [];

    /** @var array<string> */
    private array $exists = [];

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
     * Send a message.
     *
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function send(): string
    {
        // Check if we have multiple recipients
        $hasMultiple = (is_array($this->email) && count($this->email) > 1) 
                    || (is_array($this->phone) && count($this->phone) > 1);

        if ($hasMultiple) {
            return Makers::request($this, 'POST', '/send/list');
        }

        return Makers::request($this, 'POST', '/send/single');
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

        return Makers::request($this, 'POST', '/contact/add');
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

        return Makers::request($this, 'POST', '/contact/remove');
    }
}

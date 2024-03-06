<?php

namespace MDMasudSikdar\Geezsms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class GeezSmsClient
{
    // API version constant
    const API_VERSION = 'v1';

    private string $baseUrl;
    private string $token;
    private string|int $shortcode_id;
    private string|int $group_id;
    private Client $httpClient;

    /**
     * GeezSmsClient constructor.
     *
     * @param string $token API token for authentication.
     * @param string|int $shortcode_id Optional shortcode ID.
     * @param string|int $group_id Optional group ID.
     */
    public function __construct(string $token, string|int $shortcode_id = '', string|int $group_id = '')
    {
        $this->baseUrl = 'https://api.geezsms.com/api/' . self::API_VERSION . "/sms";
        $this->token = $token;
        $this->shortcode_id = $shortcode_id;
        $this->group_id = $group_id;

        // Check if the API token is configured
        if (empty($this->token)) {
            throw new \RuntimeException("Token is required.");
        }

        $client = new Client();
        $this->setHttpClient($client);
    }

    /**
     * Set the HTTP client for the API client.
     *
     * @param Client $client The Guzzle HTTP client instance.
     *
     * @return GeezSmsClient Returns the modified instance of GeezSmsClient.
     */
    public function setHttpClient(Client $client): static
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Send OTP to a phone number.
     *
     * @param string $phone The phone number.
     * @param string $method The HTTP method (default is 'GET').
     *
     * @return array The decoded response data.
     *
     * @throws ClientExceptionInterface
     */
    public function sendOtp(string $phone, string $method = 'GET'): array
    {
        $this->validatePhoneNumber($phone);

        $body = ['phone' => $phone];

        // Add 'shortcode_id' to the body if $this->shortcode_id is not empty
        if (!empty($this->shortcode_id)) {
            $body['shortcode_id'] = $this->shortcode_id;
        }

        return $this->sendRequest("/otp", $method, $body);
    }

    /**
     * Send SMS to a phone number with a message.
     *
     * @param string $phone The phone number.
     * @param string $message The SMS message.
     * @param string $method The HTTP method (default is 'GET').
     *
     * @return array The decoded response data.
     *
     * @throws ClientExceptionInterface
     */
    public function sendSms(string $phone, string $message, string $method = 'POST'): array
    {
        $this->validatePhoneNumber($phone);
        $this->validateMessageLength($message);

        $body = ['phone' => $phone, 'msg' => $message];

        // Add 'shortcode_id' to the body if $this->shortcode_id is not empty
        if (!empty($this->shortcode_id)) {
            $body['shortcode_id'] = $this->shortcode_id;
        }

        return $this->sendRequest("/send", $method, $body);
    }

    /**
     * Send SMS to multiple phone numbers with a common message and notification URL.
     *
     * @param array $phones Array of phone numbers.
     * @param string $message The SMS message.
     * @param string $notify_url The notification URL.
     * @param string $method The HTTP method (default is 'POST').
     *
     * @return array The decoded response data.
     *
     * @throws ClientExceptionInterface
     */
    public function sendBulk(array $phones, string $message, string $notify_url): array
    {
        $this->validateBulkPhoneNumbers($phones);
        $this->validateMessageLength($message);
        $this->validateNotifyUrl($notify_url);

        $body = [
            'phone' => json_encode($phones),
            'msg' => $message,
            'notify' => $notify_url,
        ];

        // Add 'group_id' to the body if $this->group_id is not empty
        if (!empty($this->group_id)) {
            $body['groupid'] = $this->group_id;
        }

        return $this->sendRequest('/send/bulk', 'POST', $body);
    }

    /**
     * Send an HTTP request to the specified URL.
     *
     * @param string $endpoint The API endpoint to send the request to.
     * @param string $method The HTTP method for the request (default is 'GET').
     * @param array $params The parameters to include in the request.
     *
     * @return array The decoded response data.
     *
     * @throws ClientExceptionInterface
     */
    private function sendRequest(string $endpoint, string $method = 'GET', array $params = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            // Merge $params with $this->token
            $mergedParams = array_merge($params, ['token' => $this->token]);

            $request = $this->buildRequest($url, $method, $mergedParams);
            $response = $this->httpClient->sendRequest($request);

            return $this->handleResponse($response);

        } catch (RequestException $e) {
            // Log or handle the error in a more detailed way
            return ['status' => false, 'message' => 'Request failed', 'error_details' => $e->getMessage()];
        }
    }

    /**
     * Build a Guzzle HTTP request.
     *
     * @param string $url The URL for the request.
     * @param string $method The HTTP method for the request.
     * @param array $params The parameters to include in the request.
     *
     * @return Request The Guzzle HTTP request instance.
     */
    private function buildRequest(string $url, string $method, array $params = []): Request
    {
        if ($method !== 'GET' && $method !== 'POST') {
            throw new \InvalidArgumentException("Invalid HTTP method: $method. Only 'GET' and 'POST' are supported.");
        }

        $headers = [];
        $body = null;

        if ($method === 'GET') {
            if (!empty($params)) {
                $queryParams = http_build_query($params);
                $url .= "?" . $queryParams;
            }
        } else {
            // If using Guzzle, convert $body to a JSON-encoded string
            if (!empty($params)) {
                $body = json_encode($params);
                $headers['Content-Type'] = 'application/json';
            }
        }

        return new Request($method, $url, $headers, $body);
    }

    /**
     * Handle the HTTP response.
     *
     * @param ResponseInterface $response The HTTP response.
     *
     * @return array The decoded response data.
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody(), true);

        // Check if 'data' index exists in the response
        return $body['data'] ?? $body;
    }

    /**
     * Validate if a phone number has a valid format.
     *
     * @param string $phone The phone number.
     */
    private function validatePhoneNumber(string $phone): void
    {
        // Check if the phone number starts with "2519" or "+2519"
        if (!str_starts_with($phone, '2519') && !str_starts_with($phone, '+2519')) {
            throw new \InvalidArgumentException("Invalid phone number format. It must start with '2519' or '+2519'.");
        }
    }

    /**
     * Validate if multiple phone numbers have valid formats.
     *
     * @param array $phones Array of phone numbers.
     */
    private function validateBulkPhoneNumbers(array $phones): void
    {
        // Check phone number format for each phone number in the array
        foreach ($phones as $phone) {
            $this->validatePhoneNumber($phone);
        }
    }

    /**
     * Validate if a message length is within the limit.
     *
     * @param string $message The SMS message.
     */
    private function validateMessageLength(string $message): void
    {
        if (mb_strlen($message, 'UTF-8') > 335) {
            throw new \InvalidArgumentException("Message content must be less than 335 characters.");
        }
    }

    /**
     * Validate if a notification URL is a valid URL.
     *
     * @param string $notify_url The notification URL.
     */
    private function validateNotifyUrl(string $notify_url): void
    {
        if (!empty($notify_url) && filter_var($notify_url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("Invalid notify_url format. It must be a valid URL.");
        }
    }
}

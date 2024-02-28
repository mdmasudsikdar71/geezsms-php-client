<?php

namespace MDMasudSikdar\Geezsms\Tests;

use GuzzleHttp\Client;
use MDMasudSikdar\Geezsms\GeezSmsClient;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class GeezSmsClientTest extends TestCase
{
    private GeezSmsClient $smsClient;

    /**
     * Set up the GeezSmsClient instance for testing.
     */
    protected function setUp(): void
    {
        $this->smsClient = new GeezSmsClient('api_token_here');
    }

    /**
     * Test the constructor with a valid token.
     */
    public function testConstructorWithValidToken(): void
    {
        $this->assertInstanceOf(GeezSmsClient::class, $this->smsClient);
    }

    /**
     * Test the constructor with a missing token.
     */
    public function testConstructorWithMissingToken(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Token is required.");

        new GeezSmsClient('');
    }

    /**
     * Test the setHttpClient method.
     * @throws Exception
     */
    public function testSetHttpClient(): void
    {
        $httpClient = $this->createMock(Client::class);
        $result = $this->smsClient->setHttpClient($httpClient);

        $this->assertInstanceOf(GeezSmsClient::class, $result);
    }
}

# GeezSMS PHP Client
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mdmasudsikdar71/geezsms-php-client.svg?style=flat-square)](https://packagist.org/packages/mdmasudsikdar71/geezsms-php-client)
[![Total Downloads](https://img.shields.io/packagist/dt/mdmasudsikdar71/geezsms-php-client.svg?style=flat-square)](https://packagist.org/packages/mdmasudsikdar71/geezsms-php-client)

GeezSMS PHP Client is a PHP library for interacting with the GeezSMS API. It provides an easy-to-use interface for sending OTP, SMS, and bulk SMS messages.

## Installation

You can install the library using Composer:

```bash
composer require mdmasudsikdar71/geezsms-php-client
```

## Usage

```php
use MDMasudSikdar\Geezsms\GeezSmsClient;

// Initialize the GeezSmsClient with your token
$smsClient = new GeezSmsClient('TOKEN');

// Send an OTP to a phone number
$response = $smsClient->sendOtp('2519XXXXXXXX');

// Send an SMS to a phone number with a message
$response = $smsClient->sendSms('2519XXXXXXXX', 'Hello, this is a test message.');

// Send lite bulk SMS to multiple phone numbers with a common message
$phones = ['2519XXXXXXXX', '2519XXXXXXXX'];
$response = $smsClient->sendLiteBulk($phones, 'Hello, this is a bulk message.');

// Send bulk SMS to multiple phone numbers with a common message and notification URL
$phones = ['2519XXXXXXXX', '2519XXXXXXXX'];
$response = $smsClient->sendBulk($phones, 'Hello, this is a bulk message.', 'https://example.com/notify');
```

## Documentation

For more details and advanced usage, please refer to the [official GeezSMS API documentation](https://documenter.getpostman.com/view/11254016/TzK2YZ2J).

## Security

If you discover any security-related issues, please email masudsikdar85@gmail.com instead of using the issue tracker.

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

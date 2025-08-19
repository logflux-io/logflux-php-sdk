# LogFlux PHP SDK

Official PHP SDK for LogFlux Agent - A lightweight, high-performance log collection and forwarding agent.

## Quick Start

```php
<?php
require_once "vendor/autoload.php";

use LogFlux\Agent\LogFluxClient;

try {
    $client = new LogFluxClient("/tmp/logflux-agent.sock");
    $client->connect();
    $client->sendLog("Hello from LogFlux PHP SDK!");
    $client->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Features

- Lightweight client for communicating with LogFlux Agent local server
- Support for both Unix socket and TCP connections
- Automatic batching of log entries
- Built-in retry logic with exponential backoff
- Thread-safe operations
- Auto-discovery of agent configuration

## Documentation

For full documentation, visit [LogFlux Documentation](https://docs.logflux.io)

## License

This SDK is distributed under the Apache License, Version 2.0. See the LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues and questions, please use the GitHub issue tracker.

[![PHP CI](https://github.com/logflux-io/logflux-php-sdk/actions/workflows/php.yml/badge.svg)](https://github.com/logflux-io/logflux-php-sdk/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/logflux/sdk.svg)](https://packagist.org/packages/logflux/sdk)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

## Requirements

- PHP 7.4 or higher
- Composer

## Installation

### Composer

```bash
composer require logflux/sdk
```

## Usage Example

```php
<?php
require_once "vendor/autoload.php";

use LogFlux\Agent\LogFluxClient;
use LogFlux\Agent\LogEntry;

try {
    // Create a client for Unix socket connection
    $client = new LogFluxClient("/tmp/logflux-agent.sock");
    
    // Connect to the agent
    $client->connect();
    
    // Send a simple log message
    $client->sendLog("Hello from PHP SDK!");
    
    // Send a structured log entry
    $entry = new LogEntry()
        ->withMessage("Application started")
        ->withLevel(LogEntry::LEVEL_INFO)
        ->withSource("my-app")
        ->withLabel("component", "web-server")
        ->withLabel("version", "1.0.0");
    
    $client->sendLogEntry($entry);
    
    $client->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Features

- Support for both Unix socket and TCP connections
- Automatic reconnection with exponential backoff
- Batch processing for high-throughput scenarios
- Zero dependencies (pure PHP)
- PSR-3 logging interface compatibility
- Compatible with PHP 7.4+

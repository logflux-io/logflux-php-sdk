# LogFlux Agent PHP SDK

A lightweight PHP SDK for communicating with the LogFlux Agent via Unix socket or TCP protocols.

## Requirements

- PHP 8.0 or higher
- `ext-sockets` extension
- `ext-json` extension  
- Composer

## Installation

Install via Composer:

```bash
composer require logflux/agent-php-sdk
```

## Quick Start

### Basic Usage

```php
<?php

require_once 'vendor/autoload.php';

use LogFlux\Agent\LogFluxClient;
use LogFlux\Agent\LogEntry;

// Create a Unix socket client (recommended)
$client = new LogFluxClient('/tmp/logflux-agent.sock');

try {
    // Connect to the agent
    $client->connect();
    
    // Create and send a log entry
    $entry = (new LogEntry('Hello from PHP!'))
        ->withSource('my-php-app')
        ->withLevel(LogEntry::LEVEL_INFO)
        ->withLabel('component', 'example');
    
    $client->sendLogEntry($entry);
    
    echo "Log sent successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    $client->close();
}
```

### TCP Connection

```php
// Create a TCP client
$client = LogFluxClient::createTcpClient('localhost', 9999);
```

### Factory Methods

```php
// Use factory methods for cleaner code
$unixClient = LogFluxClient::createUnixClient('/tmp/logflux-agent.sock');
$tcpClient = LogFluxClient::createTcpClient('localhost', 9999);
```

## Log Levels

The SDK supports standard syslog levels:

```php
LogEntry::LEVEL_EMERGENCY  // 0 - Emergency
LogEntry::LEVEL_ALERT      // 1 - Alert  
LogEntry::LEVEL_CRITICAL   // 2 - Critical
LogEntry::LEVEL_ERROR      // 3 - Error
LogEntry::LEVEL_WARNING    // 4 - Warning
LogEntry::LEVEL_NOTICE     // 5 - Notice
LogEntry::LEVEL_INFO       // 6 - Info
LogEntry::LEVEL_DEBUG      // 7 - Debug
```

## Entry Types

```php
LogEntry::TYPE_LOG    // 1 - Standard log messages
LogEntry::TYPE_METRIC // 2 - Metrics data
LogEntry::TYPE_TRACE  // 3 - Distributed tracing
LogEntry::TYPE_EVENT  // 4 - Application events
LogEntry::TYPE_AUDIT  // 5 - Audit logs
```

## Payload Types

The SDK supports payload type hints for better log processing:

```php
// Specific payload types with convenience methods
$syslogEntry = LogEntry::newSyslogEntry('kernel: USB disconnect');
$journalEntry = LogEntry::newSystemdJournalEntry('Started SSH daemon');
$metricEntry = LogEntry::newMetricEntry('{"cpu_usage": 45.2}');
$containerEntry = LogEntry::newContainerEntry('[nginx] GET /health');

// Manual payload type assignment
$entry = (new LogEntry('Custom log message'))
    ->withPayloadType(LogEntry::PAYLOAD_TYPE_APPLICATION);

// Automatic JSON detection
$jsonEntry = LogEntry::newGenericEntry('{"user": "admin"}'); 
// Automatically detected as PAYLOAD_TYPE_GENERIC_JSON
```

## Advanced Usage

### Custom Labels and Metadata

```php
$entry = (new LogEntry('User login attempt'))
    ->withSource('auth-service')
    ->withLevel(LogEntry::LEVEL_INFO)
    ->withLabel('user_id', '12345')
    ->withLabel('ip_address', '192.168.1.100')
    ->withLabel('success', 'true')
    ->withPayloadType(LogEntry::PAYLOAD_TYPE_AUDIT);

$client->sendLogEntry($entry);
```

### Error Handling

```php
try {
    $client = LogFluxClient::createUnixClient('/tmp/logflux-agent.sock');
    $client->connect();
    
    if ($client->isConnected()) {
        $entry = new LogEntry('Operation completed');
        $client->sendLogEntry($entry);
    }
    
} catch (RuntimeException $e) {
    error_log('Failed to send log: ' . $e->getMessage());
    // Handle connection issues, retry logic, etc.
} finally {
    $client->close();
}
```

### Laravel Integration

```php
// In a Laravel service provider or helper class
<?php

namespace App\Services;

use LogFlux\Agent\LogFluxClient;
use LogFlux\Agent\LogEntry;

class LogFluxService
{
    private LogFluxClient $client;
    
    public function __construct()
    {
        $this->client = LogFluxClient::createUnixClient('/tmp/logflux-agent.sock');
        $this->client->connect();
    }
    
    public function logUserAction(string $action, string $userId): void
    {
        $entry = LogEntry::newApplicationEntry($action)
            ->withSource('laravel-app')
            ->withLevel(LogEntry::LEVEL_INFO)
            ->withLabel('user_id', $userId)
            ->withLabel('framework', 'laravel');
            
        $this->client->sendLogEntry($entry);
    }
}
```

### Symfony Integration

```php
// services.yaml
services:
    LogFlux\Agent\LogFluxClient:
        factory: ['LogFlux\Agent\LogFluxClient', 'createUnixClient']
        arguments: ['%env(LOGFLUX_SOCKET_PATH)%']
        calls:
            - [connect]

// In your controller or service
public function __construct(LogFluxClient $logFluxClient)
{
    $this->logFluxClient = $logFluxClient;
}

public function someAction(): Response
{
    $entry = (new LogEntry('Controller action executed'))
        ->withSource('symfony-app')
        ->withLabel('controller', self::class);
        
    $this->logFluxClient->sendLogEntry($entry);
    
    return new Response('OK');
}
```

### Metrics Logging

```php
$metricEntry = LogEntry::newMetricEntry(json_encode([
    'memory_usage' => memory_get_usage(true),
    'cpu_percent' => sys_getloadavg()[0],
    'active_connections' => 42
]))
->withSource('monitoring-agent')
->withLabel('host', gethostname())
->withLabel('service', 'web-server');

$client->sendLogEntry($metricEntry);
```

## Best Practices

1. **Use Unix sockets** for local communication (faster and more secure)
2. **Reuse client instances** when possible to avoid connection overhead
3. **Handle connection errors** gracefully with appropriate retry logic
4. **Set meaningful labels** for better log filtering and analysis
5. **Use appropriate log levels** to control verbosity
6. **Choose correct payload types** to help LogFlux route logs appropriately
7. **Use try-finally** or destructor to ensure cleanup

## Thread Safety

The `LogFluxClient` is **not thread-safe**. In multi-process environments (like PHP-FPM), each process will have its own client instance, which is the recommended approach.

## Development

All testing and building is performed exclusively via GitHub Actions. Local testing and building is not supported to ensure consistency across all environments.

### GitHub Actions Workflows

The SDK uses GitHub Actions for all CI/CD operations:

- **ci.yml** - Continuous integration workflow that runs on every push and pull request
  - Runs PHPUnit tests across PHP versions 7.4, 8.0, 8.1, 8.2, 8.3
  - Performs static analysis with PHPStan (level 8)
  - Checks code style compliance with PHP_CodeSniffer (PSR-12 standard)
  - Generates code coverage reports and uploads to Codecov
  - Runs integration tests with mock LogFlux Agent server
  - Performs security vulnerability scanning with `composer audit`
  - Creates distribution packages (tar.gz and zip)

- **release.yml** - Release workflow triggered by tags or manual dispatch
  - Validates version consistency across files
  - Runs full test suite on all supported PHP versions
  - Creates production-ready packages without dev dependencies
  - Generates checksums for release artifacts
  - Publishes to Packagist (if API token is configured)
  - Creates GitHub release with generated release notes

### Triggering Workflows

1. **Automatic CI**: Push to `main` or `develop` branches, or create a PR
2. **Manual Release**: Create a tag with pattern `php-sdk-v*` (e.g., `php-sdk-v1.2.0`)
3. **Workflow Dispatch**: Use GitHub Actions UI for manual workflow runs

### Testing Matrix

- **PHP Versions**: 7.4, 8.0, 8.1, 8.2, 8.3
- **Runner Configuration**: `[self-hosted, linux, arm64, docker]`
- **Test Types**: Unit tests, static analysis, code style, integration tests, security scans

### No Local Build Support

This SDK intentionally does not include local build scripts or Makefiles. All development operations must be performed through GitHub Actions to ensure:
- Consistent build environment
- Reproducible builds
- Centralized CI/CD management
- Uniform testing across all PHP versions

## License

This SDK is part of the LogFlux Agent project. See the main repository for license information.
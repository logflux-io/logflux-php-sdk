<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LogFlux\Agent\LogEntry;
use LogFlux\Agent\LogFluxClient;

echo "LogFlux PHP SDK - Basic Example\n";
echo "===============================\n";

try {
    // Create log entries to demonstrate API
    $basicEntry = new LogEntry('Hello from PHP SDK!');
    
    $detailedEntry = (new LogEntry('User login attempt'))
        ->withSource('php-example')
        ->withLevel(LogEntry::LEVEL_INFO)
        ->withLabel('user_id', '12345')
        ->withLabel('ip_address', '192.168.1.100');
    
    $jsonEntry = LogEntry::newGenericEntry('{"event": "user_login", "success": true}');
    $metricEntry = LogEntry::newMetricEntry('{"cpu_usage": 45.2, "memory": 1024}');
    
    // Display the entries (since we can't connect without an agent)
    echo "Created log entries:\n";
    echo "1. Basic: " . $basicEntry->getMessage() . "\n";
    echo "2. Detailed: " . $detailedEntry->getMessage() . " (labels: " . count($detailedEntry->getLabels()) . ")\n";
    echo "3. JSON: " . $jsonEntry->getMessage() . " (type: " . $jsonEntry->getLabels()['payload_type'] . ")\n";
    echo "4. Metric: " . $metricEntry->getMessage() . " (type: " . $metricEntry->getLabels()['payload_type'] . ")\n";
    
    // Demonstrate JSON conversion
    echo "\nJSON representation of basic entry:\n";
    echo $basicEntry->toJson() . "\n";
    
    echo "\nPHP SDK basic example completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
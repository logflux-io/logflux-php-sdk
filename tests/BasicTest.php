<?php

require_once __DIR__ . '/../src/LogEntry.php';
require_once __DIR__ . '/../src/LogFluxClient.php';

use PHPUnit\Framework\TestCase;
use LogFlux\Agent\LogEntry;
use LogFlux\Agent\LogFluxClient;

class BasicTest extends TestCase 
{
    public function testLogEntryCreation()
    {
        $entry = new LogEntry('Test message');
        $this->assertInstanceOf(LogEntry::class, $entry);
        $this->assertEquals('Test message', $entry->getMessage());
        $this->assertEquals('php-sdk', $entry->getSource());
        $this->assertEquals(LogEntry::LEVEL_INFO, $entry->getLevel());
        $this->assertEquals(LogEntry::TYPE_LOG, $entry->getEntryType());
    }

    public function testLogEntryWithLabels()
    {
        $entry = (new LogEntry('Test with labels'))
            ->withSource('test-app')
            ->withLevel(LogEntry::LEVEL_ERROR)
            ->withLabel('key1', 'value1')
            ->withLabel('key2', 'value2');

        $this->assertEquals('test-app', $entry->getSource());
        $this->assertEquals(LogEntry::LEVEL_ERROR, $entry->getLevel());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $entry->getLabels());
    }

    public function testGenericEntry()
    {
        $jsonMessage = '{"event": "test", "value": 123}';
        $entry = LogEntry::newGenericEntry($jsonMessage);
        
        $this->assertEquals($jsonMessage, $entry->getMessage());
        $this->assertEquals(LogEntry::PAYLOAD_TYPE_GENERIC_JSON, $entry->getLabels()['payload_type']);
    }

    public function testMetricEntry()
    {
        $metricData = '{"cpu": 45.2, "memory": 1024}';
        $entry = LogEntry::newMetricEntry($metricData);
        
        $this->assertEquals($metricData, $entry->getMessage());
        $this->assertEquals(LogEntry::TYPE_METRIC, $entry->getEntryType());
        $this->assertEquals(LogEntry::PAYLOAD_TYPE_METRICS, $entry->getLabels()['payload_type']);
    }

    public function testToArray()
    {
        $entry = (new LogEntry('Test message'))
            ->withSource('test-source')
            ->withLabel('test', 'value');

        $array = $entry->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('Test message', $array['message']);
        $this->assertEquals('test-source', $array['source']);
        $this->assertEquals(['test' => 'value'], $array['labels']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('timestamp', $array);
    }
}

<?php

namespace LogFlux\Agent;

use DateTime;
use Ramsey\Uuid\Uuid;

/**
 * Represents a log entry to be sent to LogFlux Agent
 */
class LogEntry
{
    // Entry Types
    const TYPE_LOG = 1;
    const TYPE_METRIC = 2;
    const TYPE_TRACE = 3;
    const TYPE_EVENT = 4;
    const TYPE_AUDIT = 5;

    // Log Levels (syslog)
    const LEVEL_EMERGENCY = 0;
    const LEVEL_ALERT = 1;
    const LEVEL_CRITICAL = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_WARNING = 4;
    const LEVEL_NOTICE = 5;
    const LEVEL_INFO = 6;
    const LEVEL_DEBUG = 7;

    // Payload Types
    const PAYLOAD_TYPE_SYSTEMD_JOURNAL = 'systemd_journal';
    const PAYLOAD_TYPE_SYSLOG = 'syslog';
    const PAYLOAD_TYPE_METRICS = 'metrics';
    const PAYLOAD_TYPE_APPLICATION = 'application';
    const PAYLOAD_TYPE_CONTAINER = 'container';
    const PAYLOAD_TYPE_GENERIC = 'generic';
    const PAYLOAD_TYPE_GENERIC_JSON = 'generic_json';

    private string $id;
    private string $message;
    private string $source;
    private int $entryType;
    private int $level;
    private int $timestamp;
    private array $labels;

    public function __construct(string $message)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->message = $message;
        $this->source = 'php-sdk';
        $this->entryType = self::TYPE_LOG;
        $this->level = self::LEVEL_INFO;
        $this->timestamp = time();
        $this->labels = [];
    }

    // Builder pattern methods
    public function withSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function withType(int $entryType): self
    {
        $this->entryType = $entryType;
        return $this;
    }

    public function withLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function withTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function withLabel(string $key, string $value): self
    {
        $this->labels[$key] = $value;
        return $this;
    }

    public function withPayloadType(string $payloadType): self
    {
        return $this->withLabel('payload_type', $payloadType);
    }

    // Convenience factory methods
    public static function newGenericEntry(string $message): self
    {
        $payloadType = self::isValidJson($message) ? self::PAYLOAD_TYPE_GENERIC_JSON : self::PAYLOAD_TYPE_GENERIC;
        return (new self($message))->withPayloadType($payloadType);
    }

    public static function newSyslogEntry(string $message): self
    {
        return (new self($message))->withPayloadType(self::PAYLOAD_TYPE_SYSLOG);
    }

    public static function newSystemdJournalEntry(string $message): self
    {
        return (new self($message))->withPayloadType(self::PAYLOAD_TYPE_SYSTEMD_JOURNAL);
    }

    public static function newMetricEntry(string $message): self
    {
        return (new self($message))->withType(self::TYPE_METRIC)->withPayloadType(self::PAYLOAD_TYPE_METRICS);
    }

    public static function newApplicationEntry(string $message): self
    {
        return (new self($message))->withPayloadType(self::PAYLOAD_TYPE_APPLICATION);
    }

    public static function newContainerEntry(string $message): self
    {
        return (new self($message))->withPayloadType(self::PAYLOAD_TYPE_CONTAINER);
    }

    // JSON validation helper
    private static function isValidJson(string $str): bool
    {
        if (empty(trim($str))) {
            return false;
        }

        $str = trim($str);
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // Convert to array for JSON serialization
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'source' => $this->source,
            'entry_type' => $this->entryType,
            'level' => $this->level,
            'timestamp' => $this->timestamp,
            'labels' => $this->labels,
        ];
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getEntryType(): int
    {
        return $this->entryType;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
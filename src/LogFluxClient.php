<?php

namespace LogFlux\Agent;

use Exception;
use RuntimeException;

/**
 * LogFlux Agent client for PHP applications
 */
class LogFluxClient
{
    private ?string $socketPath;
    private ?string $host;
    private ?int $port;
    private bool $isUnixSocket;
    private $socket = null;
    private bool $connected = false;

    /**
     * Create a Unix socket client
     */
    public function __construct(string $socketPathOrHost, ?int $port = null)
    {
        if ($port === null) {
            // Unix socket
            $this->socketPath = $socketPathOrHost;
            $this->host = null;
            $this->port = null;
            $this->isUnixSocket = true;
        } else {
            // TCP socket
            $this->socketPath = null;
            $this->host = $socketPathOrHost;
            $this->port = $port;
            $this->isUnixSocket = false;
        }
    }

    /**
     * Create a Unix socket client
     */
    public static function createUnixClient(string $socketPath): self
    {
        return new self($socketPath);
    }

    /**
     * Create a TCP client
     */
    public static function createTcpClient(string $host, int $port): self
    {
        return new self($host, $port);
    }

    /**
     * Connect to the LogFlux agent
     */
    public function connect(): void
    {
        if ($this->connected) {
            return;
        }

        try {
            if ($this->isUnixSocket) {
                $this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
                if ($this->socket === false) {
                    throw new RuntimeException('Failed to create Unix socket: ' . socket_strerror(socket_last_error()));
                }

                $result = socket_connect($this->socket, $this->socketPath);
                if ($result === false) {
                    throw new RuntimeException('Failed to connect to Unix socket: ' . socket_strerror(socket_last_error($this->socket)));
                }
            } else {
                $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if ($this->socket === false) {
                    throw new RuntimeException('Failed to create TCP socket: ' . socket_strerror(socket_last_error()));
                }

                $result = socket_connect($this->socket, $this->host, $this->port);
                if ($result === false) {
                    throw new RuntimeException('Failed to connect to TCP socket: ' . socket_strerror(socket_last_error($this->socket)));
                }
            }

            $this->connected = true;

        } catch (Exception $e) {
            $this->cleanup();
            throw new RuntimeException('Failed to connect to LogFlux agent: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Send a log entry to the agent
     */
    public function sendLogEntry(LogEntry $entry): void
    {
        if (!$this->connected) {
            throw new RuntimeException('Client not connected. Call connect() first.');
        }

        try {
            // Convert entry to JSON
            $messageArray = $entry->toArray();
            $jsonMessage = json_encode($messageArray, JSON_THROW_ON_ERROR);
            
            // Add newline delimiter
            $messageWithNewline = $jsonMessage . "\n";
            
            // Send the message
            $result = socket_write($this->socket, $messageWithNewline, strlen($messageWithNewline));
            if ($result === false) {
                throw new RuntimeException('Failed to write to socket: ' . socket_strerror(socket_last_error($this->socket)));
            }

        } catch (Exception $e) {
            $this->connected = false;
            $this->cleanup();
            throw new RuntimeException('Failed to send log entry: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if client is connected
     */
    public function isConnected(): bool
    {
        return $this->connected && $this->socket !== null;
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        $this->connected = false;
        $this->cleanup();
    }

    /**
     * Cleanup resources
     */
    private function cleanup(): void
    {
        if ($this->socket !== null) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Destructor ensures cleanup
     */
    public function __destruct()
    {
        $this->close();
    }
}
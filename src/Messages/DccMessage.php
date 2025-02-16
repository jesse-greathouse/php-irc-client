<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\IrcClientEvent,
    JesseGreathouse\PhpIrcClient\Helpers\Event;

/**
 * Represents a DCC (Direct Client-to-Client) message received via IRC.
 */
class DccMessage extends IrcMessage
{
    /** @var string Name of the DCC action (e.g., SEND, CHAT). */
    public string $action;

    /** @var string|null Name of the file being transferred (if applicable). */
    public ?string $fileName = null;

    /** @var string|null IP address of the sender. */
    public ?string $ip = null;

    /** @var int|null Port number for the connection. */
    public ?int $port = null;

    /** @var int|null File size if applicable. */
    public ?int $fileSize = null;

    /**
     * Initializes the DccMessage instance.
     *
     * @param string $command The raw DCC command.
     */
    public function __construct(string $command)
    {
        parent::__construct($command);

        $parts = explode(' ', $command);

        $this->action = $parts[1] ?? '';
        $this->fileName = $parts[2] ?? null;
        $this->ip = $parts[3] ?? null;
        $this->port = isset($parts[4]) ? (int) $parts[4] : null;
        $this->fileSize = isset($parts[5]) ? (int) $parts[5] : null;
    }

    /**
     * Retrieves an array of events triggered by this message.
     *
     * @return Event[] List of events associated with this DCC message.
     */
    public function getEvents(): array
    {
        return [
            new Event(IrcClientEvent::DCC, [$this->fileName, $this->ip, $this->port, $this->fileSize])
        ];
    }
}

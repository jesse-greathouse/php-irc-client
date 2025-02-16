<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\IrcClientEvent,
    JesseGreathouse\PhpIrcClient\Helpers\Event;

/**
 * Represents a CTCP (Client-To-Client Protocol) message received via IRC.
 */
class CtcpMessage extends IrcMessage
{
    /** @var string Name of the CTCP action. */
    public string $action;

    /** @var string Full payload of the message. */
    public string $command;

    /** @var string[] Additional arguments of the action. */
    public array $args = [];

    /**
     * Initializes the CtcpMessage instance.
     *
     * @param string $command The raw CTCP command.
     */
    public function __construct(string $command)
    {
        parent::__construct($command);

        // Explode the command into parts for processing.
        $this->args = explode(' ', $command);

        // Remove the first element (CTCP identifier).
        array_shift($this->args);

        // Extract the action and remaining arguments.
        $this->action = array_shift($this->args) ?? '';

        // Assign the command payload.
        $this->command = $this->payload;
    }

    /**
     * Retrieves an array of events triggered by this message.
     *
     * @return Event[] List of events associated with this CTCP message.
     */
    public function getEvents(): array
    {
        return [new Event(IrcClientEvent::CTCP, [$this->action, $this->args, $this->command])];
    }
}

<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Messages;

use JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\IrcChannel,
    JesseGreathouse\PhpIrcClient\IrcClient;

/**
 * Represents a generic IRC message with parsing and event handling capabilities.
 */
class IrcMessage
{
    /** @var IrcChannel|null The IRC channel associated with this message, if applicable. */
    public ?IrcChannel $channel = null;

    /** @var string|null The command suffix, if present. */
    protected ?string $commandSuffix = null;

    /** @var bool Indicates whether this message has been handled. */
    protected bool $handled = false;

    /** @var string The payload or message content. */
    protected string $payload = '';

    /** @var string|null The source of the message, usually a nickname. */
    protected ?string $source = null;

    /** @var string|null The target of the message, such as a user or channel. */
    public ?string $target = null;

    /**
     * Initializes an IRC message and parses its components.
     *
     * @param string $command The raw IRC command string.
     */
    public function __construct(protected string $command)
    {
        $this->parse($this->command);
    }

    /**
     * Returns the parsed payload of the message.
     *
     * @return string The message payload.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Handles the message using the provided IRC client.
     *
     * @param IrcClient $client The IRC client instance.
     * @param bool $force Whether to force handling if already handled.
     */
    public function handle(IrcClient $client, bool $force = false): void
    {
        if ($this->handled && !$force) {
            return;
        }
    }

    /**
     * Retrieves an array of events triggered by this message.
     *
     * @return Event[] List of events associated with this message.
     */
    public function getEvents(): array
    {
        return [];
    }

    /**
     * Injects a list of known IRC channels into this message instance.
     *
     * @param array<string, IrcChannel> $channels Associative array of channels.
     */
    public function injectChannel(array $channels): void
    {
        if (isset($channels[$this->target])) {
            $this->channel = $channels[$this->target];
        }
    }

    /**
     * Parses the raw IRC command string and extracts its components.
     *
     * @param string $command The raw command string.
     */
    protected function parse(string $command): void
    {
        $command = trim($command);

        // Extract source if present
        if (str_starts_with($command, ':')) {
            $parts = explode(' ', $command, 3);
            if (count($parts) < 2) {
                return;
            }
            $this->source = substr($parts[0], 1);
            $this->command = $parts[1];
            $command = $parts[2] ?? ''; // Correctly preserve the remaining portion
        } else {
            $parts = explode(' ', $command, 2);
            $this->command = $parts[0];
            $command = $parts[1] ?? '';
        }

        // Extract command suffix and payload if present
        if (str_contains($command, ':')) {
            [$suffix, $payload] = explode(':', $command, 2);
            $this->commandSuffix = trim($suffix);
            $this->payload = $payload;
        } else {
            $this->commandSuffix = trim($command);
        }
    }

}

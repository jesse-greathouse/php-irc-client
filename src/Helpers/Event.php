<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Helpers;

class Event
{
    /** @var string The name of the event being emitted */
    private string $event;

    /** @var array The arguments to send to the event callback */
    private array $arguments;

    /**
     * Constructor for the Event class.
     * Initializes the event with a name and optional arguments.
     *
     * @param string $event The event that is being emitted.
     * @param array $arguments The array of arguments to send to the event callback.
     */
    public function __construct(string $event, array $arguments = [])
    {
        $this->event = $event;
        $this->arguments = $arguments;
    }

    /**
     * Get the arguments associated with the event.
     *
     * @return array The arguments passed to the event callback.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the event name.
     *
     * @return string The name of the event.
     */
    public function getEvent(): string
    {
        return $this->event;
    }
}

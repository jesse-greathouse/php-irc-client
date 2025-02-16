<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient\Helpers;

class EventHandlerCollection
{
    /** @var array<string, array<int, callable>> A list of event handlers indexed by event name */
    private array $eventHandlers = [];

    /**
     * Registers an event handler.
     *
     * If a callable is passed as the event, it will catch all events (`*`).
     *
     * @param callable|string $event The event name or a callable to catch all events.
     * @param callable|null $function The function to invoke when the event is triggered.
     */
    public function addHandler(callable|string $event, ?callable $function = null): void
    {
        // If the event is a callable, treat it as a handler for all events
        if (is_callable($event)) {
            $function = $event;
            $event = '*';
        }

        // Initialize the array for the event if not already set
        $this->eventHandlers[$event] ??= [];

        // Add the handler to the list of handlers for the event
        $this->eventHandlers[$event][] = $function;
    }

    /**
     * Invokes all handlers associated with a specific event.
     *
     * Handlers for the specific event and wildcard `*` are merged and executed.
     * If the event's arguments are null, an empty array is passed to the handlers.
     *
     * @param Event $event The event instance containing the event name and arguments.
     *
     * @return void
     */
    public function invoke(Event $event): void
    {
        // Merge handlers for the event and the wildcard event (`*`).
        $handlers = array_merge(
            $this->eventHandlers['*'] ?? [],
            $this->eventHandlers[$event->getEvent()] ?? []
        );

        $arguments = $event->getArguments() ?? [];

        // Execute each handler with the event's arguments.
        foreach ($handlers as $handler) {
            $handler(...$arguments);
        }
    }

    /**
     * Retrieves a list of all registered event names.
     *
     * @return array<string> List of event names for which handlers are registered.
     */
    public function getEventHandlerList(): array
    {
        return array_keys($this->eventHandlers);
    }
}

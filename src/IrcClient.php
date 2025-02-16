<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

use JesseGreathouse\PhpIrcClient\Helpers\EventHandlerCollection,
    JesseGreathouse\PhpIrcClient\Exceptions\NickRequiredException,
    JesseGreathouse\PhpIrcClient\Messages\IrcMessage,
    JesseGreathouse\PhpIrcClient\Options\ClientOptions;

/**
 * Class representing an IRC client connection.
 */
class IrcClient
{
    /** @var array<string, IrcChannel> Stores channels the client is connected to. */
    private array $channels = [];

    /** @var IrcConnection Connection handler. */
    private IrcConnection $connection;

    /** @var bool Indicates if the client is authenticated. */
    private bool $isAuthenticated = false;

    /** @var EventHandlerCollection Collection of message event handlers. */
    private EventHandlerCollection $messageEventHandlers;

    /** @var ClientOptions Configuration options for the client. */
    private ClientOptions $options;

    /** @var IrcUser|null Current user connected to the IRC server. */
    private ?IrcUser $user = null;

    /** @var string|null Client version. */
    private ?string $version;

    public const VERSION_DEFAULT = 'php-irc-client by Jesse Greathouse (https://github.com/jesse-greathouse/php-irc-client)';

    /**
     * IrcClient constructor.
     *
     * Initializes the IRC client and sets up necessary connections and event handlers.
     *
     * @param string $server The server address to connect to, including port (`address:port`).
     * @param ClientOptions|null $options Optional configuration options for the connection.
     * @param string|null $version Client version.
     */
    public function __construct(string $server, ?ClientOptions $options = null, ?string $version = null)
    {
        $this->options = $options ?? new ClientOptions();
        $this->connection = new IrcConnection($server, $this->options->connectionOptions());

        if ($this->options->nickname !== null) {
            $this->user = new IrcUser($this->options->nickname);
        }

        $this->messageEventHandlers = new EventHandlerCollection();

        if (!empty($this->options->channels)) {
            foreach ($this->options->channels as $channel) {
                $this->channels[$channel] = new IrcChannel($channel);
            }
        }

        $this->version = $version;

        if ($this->options->autoConnect) {
            $this->connect();
        }
    }

    /**
     * Set the user credentials for the connection.
     *
     * @param IrcUser|string $user The user information, either an IrcUser object or nickname string.
     */
    public function setUser(IrcUser|string $user): void
    {
        if (is_string($user)) {
            $user = new IrcUser($user);
        }

        if ($this->connection->isConnected() && $this->user->nickname !== $user->nickname) {
            $this->send("NICK :$user->nickname");
        }

        $this->user = $user;
    }

    /**
     * Connect to the IRC server and start listening for messages.
     *
     * @throws NickRequiredException if no user information is provided before connecting.
     */
    public function connect(): void
    {
        if ($this->user === null) {
            throw new NickRequiredException('A nickname must be set before connecting to an IRC server.');
        }

        if ($this->connection->isConnected()) {
            return;
        }

        $this->isAuthenticated = false;
        $this->connection->onData(fn(IrcMessage $msg) => $this->handleIrcMessage($msg));
        $this->connection->open();
    }

    /**
     * Close the current connection.
     */
    public function disconnect(): void
    {
        $this->connection->close();
    }

    /**
     * Register a callback for an event.
     *
     * @param string $event The event to register for.
     * @param callable $callback The callback to invoke when the event is emitted.
     */
    public function on(string $event, callable $callback): void
    {
        $this->messageEventHandlers->addHandler($event, $callback);
    }

    /**
     * Send a raw command to the IRC server.
     *
     * @param string $command The command string to send.
     */
    public function send(string $command): void
    {
        $this->connection->write($command);
    }

    /**
     * Send a message to a channel or user.
     *
     * @param string $target The channel or user to message. The target must start with `#` for channels.
     * @param string $message The message to send.
     */
    public function say(string $target, string $message): void
    {
        foreach (explode("\n", $message) as $msg) {
            $this->send("PRIVMSG $target :" . trim($msg));
        }
    }

    /**
     * Join an IRC channel.
     *
     * @param string $channel The channel name to join.
     */
    public function join(string $channel): void
    {
        $channel = $this->formatChannelName($channel);
        $this->send("JOIN $channel");
        $this->getChannel($channel);
    }

    /**
     * Leave an IRC channel.
     *
     * @param string $channel The channel name to leave.
     */
    public function part(string $channel): void
    {
        $channel = $this->formatChannelName($channel);

        if (isset($this->channels[$channel])) {
            $this->send("PART $channel");
        }
    }

    /**
     * Retrieve channel information by name.
     *
     * @param string $channel The channel name.
     *
     * @return IrcChannel The channel object.
     */
    public function getChannel(string $channel): IrcChannel
    {
        $channel = $this->formatChannelName($channel);

        return $this->channels[$channel] ??= new IrcChannel($channel);
    }

    /**
     * Get the client's current nickname.
     *
     * @return string|null The nickname or null if not set.
     */
    public function getNickname(): ?string
    {
        return $this->user?->nickname;
    }

    /**
     * Get all connected channels.
     *
     * @return array<string, IrcChannel> List of channels.
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Check if the client should auto-rejoin channels when kicked.
     *
     * @return bool True if auto-rejoin is enabled, false otherwise.
     */
    public function shouldAutoRejoin(): bool
    {
        return $this->options->autoRejoin;
    }

    /**
     * Handle incoming IRC messages and trigger appropriate event handlers.
     *
     * @param IrcMessage $message The incoming message.
     */
    private function handleIrcMessage(IrcMessage $message): void
    {
        $message->injectChannel($this->channels);
        $message->handle($this);

        if (!$this->isAuthenticated && $this->user !== null) {
            $this->send("USER {$this->user->nickname} * * :{$this->user->nickname}");
            $this->send("NICK {$this->user->nickname}");
            $this->isAuthenticated = true;
        }

        foreach ($message->getEvents() as $event) {
            $this->messageEventHandlers->invoke($event);
        }
    }

    /**
     * Ensure the channel name is properly formatted.
     *
     * @param string $channel The raw channel name.
     *
     * @return string The formatted channel name.
     */
    private function formatChannelName(string $channel): string
    {
        return $channel[0] === '#' ? $channel : "#$channel";
    }

    /**
     * Get the connection instance.
     *
     * @return IrcConnection The IRC connection.
     */
    public function getConnection(): IrcConnection
    {
        return $this->connection;
    }

    /**
     * Get the client version.
     *
     * @return string The version of the client.
     */
    public function getVersion(): string
    {
        return $this->version ?? self::VERSION_DEFAULT;
    }

    /**
     * Set the client version.
     *
     * @param string $version The version to set.
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Convert the client properties to an array.
     *
     * @return array The client properties as an associative array.
     */
    public function toArray(): array
    {
        $nick = $this->user ? (string) $this->user : null;
        $channels = [];

        foreach ($this->getChannels() as $channel) {
            $channels[$channel->getName()] = $channel->toArray();
        }

        return [
            'user'              => $nick,
            'version'           => $this->getVersion(),
            'is_authenticated'  => $this->isAuthenticated,
            'channels'          => $channels,
            'connection'        => $this->getConnection()->toArray(),
        ];
    }

    /**
     * Convert the client properties to a JSON string.
     *
     * @return string The client properties as a JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

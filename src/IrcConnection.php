<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;


use JesseGreathouse\PhpIrcClient\Exceptions\NotConnectedException,
    JesseGreathouse\PhpIrcClient\Helpers\Event,
    JesseGreathouse\PhpIrcClient\Helpers\EventHandlerCollection,
    JesseGreathouse\PhpIrcClient\Messages\IrcMessage,
    JesseGreathouse\PhpIrcClient\Options\ConnectionOptions;

use React\Dns\Resolver\Factory as DnsResolverFactory,
    React\EventLoop\Factory as LoopFactory,
    React\EventLoop\LoopInterface,
    React\Socket\DnsConnector,
    React\Socket\ConnectionInterface,
    React\Socket\TcpConnector;

class IrcConnection
{
    /** @var array<int, string> Message queue for flood protection */
    private array $messageQueue = [];

    /** @var bool Connection status */
    private bool $connected = false;

    /** @var ConnectionInterface|null Active connection instance */
    private ?ConnectionInterface $connection = null;

    /** @var EventHandlerCollection Event handler collection */
    private EventHandlerCollection $eventHandlerCollection;

    /** @var bool Flag indicating whether flood protection is enabled */
    private bool $floodProtected;

    /** @var LoopInterface Event loop instance */
    private LoopInterface $loop;

    /** @var IrcMessageParser IRC message parser instance */
    private IrcMessageParser $messageParser;

    public function __construct(
        private string $server,
        ?ConnectionOptions $options = null
    ) {
        $options = $options ?? new ConnectionOptions();

        $this->eventHandlerCollection = new EventHandlerCollection();
        $this->floodProtected = $options->floodProtectionDelay > 0;
        $this->loop = LoopFactory::create();
        $this->messageParser = new IrcMessageParser();

        if ($this->floodProtected) {
            $this->loop->addPeriodicTimer($options->floodProtectionDelay / 1000, function () {
                if ($msg = array_shift($this->messageQueue)) {
                    $this->connection->write($msg);
                }
            });
        }
    }

    /**
     * Open a connection to the IRC server.
     */
    public function open(): void
    {
        if ($this->isConnected()) {
            return;
        }

        $tcpConnector = new TcpConnector($this->loop);
        $dnsResolverFactory = new DnsResolverFactory();
        $dns = $dnsResolverFactory->createCached('1.1.1.1', $this->loop);
        $dnsConnector = new DnsConnector($tcpConnector, $dns);

        $dnsConnector->connect($this->server)->then(function (ConnectionInterface $connection) {
            $this->connection = $connection;
            $this->connected = true;

            $this->connection->on(IrcClientEvent::DATA, function ($data) {
                foreach ($this->messageParser->parse($data) as $msg) {
                    $this->handleMessage($msg);
                }
            });

            $this->connection->on(IrcClientEvent::CLOSE, function () {
                $this->connected = false;
            });

            $this->connection->on(IrcClientEvent::END, function () {
                $this->connected = false;
            });
        });

        $this->loop->run();
    }

    /**
     * Close the current IRC server connection.
     */
    public function close(): void
    {
        if ($this->isConnected()) {
            $this->connection->close();
            $this->loop->stop();
        }
    }

    /**
     * Test if there is an open connection to the IRC server.
     */
    public function isConnected(): bool
    {
        return $this->connection && $this->connected;
    }

    /**
     * Set a callback for received IRC data.
     * An IrcMessage object will be passed to the callback.
     *
     * @param callable $function The function to be called.
     */
    public function onData(callable $function): void
    {
        $this->eventHandlerCollection->addHandler('data', $function);
    }

    /**
     * Send a command to the IRC server.
     *
     * @param string $command The raw IRC command.
     *
     * @throws NotConnectedException if no open connection is available.
     */
    public function write(string $command): void
    {
        if (!$this->isConnected()) {
            throw new NotConnectedException('No open connection was found to write commands to.');
        }

        // Make sure the command ends in a newline character
        if (substr($command, -1) !== "\n") {
            $command .= "\n";
        }

        if ($this->floodProtected) {
            $this->messageQueue[] = $command;
        } else {
            $this->connection->write($command);
        }
    }

    /**
     * Handle a single parsed IrcMessage.
     *
     * @param IrcMessage $message The parsed IRC message to handle.
     */
    private function handleMessage(IrcMessage $message): void
    {
        $this->eventHandlerCollection->invoke(new Event('data', [$message]));
    }

    /**
     * Returns the instance of this connection.
     *
     * @return ConnectionInterface|null
     */
    public function getConnection(): ?ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Get the server address for this connection.
     *
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * Converts the properties of this class to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $handlers = $this->eventHandlerCollection->getEventHandlerList();
        return [
            'server' => $this->getServer(),
            'is_connected' => $this->isConnected(),
            'event_handlers' => $handlers,
        ];
    }

    /**
     * Set the connection status.
     *
     * @param bool $connected
     */
    public function setConnected(bool $connected): void
    {
        $this->connected = $connected;
    }
}

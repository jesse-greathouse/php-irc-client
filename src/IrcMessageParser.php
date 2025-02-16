<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

use JesseGreathouse\PhpIrcClient\Messages\{
    ConsoleMessage,
    CtcpMessage,
    DccMessage,
    IrcMessage,
    JoinMessage,
    InviteMessage,
    KickMessage,
    MOTDMessage,
    ModeMessage,
    NameReplyMessage,
    NickMessage,
    NoticeMessage,
    PartMessage,
    PingMessage,
    PrivmsgMessage,
    QuitMessage,
    TopicChangeMessage,
    VersionMessage,
    WelcomeMessage
};

use Generator;

class IrcMessageParser
{
    /**
     * Parse one or more IRC messages.
     *
     * @param string $message A string received from the IRC server
     * @return Generator|IrcMessage[] Parsed IRC messages as objects
     */
    public function parse(string $message): Generator
    {
        foreach (explode("\r\n", $message) as $msg) {
            if (trim($msg) === '') {
                continue;
            }

            yield $this->parseSingle($msg);
        }
    }

    /**
     * Parse a single IRC message to a corresponding object.
     *
     * @param string $message The IRC message string to parse
     * @return IrcMessage The parsed message object
     */
    private function parseSingle(string $message): IrcMessage
    {
        $command = $this->getCommand($message);

        // Handle malformed message gracefully by returning a generic IrcMessage.
        if ($command === false) {
            return new IrcMessage($message);
        }

        return match ($command) {
            IrcEvent::VERSION => new VersionMessage($message),
            IrcEvent::CTCP => new CtcpMessage($message),
            IrcEvent::DCC => new DccMessage($message),
            IrcEvent::JOIN => new JoinMessage($message),
            IrcEvent::KICK => new KickMessage($message),
            IrcEvent::PING => new PingMessage($message),
            IrcEvent::PRIVMSG => new PrivmsgMessage($message),
            IrcEvent::WELCOME => new WelcomeMessage($message),
            IrcEvent::TOPIC, IrcEvent::RPL_TOPIC => new TopicChangeMessage($message),
            IrcEvent::NAMREPLY => new NameReplyMessage($message),
            IrcEvent::MOTD => new MOTDMessage($message),
            IrcEvent::MODE => new ModeMessage($message),
            IrcEvent::NICK => new NickMessage($message),
            IrcEvent::NOTICE => new NoticeMessage($message),
            IrcEvent::INVITE => new InviteMessage($message),
            IrcEvent::QUIT => new QuitMessage($message),
            IrcEvent::PART => new PartMessage($message),
            default => $this->parseNumericCommand($command, $message)
        };
    }

    /**
     * Parse the command part of an IRC message.
     *
     * @param string $message The IRC message to parse
     * @return bool|string The command or false if not found
     */
    private function getCommand(string $message): bool|string
    {
        // Skip the leading colon if present, then get the command
        if ($message[0] === ':') {
            $message = strstr($message, ' ') ?: '';
        }

        return strstr(trim($message), ' ', true) ?: false;
    }

    /**
     * Parse numeric commands, often from the server console.
     *
     * @param string $command The command to check
     * @param string $message The raw message
     * @return IrcMessage The corresponding message object
     */
    private function parseNumericCommand(string $command, string $message): IrcMessage
    {
        // If it's a 3-digit numeric code (usually from the server console)
        if (strlen(trim($command)) === 3 && is_numeric($command)) {
            return new ConsoleMessage($message);
        }

        // Default return for unknown commands
        return new IrcMessage($message);
    }
}

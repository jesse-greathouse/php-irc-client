<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

use Generator;
use JesseGreathouse\PhpIrcClient\Messages\ConsoleMessage;
use JesseGreathouse\PhpIrcClient\Messages\CtcpMessage;
use JesseGreathouse\PhpIrcClient\Messages\DccMessage;
use JesseGreathouse\PhpIrcClient\Messages\IrcMessage;
use JesseGreathouse\PhpIrcClient\Messages\JoinMessage;
use JesseGreathouse\PhpIrcClient\Messages\InviteMessage;
use JesseGreathouse\PhpIrcClient\Messages\KickMessage;
use JesseGreathouse\PhpIrcClient\Messages\MOTDMessage;
use JesseGreathouse\PhpIrcClient\Messages\ModeMessage;
use JesseGreathouse\PhpIrcClient\Messages\NameReplyMessage;
use JesseGreathouse\PhpIrcClient\Messages\NickMessage;
use JesseGreathouse\PhpIrcClient\Messages\NoticeMessage;
use JesseGreathouse\PhpIrcClient\Messages\PartMessage;
use JesseGreathouse\PhpIrcClient\Messages\PingMessage;
use JesseGreathouse\PhpIrcClient\Messages\PrivmsgMessage;
use JesseGreathouse\PhpIrcClient\Messages\QuitMessage;
use JesseGreathouse\PhpIrcClient\Messages\TopicChangeMessage;
use JesseGreathouse\PhpIrcClient\Messages\VersionMessage;
use JesseGreathouse\PhpIrcClient\Messages\WelcomeMessage;

class IrcMessageParser
{
    /**
     * Parse one or more IRC messages.
     *
     * @param string $message A string received from the IRC server
     * @return Generator|IrcMessage[]
     */
    public function parse(string $message)
    {
        foreach (explode("\r\n", $message) as $msg) {
            if ('' === trim($msg)) {
                continue;
            }

            yield $this->parseSingle($msg);
        }
    }

    /**
     * Parse a single message to a corresponding object.
     */
    private function parseSingle(string $message): IrcMessage
    {
        $command = $this->getCommand($message);

        // Sometimes Parsing can fail due to malformed message.
        // Just return empty Message Object.
        if (false === $command) return new IrcMessage($message);

        switch ($command) {
            case 'VERSION':
                return new VersionMessage($message);
            case 'CTCP':
                return new CtcpMessage($message);
            case 'DCC':
                return new DccMessage($message);
            case 'JOIN':
                return new JoinMessage($message);
            case 'KICK':
                return new KickMessage($message);
            case 'PING':
                return new PingMessage($message);
            case 'PRIVMSG':
                return new PrivmsgMessage($message);
            case IrcCommand::RPL_WELCOME:
                return new WelcomeMessage($message);
            case 'TOPIC':
            case IrcCommand::RPL_TOPIC:
                return new TopicChangeMessage($message);
            case IrcCommand::RPL_NAMREPLY:
                return new NameReplyMessage($message);
            case IrcCommand::RPL_MOTD:
                return new MOTDMessage($message);
            case 'MODE':
                return new ModeMessage($message);
            case 'NICK':
                return new NickMessage($message);
            case 'NOTICE':
                return new NoticeMessage($message);
            case 'INVITE':
                return new InviteMessage($message);
            case 'QUIT':
                return new QuitMessage($message);
            case 'PART':
                return new PartMessage($message);
            default:
                // The 3 digit numeric commands are usually the server's console messaging.
                if (false !== $command && (3 === strlen(trim($command))) && is_numeric($command)) {
                    return new ConsoleMessage($message);
                }

                return new IrcMessage($message);
        }
    }

    /**
     * Get the COMMAND part of an IRC message.
     */
    private function getCommand(string $message): bool | string
    {
        if ($message[0] === ':') {
            $s = strstr($message, ' ');
            if (false !== $s) {
                $message = $s;
            }
        }

        return strstr(trim($message), ' ', true);
    }
}

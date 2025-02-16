<?php

declare(strict_types=1);

namespace JesseGreathouse\PhpIrcClient;

final class IrcClientEvent
{
    /** @var string Version constant */
    public const VERSION = 'version';

    /** @var string CTCP constant */
    public const CTCP = 'ctcp';

    /** @var string Console constant */
    public const CONSOLE = 'console';

    /** @var string Close constant */
    public const CLOSE = 'close';

    /** @var string End constant */
    public const END = 'end';

    /** @var string Data constant */
    public const DATA = 'data';

    /** @var string DCC constant */
    public const DCC = 'dcc';

    /** @var string Join constant */
    public const JOIN = 'joinInfo';

    /** @var string Kick constant */
    public const KICK = 'kick';

    /** @var string Ping constant */
    public const PING = 'ping';

    /** @var string PRIVMSG constant */
    public const PRIVMSG = 'privmsg';

    /** @var string Message constant */
    public const MESSAGE = 'message';

    /** @var string Topic constant */
    public const TOPIC = 'topic';

    /** @var string Mode constant */
    public const MODE = 'mode';

    /** @var string NAMES constant */
    public const NAMES = 'names';

    /** @var string NICK constant */
    public const NICK = 'nick';

    /** @var string Notice constant */
    public const NOTICE = 'notice';

    /** @var string INVITE constant */
    public const INVITE = 'invite';

    /** @var string QUIT constant */
    public const QUIT = 'quit';

    /** @var string PART constant */
    public const PART = 'part';

    /** @var string REGISTERED constant */
    public const REGISTERED = 'registered';

    /** @var string MOTD constant */
    public const MOTD = 'motd';
}

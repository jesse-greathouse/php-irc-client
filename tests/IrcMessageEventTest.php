<?php

declare(strict_types=1);

namespace Tests;

use JesseGreathouse\PhpIrcClient\Helpers\Event;
use JesseGreathouse\PhpIrcClient\Helpers\EventHandlerCollection;
use JesseGreathouse\PhpIrcClient\IrcChannel;
use JesseGreathouse\PhpIrcClient\IrcClient;
use JesseGreathouse\PhpIrcClient\IrcConnection;
use JesseGreathouse\PhpIrcClient\IrcMessageParser;

class IrcMessageEventTest extends TestCase
{
    public function testKick(): void
    {
        $this->invokeClientEvents(
            ':JesseGreathouse!~JesseGreathouse@foo.bar.be KICK #channel user :Get out!',
            [[new Event(
                'kick',
                [new IrcChannel('#channel'), 'user', 'JesseGreathouse', 'Get out!']
            )]]
        );
    }

    public function testMOTD(): void
    {
        $this->invokeClientEvents(
            ':JesseGreathouse!~JesseGreathouse@foo.bar.be 372 IrcBot :Message of the day',
            [[new Event('motd', ['Message of the day'])]]
        );
    }

    public function testNamesEvent(): void
    {
        $this->invokeClientEvents(
            ':JesseGreathouse!~JesseGreathouse@foo.bar.be 353 IrcBot = #channel :IrcBot @Q OtherUser',
            [
                [new Event('names', [new IrcChannel('#channel'), ['IrcBot', '@Q', 'OtherUser']])],
                [new Event('names#channel', [['IrcBot', '@Q', 'OtherUser']])],
            ]
        );
    }

    public function testPingEvent(): void
    {
        $this->invokeClientEvents('PING :0123456', [[new Event('ping')]]);
        $this->invokeClientEvents(
            "PING :0123456\nPING :0123457",
            [[new Event('ping')], [new Event('ping')]]
        );
    }

    public function testPrivmsgEvent(): void
    {
        $this->invokeClientEvents(
            ':JesseGreathouse!~JesseGreathouse@foo.bar.be PRIVMSG #channel :Hello World!',
            [
                [new Event('message', ['JesseGreathouse', new IrcChannel('#channel'), 'Hello World!'])],
                [new Event('message#channel', ['JesseGreathouse', new IrcChannel('#channel'), 'Hello World!'])],
            ]
        );
    }

    public function testTopicChangeEvent(): void
    {
        $this->invokeClientEvents(
            ':JesseGreathouse!~JesseGreathouse@foo.bar.be TOPIC #channel :My Topic',
            [[new Event('topic', [new IrcChannel('#channel'), 'My Topic'])]]
        );
    }

    private function invokeClientEvents(string $message, array $expectedEvents): void
    {
        $eventCollection = $this->getMockBuilder(EventHandlerCollection::class)
            ->setMethods(['invoke'])
            ->getMock();
        $eventCollection->expects($this->exactly(count($expectedEvents)))
            ->method('invoke')
            ->withConsecutive(...$expectedEvents);

        $connection = $this->getMockBuilder(IrcConnection::class)
            ->setConstructorArgs([''])
            ->setMethods(['write'])
            ->getMock();

        $client = new IrcClient('');
        $client->setUser('PhpIrcClient');
        $this->setPrivate($client, 'messageEventHandlers', $eventCollection);
        $this->setPrivate($client, 'connection', $connection);
        $this->setPrivate($client, 'channels', ['#channel' => new IrcChannel('#channel')]);

        foreach ((new IrcMessageParser())->parse($message) as $msg) {
            $this->callPrivate($client, 'handleIrcMessage', [$msg]);
        }
    }
}

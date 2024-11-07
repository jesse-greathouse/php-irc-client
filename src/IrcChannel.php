<?php

declare(strict_types=1);

namespace Jerodev\PhpIrcClient;

use Exception;

class IrcChannel
{
    const PART_MASK = '/#PART\:\s(.*)$/is';

    /**
     * Name of the channel.
     *
     * @var string
     */
    private $name;

    /**
     * Topic of the channel.
     *
     * @var string
     */
    private $topic;

    /** @var array<int, string> */
    private array $users = [];

    public function __construct(string $name)
    {
        $name = trim($name);

        if ('' === $name || '#' === $name) {
            throw new Exception('Channel name is empty.');
        }

        $name = $this->namefromPartMsg($name);

        $this->name = $name;

        if ($this->name[0] !== '#') {
            $this->name = '#' . $this->name;
        }
    }

    /**
     * Fetch the name of the channel, including the `#`.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the current channel topic.
     */
    public function getTopic(): ?string
    {
        return $this->topic;
    }

    /**
     * Fetch the list of users currently on this channel.
     * @return array<int, string>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Set the current channel topic.
     * @param string $topic The topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * Set the list of active users on the channel.
     * User modes (`+`, `@`) will be removed from the nicknames.
     * @param array<int, string> $users An array of user names.
     */
    public function setUsers(array $users): void
    {
        $this->users = array_map(function ($user): string {
            if (null !== $user && $user !== '') {
                if (in_array($user[0], ['+', '@'])) {
                    $user = substr($user, 1);
                }
            }

            return $user;
        }, $users);
    }

    /**
     * Attempts to get the chat name from a weird part message.
     * Use case came from part message: user-name parted #PART: channel-name
     *
     * @param string $name
     * @return string
     */
    private function namefromPartMsg(string $name): string|null
    {
        $matches = [];

        preg_match(self::PART_MASK, $name, $matches);

        if (1 < count($matches)) {
            [, $name] = $matches;
        }

        return $name;
    }
}

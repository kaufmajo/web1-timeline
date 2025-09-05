<?php

declare(strict_types=1);

namespace App\Storage;

use App\Model\CommandInterface;
use Exception;

class CommandStorage
{
    protected array $commandArray = [];

    public function set(string $key, CommandInterface $command): void
    {
        $this->commandArray[$key] = $command;
    }

    /**
     * @return CommandInterface
     * @throws Exception
     */
    public function get(string $key)
    {
        if (isset($this->commandArray[$key])) {
            return $this->commandArray[$key];
        }

        throw new Exception('Command is not in storage: ' . $key);
    }
}

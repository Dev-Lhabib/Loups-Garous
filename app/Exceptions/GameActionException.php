<?php

namespace App\Exceptions;

use RuntimeException;

class GameActionException extends RuntimeException
{
    public function __construct(
        private string $messageKey,
        private array $params = [],
    ) {
        parent::__construct(__($messageKey, $params));
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}

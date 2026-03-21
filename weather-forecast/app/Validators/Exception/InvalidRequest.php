<?php

namespace App\Validators\Exception;

class InvalidRequest extends \RuntimeException
{
    public function __construct(string $message, private readonly int $httpCode = 400)
    {
        parent::__construct($message);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
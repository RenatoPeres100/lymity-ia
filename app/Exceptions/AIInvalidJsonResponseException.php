<?php

namespace App\Exceptions;

use RuntimeException;

class AIInvalidJsonResponseException extends RuntimeException
{
    public function __construct(
        string  $message = 'Resposta JSON inválida do Gemini após normalização.',
        private readonly string $preview = '',
        private readonly string $schemaName = '',
        int     $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getPreview(): string
    {
        return $this->preview;
    }

    public function getSchemaName(): string
    {
        return $this->schemaName;
    }
}

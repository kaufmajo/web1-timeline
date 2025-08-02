<?php

namespace App\Listener;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class LoggingErrorListener
{
    /**
     * Log format for messages:
     *
     * STATUS [METHOD] path: message
     */
    const LOG_FORMAT = '%d [%s] %s: %s';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->logger->error(sprintf(
            self::LOG_FORMAT,
            $response->getStatusCode(),
            'Method:' . $request->getMethod(),
            'Uri:' . (string) $request->getUri(),
            'Message:' . $error->getMessage() . ' File:' . $error->getFile() . ' Line:' . $error->getLine()
        ));
    }
}

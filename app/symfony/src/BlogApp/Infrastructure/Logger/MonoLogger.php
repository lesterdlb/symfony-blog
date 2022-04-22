<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\Logger;

use App\BlogApp\Domain\LoggerInterface;
use Psr\Log\LoggerInterface as Logger;

final class MonoLogger implements LoggerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }
}
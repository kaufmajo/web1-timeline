<?php

declare(strict_types=1);

namespace App\Middleware;

use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ServerVersionProvider;
use Psr\Log\LoggerInterface;

class DbalLoggingMiddleware implements Middleware
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function wrap(Driver $driver): Driver
    {
        return new class($driver, $this->logger) implements Driver {

            public function __construct(
                private readonly Driver $driver,
                private readonly LoggerInterface $logger
            ) {}

            public function connect(array $params): DriverConnection
            {
                $conn = $this->driver->connect($params);

                return new class($conn, $this->logger) implements DriverConnection {

                    public function __construct(
                        private readonly DriverConnection $conn,
                        private readonly LoggerInterface $logger
                    ) {}

                    public function prepare(string $sql): \Doctrine\DBAL\Driver\Statement
                    {
                        $stmt = $this->conn->prepare($sql);

                        return new class($stmt, $sql, $this->logger) implements \Doctrine\DBAL\Driver\Statement {

                            private array $boundParams = [];

                            public function __construct(
                                private readonly \Doctrine\DBAL\Driver\Statement $stmt,
                                private readonly string $sql,
                                private readonly LoggerInterface $logger
                            ) {}

                            public function bindValue(int|string $param, mixed $value, ParameterType $type): void
                            {
                                $this->boundParams[$param] = $value;
                                $this->stmt->bindValue($param, $value, $type);
                            }

                            public function execute($params = null): \Doctrine\DBAL\Driver\Result
                            {
                                $allParams = $params ?? $this->boundParams;
                                $finalSql = $this->interpolateQuery($this->sql, $allParams);

                                $this->logger->info('[SQL EXECUTE] ' . $finalSql);

                                return $this->stmt->execute($params);
                            }

                            private function interpolateQuery(string $query, array $params): string
                            {
                                $keys = [];
                                $values = [];

                                foreach ($params as $key => $value) {
                                    // Named placeholders
                                    if (is_string($key)) {
                                        $keys[] = '/:' . preg_quote($key, '/') . '/';
                                    } else {
                                        // Positional placeholders
                                        $keys[] = '/\?/';
                                    }

                                    if (is_null($value)) {
                                        $values[] = 'NULL';
                                    } elseif (is_numeric($value)) {
                                        $values[] = $value;
                                    } else {
                                        $values[] = "'" . addslashes((string)$value) . "'";
                                    }
                                }

                                return preg_replace($keys, $values, $query, 1, $count);
                            }
                        };
                    }

                    public function query(string $sql): \Doctrine\DBAL\Driver\Result
                    {
                        $this->logger->info('[SQL QUERY] ' . $sql);
                        return $this->conn->query($sql);
                    }

                    public function exec(string $sql): int
                    {
                        $this->logger->info('[SQL EXEC] ' . $sql);
                        return $this->conn->exec($sql);
                    }

                    public function lastInsertId(?string $name = null): string
                    {
                        return $this->conn->lastInsertId($name);
                    }

                    public function beginTransaction(): void
                    {
                        $this->conn->beginTransaction();
                    }

                    public function commit(): void
                    {
                        $this->conn->commit();
                    }

                    public function rollBack(): void
                    {
                        $this->conn->rollBack();
                    }

                    public function quote(string $value): string
                    {
                        return $this->conn->quote($value);
                    }

                    public function getNativeConnection(): mixed
                    {
                        return $this->conn->getNativeConnection();
                    }

                    public function getServerVersion(): string
                    {
                        return $this->conn->getServerVersion();
                    }
                };
            }

            public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
            {
                return $this->driver->getDatabasePlatform($versionProvider);
            }

            public function getExceptionConverter(): ExceptionConverter
            {
                return $this->driver->getExceptionConverter();
            }
        };
    }
}

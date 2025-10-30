<?php

declare(strict_types=1);

namespace App\Infrastructure\Redis;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class RedisProvider
{
    public function __construct(
        #[Autowire(env: 'REDIS_DSN')]
        private string $dsn,
    ) {
    }

    public function get(): \Redis
    {
        $parts = parse_url($this->dsn);
        if (!$parts || !isset($parts['host'])) {
            throw new \InvalidArgumentException("DSN Redis invalide : $this->dsn");
        }

        $redis = new \Redis();
        $port = $parts['port'] ?? 6379;
        $redis->connect($parts['host'], (int) $port);

        if (isset($parts['pass'])) {
            $redis->auth($parts['pass']);
        }

        if (isset($parts['path'])) {
            $db = (int) ltrim($parts['path'], '/');
            $redis->select($db);
        }

        return $redis;
    }
}

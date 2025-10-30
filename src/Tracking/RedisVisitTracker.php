<?php

declare(strict_types=1);

namespace App\Tracking;

use Symfony\Component\HttpFoundation\Request;

readonly class RedisVisitTracker implements VisitTrackerInterface
{
    public function __construct(
        private \Redis $redis,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function track(Request $request): void
    {
        $now = new \DateTimeImmutable(timezone: new \DateTimeZone('UTC'));
        $day = $now->format('Ymd');
        $timestamp = $now->getTimestamp();

        $path = $request->getPathInfo();

        // --- Détails (ZSET journalier) ---
        $zsetKey = 'visits:'.$day;
        $data = [
            'path' => $path,
            'ip' => $request->getClientIp(),
            'referrer' => $request->headers->get('referer', ''),
            'accept_language' => $request->headers->get('accept-language', ''),
            'user_agent' => $request->headers->get('user-agent', ''),
            'timestamp' => $timestamp,
        ];

        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $json = [];
        }

        $this->redis->multi();

        $this->redis->zAdd($zsetKey, $timestamp, $json);
        $this->redis->expire($zsetKey, self::TTL_DAYS * 86400);

        // --- Compteur global du jour ---
        $totalKey = 'pageviews:total:'.$day;
        $this->redis->incr($totalKey);
        $this->redis->expire($totalKey, self::TTL_DAYS * 86400);

        // --- Compteur par page ---
        $pageKey = sprintf('pageviews:%s:%s', $path, $day);
        $this->redis->incr($pageKey);
        $this->redis->expire($pageKey, self::TTL_DAYS * 86400);

        // --- ZSET global de popularité ---
        $this->redis->zIncrBy('pageviews:zset', 1, $path);

        $this->redis->exec();
    }
}

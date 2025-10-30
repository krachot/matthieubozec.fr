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
            $json = '{}';
        }

        $this->redis->multi();

        $this->redis->zAdd($zsetKey, $timestamp, (string) $json);
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

        // --- Visiteurs uniques (SET) ---
        $uniqueKey = 'visitors:unique:'.$day;
        $ip = $data['ip'] ?? 'unknown';
        if ('unknown' !== $ip) {
            $this->redis->sAdd($uniqueKey, $ip);
            $this->redis->expire($uniqueKey, self::TTL_DAYS * 86400);
        }

        $this->redis->exec();
    }

    /**
     * @return array{
     *     period_days: int,
     *     total_visits: int,
     *     unique_visitors: int,
     *     visits_per_day: array<string,int>,
     *     top_pages: array<string,int>,
     *     top_langs: array<string,int>,
     *     top_referrers: array<string,int>
     * }
     */
    public function getStats(int $days = 30): array
    {
        $now = new \DateTimeImmutable(timezone: new \DateTimeZone('UTC'));

        /** @var array<string,int> $visits */
        $visits = [];
        $total = 0;
        /** @var array<string,int> $langs */
        $langs = [];
        /** @var array<string,int> $referrers */
        $referrers = [];
        /** @var array<string,int> $topPages */
        $topPages = [];

        for ($i = 0; $i < $days; ++$i) {
            $date = (string) $now->modify("-$i days")->format('Ymd');
            $key = "visits:$date";

            /** @var array<int,string>|false $entries */
            $entries = $this->redis->zRange($key, 0, -1);
            if (!is_array($entries)) {
                continue;
            }

            // forcer la clé en string
            $visits[(string) $date] = count($entries);
            $total += $visits[(string) $date];

            foreach ($entries as $json) {
                /** @var array{
                 *     path?: string,
                 *     ip?: string,
                 *     referrer?: string,
                 *     accept_language?: string,
                 *     user_agent?: string,
                 *     timestamp?: int
                 * }|null $data
                 */
                $data = json_decode($json, true);
                if (!is_array($data)) {
                    continue;
                }

                $langRaw = $data['accept_language'] ?? '';
                $lang = '' !== $langRaw ? explode(',', (string) $langRaw)[0] : 'unknown';
                $langs[$lang] = ($langs[$lang] ?? 0) + 1;

                $ref = $data['referrer'] ?? '';
                $referrer = '' !== $ref ? (string) $ref : 'direct';
                $referrers[$referrer] = ($referrers[$referrer] ?? 0) + 1;

                $page = (string) ($data['path'] ?? '/');
                $topPages[$page] = ($topPages[$page] ?? 0) + 1;
            }
        }

        arsort($langs);
        arsort($referrers);
        arsort($topPages);

        return [
            'period_days' => $days,
            'total_visits' => (int) $total,
            'unique_visitors' => $this->getUniqueVisitors($days),
            'visits_per_day' => array_map(
                static fn (int $v): int => $v,
                array_combine(
                    array_map('strval', array_keys($visits)),
                    array_values($visits)
                ) ?: []
            ),
            'top_pages' => array_slice($topPages, 0, 10, true),
            'top_langs' => array_slice($langs, 0, 5, true),
            'top_referrers' => array_slice($referrers, 0, 5, true),
        ];
    }

    public function getUniqueVisitors(int $days = 30): int
    {
        $now = new \DateTimeImmutable(timezone: new \DateTimeZone('UTC'));
        $keys = [];

        for ($i = 0; $i < $days; ++$i) {
            $keys[] = 'visitors:unique:'.$now->modify("-$i days")->format('Ymd');
        }

        $unionKey = 'visitors:unique:tmp:'.$now->getTimestamp();
        $this->redis->sUnionStore($unionKey, ...$keys);
        $count = (int) $this->redis->sCard($unionKey);
        $this->redis->del($unionKey);

        return $count;
    }
}

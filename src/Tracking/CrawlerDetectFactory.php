<?php

declare(strict_types=1);

namespace App\Tracking;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\Request;

class CrawlerDetectFactory
{
    public function create(?Request $request = null): CrawlerDetect
    {
        if ($request) {
            // the app is accessed by a HTTP request
            $headers = $request->server->all();
            $userAgent = $request->headers->get('User-Agent');
        } else {
            // the app is accessed by the CLI
            $headers = $userAgent = null;
        }

        return new CrawlerDetect($headers, $userAgent);
    }
}

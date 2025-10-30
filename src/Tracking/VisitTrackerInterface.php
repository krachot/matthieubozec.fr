<?php

namespace App\Tracking;

use Symfony\Component\HttpFoundation\Request;

interface VisitTrackerInterface
{
    public const TTL_DAYS = 360;

    public function track(Request $request): void;
}

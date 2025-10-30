<?php

declare(strict_types=1);

namespace App\Http\Listener;

use App\Tracking\CrawlerDetectFactory;
use App\Tracking\VisitTrackerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

#[AsEventListener(event: RequestEvent::class, method: 'onRequest')]
#[AsEventListener(event: TerminateEvent::class, method: 'onTerminate')]
class RequestTrackingListener
{
    private ?Request $requestToTrack = null;

    public function __construct(
        private readonly CrawlerDetectFactory $crawlerDetectFactory,
        private readonly VisitTrackerInterface $visitTracker,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->shouldTrack($request)) {
            return;
        }

        $this->requestToTrack = $request;
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (!$this->requestToTrack) {
            return;
        }

        $this->visitTracker->track($this->requestToTrack);
    }

    private function shouldTrack(Request $request): bool
    {
        if (!$request->isMethod(Request::METHOD_GET)) {
            return false;
        }

        if ($request->isXmlHttpRequest() || 'json' === $request->getPreferredFormat()) {
            return false;
        }

        if ('82.66.236.112' === $request->getClientIp()) {
            // Don't track me
            return false;
        }

        if (
            $request->headers->has('Turbo-Frame')
            || 'false' === $request->headers->get('Turbo-Visit')
            || $request->isMethod(Request::METHOD_HEAD)
        ) {
            return false;
        }

        if (str_starts_with($request->getPathInfo(), '/_profiler')) {
            return false;
        }

        if ($this->crawlerDetectFactory->create($request)->isCrawler()) {
            return false;
        }

        return true;
    }
}

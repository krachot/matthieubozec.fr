<?php

declare(strict_types=1);

namespace App\Http\ValueResolver;

use App\Page\Page;
use App\Page\PageRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsTargetedValueResolver('page')]
readonly class PageValueResolver implements ValueResolverInterface
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
    ) {
    }

    /**
     * @return iterable<Page>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (
            !$argumentType
            || !is_a($argumentType, Page::class, true)
        ) {
            return [];
        }

        $permalink = $request->attributes->get('permalink');
        if (!is_string($permalink)) {
            return [];
        }

        if ($page = $this->pageRepository->findByPermalink($permalink)) {
            return [$page];
        }

        throw new NotFoundHttpException(\sprintf('Invalid page given for parameter "%s".', $permalink));
    }
}

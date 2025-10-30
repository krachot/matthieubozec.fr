<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Page\PageRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:generate:og-image',
)]
class GenerateOgImageCommand extends Command
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addOption('page', mode: InputOption::VALUE_REQUIRED)
            ->addOption('title', null, InputOption::VALUE_REQUIRED)
            ->addOption('description', null, InputOption::VALUE_REQUIRED)
            ->addArgument('target', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $page */
        $page = $input->getOption('page');
        /** @var string|null $title */
        $title = $input->getOption('title');
        /** @var string|null $description */
        $description = $input->getOption('description');
        /** @var string $target */
        $target = $input->getArgument('target');

        if (!$page && !$title) {
            $output->writeln('<error>No Page or Title provided</error>');

            return Command::INVALID;
        }

        $ogImageContent = null;
        if ($page) {
            $ogImageContent = $this->generateForPage($page, $output);
        }

        if ($title) {
            $ogImageContent = $this->generateManually($title, $description);
        }

        if (!$ogImageContent) {
            $output->writeln('<error>Error while create og-image</error>');

            return Command::FAILURE;
        }

        $targetDir = dirname($target);
        $fs = new Filesystem();
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $fs->dumpFile($target, $ogImageContent);

        return Command::SUCCESS;
    }

    private function generateForPage(string $pageKey, OutputInterface $output): ?string
    {
        $page = $this->pageRepository->find($pageKey);
        if (!$page) {
            $output->writeln(sprintf('<error>Could not find a page with key "%s"</error>', $pageKey));

            return null;
        }

        /** @var string $title */
        $title = $page->get('og.title') ?: $page->get('title');
        /** @var string|null $description */
        $description = $page->get('og.description') ?: null;

        return $this->doGenerate($title, $description);
    }

    private function generateManually(string $title, ?string $description = null): string
    {
        return $this->doGenerate($title, $description);
    }

    private function doGenerate(string $title, ?string $description = null): string
    {
        if (!$description) {
            $description = 'matthieubozec.fr';
        }

        $response = $this->httpClient->request('POST', 'https://ogimage.click/api/v1/images', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'name' => 'og:basic',
                'params' => [
                    'title' => [
                        'text' => $title,
                        'fontFamily' => 'inter',
                        'fontWeight' => 700,
                        'fontSize' => 50,
                        'color' => '#030712',
                    ],
                    'description' => [
                        'text' => $description,
                        'fontFamily' => 'inter',
                        'fontWeight' => 400,
                        'fontSize' => 30,
                        'color' => '#030712',
                    ],
                    'logo' => [
                        'url' => 'https://www.matthieubozec.fr/img/logo-2.png',
                    ],
                ],
                'background' => [
                    'type' => 'linear-gradient',
                    'colorStops' => [
                        'rgb(186, 232, 232)',
                        'rgb(255, 255, 254)',
                        'rgb(255, 255, 254)',
                    ],
                    'direction' => 'to bottom left',
                    'noise' => 0.15,
                ],
                'canvas' => [
                    'width' => 1200,
                    'height' => 630,
                ],
            ],
        ]);

        return $response->getContent();
    }
}

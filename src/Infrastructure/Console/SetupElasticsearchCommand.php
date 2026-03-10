<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Elasticsearch\IndexTemplateManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:setup-elasticsearch',
    description: 'Create Elasticsearch index template for log indices',
)]
final class SetupElasticsearchCommand extends Command
{
    public function __construct(
        private readonly IndexTemplateManager $templateManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->templateManager->ensureTemplateExists();
            $io->success('Elasticsearch index template "logs-template" created/updated.');
        } catch (Throwable $e) {
            $io->error('Failed to create index template: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

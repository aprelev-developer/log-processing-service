<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\DTO\LogMessage;
use DateTimeImmutable;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:publish-test-log',
    description: 'Publish a test log message to the message bus',
)]
final class PublishTestLogCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('service', 's', InputOption::VALUE_OPTIONAL, 'Service name', 'test-service')
            ->addOption('level', 'l', InputOption::VALUE_OPTIONAL, 'Log level', 'info')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Log message', 'This is a test log message');
    }

    /**
     * @throws RandomException
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $traceId = bin2hex(random_bytes(16));
        $logMessage = new LogMessage(
            service: $input->getOption('service'),
            level: $input->getOption('level'),
            message: $input->getOption('message'),
            timestamp: (new DateTimeImmutable())->format('c'),
            traceId: $traceId,
            context: ['source' => 'cli', 'test' => true],
        );

        $this->messageBus->dispatch($logMessage);

        $io->success(sprintf(
            'Test log message published (service=%s, level=%s, traceId=%s)',
            $logMessage->service,
            $logMessage->level,
            $traceId,
        ));

        return Command::SUCCESS;
    }
}

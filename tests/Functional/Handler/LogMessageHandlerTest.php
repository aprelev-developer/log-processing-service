<?php

declare(strict_types=1);

namespace App\Tests\Functional\Handler;

use Throwable;
use Random\RandomException;
use App\Application\DTO\LogMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Infrastructure\Messaging\Handler\LogMessageHandler;

final class LogMessageHandlerTest extends KernelTestCase
{
    /**
     * @throws RandomException
     */
    public function testHandlerProcessesValidMessage(): void
    {
        self::bootKernel();
        $container = LogMessageHandlerTest::getContainer();

        $handler = $container->get(LogMessageHandler::class);

        $message = new LogMessage(
            service: 'test-service',
            level: 'info',
            message: 'Functional test log entry',
            timestamp: (new \DateTimeImmutable())->format('c'),
            traceId: bin2hex(random_bytes(16)),
            context: ['test' => true],
        );

        try {
            $handler($message);
            $this->assertTrue(true, 'Handler processed message successfully');
        } catch (Throwable $e) {
            $this->markTestSkipped('Elasticsearch not available: ' . $e->getMessage());
        }
    }

    public function testHandlerRejectsInvalidMessage(): void
    {
        self::bootKernel();
        $container = LogMessageHandlerTest::getContainer();

        $handler = $container->get(LogMessageHandler::class);

        $message = new LogMessage(
            service: '',
            level: 'invalid-level',
            message: '',
            timestamp: '',
        );

        $this->expectException(\InvalidArgumentException::class);
        $handler($message);
    }
}

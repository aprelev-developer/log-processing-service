<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Application\DTO\SearchResultDTO;
use App\Infrastructure\Elasticsearch\ElasticsearchSearchRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LogSearchControllerTest extends WebTestCase
{
    public function testSearchReturnsJson(): void
    {
        $client = static::createClient();

        $mockRepo = $this->createMock(ElasticsearchSearchRepository::class);
        $mockRepo->method('search')
            ->willReturn(new SearchResultDTO(
                total: 1,
                logs: [
                    [
                        '_id' => 'test-id',
                        '_index' => 'logs-test-2024.03.15',
                        'service' => 'test-service',
                        'level' => 'info',
                        'message' => 'Test log',
                        'timestamp' => '2024-03-15T10:00:00+00:00',
                    ],
                ],
                page: 1,
                perPage: 20,
            ));

        static::getContainer()->set(ElasticsearchSearchRepository::class, $mockRepo);

        $client->request('GET', '/api/logs/search', ['service' => 'test-service']);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(1, $data['total']);
        $this->assertCount(1, $data['logs']);
        $this->assertSame('test-service', $data['logs'][0]['service']);
    }

    public function testSearchWithInvalidPerPage(): void
    {
        $client = static::createClient();

        $mockRepo = $this->createMock(ElasticsearchSearchRepository::class);
        static::getContainer()->set(ElasticsearchSearchRepository::class, $mockRepo);

        $client->request('GET', '/api/logs/search', ['per_page' => '200']);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }
}

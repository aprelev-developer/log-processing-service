<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testLivenessEndpoint(): void
    {
        $client = HealthControllerTest::createClient();
        $client->request('GET', '/liveness');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('alive', $data['status']);
    }
}

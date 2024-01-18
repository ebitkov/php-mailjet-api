<?php

namespace ebitkov\Mailjet\Tests;

use DG\BypassFinals;
use ebitkov\Mailjet\Client;
use Mailjet\Response;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

use function PHPStan\dumpType;

abstract class MailjetApiTestCase extends TestCase
{
    /**
     * @var array<"v3"|"v3.1", Client>
     */
    private static array $clients = [];


    /**
     * @throws Exception
     */
    public function getClient(string $version = 'v3.1'): Client
    {
        if (!isset(self::$clients[$version])) {
            BypassFinals::enable();

            $mailjet = $this->createStub(\Mailjet\Client::class);

            // load fixtures
            $finder = new Finder();
            $finder
                ->in(__DIR__ . '/../fixtures/api')
                ->name('*.yaml');

            $map = [];

            foreach ($finder as $file) {
                $data = Yaml::parseFile($file);
                $request = $data['request'];
                $responseData = $data['response'];

                // configure return map
                $response = $this->createStub(Response::class);

                $response->method('success')->willReturn(2 == floor($responseData['status'] / 100));
                $response->method('getStatus')->willReturn($responseData['status']);
                $response->method('getTotal')->willReturn($responseData['body']['Total'] ?? null);
                $response->method('getBody')->willReturn($responseData['body']);
                $response->method('getData')->willReturn($responseData['body']['Data'] ?? $responseData['body']);

                $map[$request['method']][] = [
                    $request['resource'],
                    $request['args'],
                    $request['options'],
                    $response
                ];
            }

            $mailjet->method('get')->willReturnMap($map['get']);
            $mailjet->method('post')->willReturnMap($map['post']);

            self::$clients[$version] = new Client($mailjet, ['version' => $version]);
        }

        return self::$clients[$version];
    }
}

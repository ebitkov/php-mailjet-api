<?php

namespace ebitkov\Mailjet\Tests;

use DG\BypassFinals;
use ebitkov\Mailjet\Client;
use Mailjet\Response;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

abstract class MailjetApiTestCase extends TestCase
{
    private static ?Client $client = null;


    /**
     * @throws Exception
     */
    public function getClient(): Client
    {
        if (!self::$client) {
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

                $response->method('success')->willReturn($responseData['status'] == 200);
                $response->method('getStatus')->willReturn($responseData['status']);
                $response->method('getTotal')->willReturn($responseData['body']['Total']);
                $response->method('getBody')->willReturn($responseData['body']);
                $response->method('getData')->willReturn($responseData['body']['Data']);

                $map[$request['method']][] = [
                    $request['resource'],
                    $request['args'],
                    [],
                    $response
                ];
            }

            $mailjet->method('get')->willReturnMap($map['get']);

            self::$client = new Client($mailjet);
        }

        return self::$client;
    }
}

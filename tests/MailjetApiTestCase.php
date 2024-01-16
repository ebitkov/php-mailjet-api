<?php

namespace ebitkov\Mailjet\Tests;

use DG\BypassFinals;
use ebitkov\Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MailjetApiTestCase extends TestCase
{
    /**
     * @throws Exception
     */
    public function getClient(): Client
    {
        BypassFinals::enable();

        $mailjet = $this->createMock(\Mailjet\Client::class);

        $body = [
            'Count' => 1,
            'Data' => [
                [
                    "IsUnsubscribed" => true,
                    "ContactID" => 987654321,
                    "ID" => 1234567890,
                    "IsActive" => true,
                    "ListID" => 123456,
                    "ListName" => "abcdef123",
                    "SubscribedAt" => "2018-01-01T00:00:00",
                    "UnsubscribedAt" => "2018-01-01T00:00:00"
                ]
            ],
            'Total' => 1,
        ];
        $successfulListRecipientResponse = $this->createStub(Response::class);
        $successfulListRecipientResponse->method('success')->willReturn(true);
        $successfulListRecipientResponse->method('getStatus')->willReturn(200);
        $successfulListRecipientResponse->method('getTotal')->willReturn(1);
        $successfulListRecipientResponse->method('getBody')->willReturn($body);
        $successfulListRecipientResponse->method('getData')->willReturn($body['Data']);

        $getMap = [
            [
                Resources::$Listrecipient,
                ['filters' => []],
                [],
                $successfulListRecipientResponse
            ]
        ];

        $mailjet->method('get')
            ->willReturnMap($getMap);

        return new Client($mailjet);
    }
}

<?php

namespace Email\v3;

use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;
use PHPUnit\Framework\MockObject\Exception;

class SentEmailTest extends MailjetApiTestCase
{
    /**
     * @throws Exception
     */
    public function testSerialization(): void
    {
        $client = $this->getClient();

        $data = [
            'Email' => 'passenger@mailjet.com',
            'MessageID' => 1234567890987654400,
            'MessageUUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j'
        ];

        /** @var SentEmail $sentEmail */
        $sentEmail = $client->serialize($data, SentEmail::class);

        $this->assertSame('passenger@mailjet.com', $sentEmail->email);
        $this->assertSame(1234567890987654400, $sentEmail->messageId);
        $this->assertSame('1ab23cd4-e567-8901-2345-6789f0gh1i2j', $sentEmail->messageUuid);
    }
}

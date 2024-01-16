<?php

namespace ebitkov\Mailjet\Tests\Email;

use ebitkov\Mailjet\Email\Contact;
use ebitkov\Mailjet\Email\Subscription;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;
use PHPUnit\Framework\MockObject\Exception;

class SubscriptionTest extends MailjetApiTestCase
{
    /**
     * @throws Exception
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function testGetContact(): void
    {
        $client = $this->getClient();
        $result = $client->getListRecipients();
        /** @var Subscription $listRecipient */
        $listRecipient = $result->first();

        $contact = $listRecipient->getContact();

        $this->assertInstanceOf(Contact::class, $contact);
    }
}

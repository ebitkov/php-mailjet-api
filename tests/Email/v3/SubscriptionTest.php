<?php

namespace Email\v3;

use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Email\v3\ContactsList;
use ebitkov\Mailjet\Email\v3\Subscription;
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

    /**
     * @throws Exception
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function testGetContactsList(): void
    {
        $client = $this->getClient();
        $result = $client->getListRecipients();
        /** @var Subscription $listRecipient */
        $listRecipient = $result->first();

        $contact = $listRecipient->getContactsList();

        $this->assertInstanceOf(ContactsList::class, $contact);
    }
}

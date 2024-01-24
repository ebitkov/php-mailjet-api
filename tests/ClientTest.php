<?php

namespace ebitkov\Mailjet\Tests;

use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Email\v3\ContactsList;
use ebitkov\Mailjet\Email\v3\Subscription;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;
use PHPUnit\Framework\MockObject\Exception;

class ClientTest extends MailjetApiTestCase
{
    /**
     * @throws Exception
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function testGetListRecipientsWithNoFilters(): void
    {
        $mailjet = $this->getClient();
        $result = $mailjet->getListRecipients();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(2, $result->totalCount);

        $listRecipient = $result->first();
        $this->assertInstanceOf(Subscription::class, $listRecipient);
        /* todo: move to an own test case
        $this->assertSame(true, $listRecipient->isUnsubscribed());
        $this->assertSame(987654321, $listRecipient->getContactId());
        $this->assertSame(1234567890, $listRecipient->getId());
        $this->assertSame(123456, $listRecipient->getListId());
        $this->assertSame('abcdef123', $listRecipient->getListName());
        $this->assertEquals(new DateTime('2018-01-01'), $listRecipient->getSubscribedAt());
        $this->assertEquals(new DateTime('2018-01-01'), $listRecipient->getUnsubscribedAt());
        */
    }

    /**
     * @throws Exception
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function testGetContactsWithNoFilters(): void
    {
        $mailjet = $this->getClient();
        $result = $mailjet->getContacts();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(10, $result->totalCount);

        $contact = $result->first();

        $this->assertInstanceOf(Contact::class, $contact);
    }

    /**
     * @throws Exception
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function testGetContactsListsWithNoFilters(): void
    {
        $mailjet = $this->getClient();
        $result = $mailjet->getContactsLists();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(3, $result->totalCount);

        $contact = $result->first();

        $this->assertInstanceOf(ContactsList::class, $contact);
    }

    public function testGetContactDataById(): void
    {
        $client = $this->getClient();
        $result = $client->getContactDataById(1);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertSame(1, $result->getId());
        $this->assertCount(1, $result->getProperties());
    }
}

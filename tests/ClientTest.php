<?php

namespace ebitkov\Mailjet\Tests;

use DateTime;
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
        $this->assertSame(1, $result->totalCount);

        $listRecipient = $result->first();
        $this->assertInstanceOf(Subscription::class, $listRecipient);

        $this->assertSame(true, $listRecipient->isUnsubscribed);
        $this->assertSame(987654321, $listRecipient->contactId);
        $this->assertSame(1234567890, $listRecipient->id);
        $this->assertSame(123456, $listRecipient->listId);
        $this->assertSame('abcdef123', $listRecipient->listName);
        $this->assertEquals(new DateTime('2018-01-01'), $listRecipient->subscribedAt);
        $this->assertEquals(new DateTime('2018-01-01'), $listRecipient->unsubscribedAt);
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
        $this->assertSame(1, $result->totalCount);

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
        $this->assertSame(1, $result->totalCount);

        $contact = $result->first();

        $this->assertInstanceOf(ContactsList::class, $contact);
    }
}

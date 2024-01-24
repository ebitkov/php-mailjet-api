<?php

namespace ebitkov\Mailjet\Tests\Email\v3;

use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Email\v3\ContactsList;
use ebitkov\Mailjet\Email\v3\Subscription;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;

class ContactsListTest extends MailjetApiTestCase
{
    public function testGetSubscriptions(): void
    {
        $list = $this->getContactsList();
        $subscriptions = $list->getSubscriptions();

        $this->assertSame(1, $subscriptions->count());
        $this->assertInstanceOf(Subscription::class, $subscriptions->first());
        $this->assertSame(1, $subscriptions->first()->getListId());
    }

    private function getContactsList(): ContactsList
    {
        $client = $this->getClient();
        return $client->getContactsListById(1);
    }

    public function testGetSubscribedContacts(): void
    {
        $list = $this->getContactsList();
        $contacts = $list->getSubscribedContacts();

        $this->assertSame(1, $contacts->count());
        $this->assertInstanceOf(Contact::class, $contacts->first());
    }

    public function testUnsubscribeAllContacts(): void
    {
        $list = $this->getContactsList();

        $this->assertSame(
            1,
            $list->unsubscribeAllContacts()
        );
    }
}

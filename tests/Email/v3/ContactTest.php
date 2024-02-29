<?php

namespace ebitkov\Mailjet\Tests\Email\v3;

use ebitkov\Mailjet\Tests\MailjetApiTestCase;

class ContactTest extends MailjetApiTestCase
{
    public function testGetProperties()
    {
        $client = $this->getClient();
        $contact = $client->getContactById(1);

        $properties = $contact->getProperties();

        $this->assertIsArray($properties);
        $this->assertNotEmpty($properties);

        $this->assertEquals(['property_name' => 'some value'], $properties);
    }

    /**
     * Tests, if all requests for unsubscribing the contact from all its lists is working.
     * Since we are testing against a mock API, there is no way of testing, if the contact is actually unsubscribed.
     */
    public function testUnsubscribeFromAllLists(): void
    {
        $client = $this->getClient();
        $contact = $client->getContactById(1);

        $this->assertSame(
            1,
            $contact->unsubscribeFromAllLists()
        );
    }

    public function testSubscribeToLists(): void
    {
        $client = $this->getClient();
        $contact = $client->getContactById(1);
        $list = $client->getContactsListById(1);

        $response = $contact->subscribeToLists([$list], true);

        $this->assertNotNull($response);
        $this->assertTrue($response->success());
    }
}

<?php

namespace ebitkov\Mailjet\Tests\Email\v3;

use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;

class ContactTest extends MailjetApiTestCase
{
    public function testGetProperties()
    {
        $client = $this->getClient();
        $contact = $client->getContactById(1);

        $this->assertInstanceOf(Contact::class, $contact);

        $properties = $contact->getProperties();

        $this->assertIsArray($properties);
        $this->assertNotEmpty($properties);

        $this->assertEquals(['property_name' => 'some value'], $properties);
    }
}

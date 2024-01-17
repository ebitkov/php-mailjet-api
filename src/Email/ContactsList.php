<?php

namespace ebitkov\Mailjet\Email;

use DateTimeImmutable;

/**
 * Contact List objects help you organize your contacts into lists.
 *
 * @see https://dev.mailjet.com/email/reference/contacts/contact-list/
 */
final class ContactsList implements Resource
{
    public bool $isDeleted;
    public string $name;
    public string $address;
    public DateTimeImmutable $createdAt;
    public int $id;
    public int $subscriberCount;

    # todo: getSubscriber()
    # todo: getSubscribedContacts()
}
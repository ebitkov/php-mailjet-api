<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;

/**
 * Contact List objects help you organize your contacts into lists.
 *
 * @see https://dev.mailjet.com/email/reference/contacts/contact-list/
 */
final class ContactsList implements Resource
{
    use ClientAware;


    public bool $isDeleted;
    public string $name;
    public string $address;
    public DateTimeImmutable $createdAt;
    public int $id;
    public int $subscriberCount;

    /**
     * Returns all subscriptions of this list.
     *
     * @return Result<Subscription>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getSubscriptions(): Result
    {
        return $this->client->getListRecipients([
            'ContactsList' => $this->id
        ]);
    }

    /**
     * Gets all subscribed contacts to this list.
     *
     * @return Result<Contact>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getSubscribedContacts(): Result
    {
        return $this->client->getContacts([
            'ContactsList' => $this->id
        ]);
    }
}

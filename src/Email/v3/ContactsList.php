<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;
use InvalidArgumentException;

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
     * Unsubscribes all its contacts from the list.
     * Requires additional requests to get all subscribed contacts.
     *
     * @return Result<Job>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function unsubscribeAllContacts(): Result
    {
        $subscriber = $this->getSubscribedContacts();

        $contacts = [];
        foreach ($subscriber as $item) {
            $contacts[] = ['Email' => $item->getEmail()];
        }

        return $this->client->bulkManageContacts(
            $contacts,
            [
                [
                    'ListID' => $this->id,
                    'Action' => 'unsub'
                ]
            ]
        );
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

    /**
     * Subscribes a list of contacts to the list.
     *
     * @param Contact[] $contacts
     *
     * @return Result<Job>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function subscribe(array $contacts, bool $forceAdd = false): Result
    {
        $toSubscribe = [];
        foreach ($contacts as $contact) {
            if (!$contact instanceof Contact) {
                throw new InvalidArgumentException('Item of $contacts has to be instance of ' . Contact::class);
            }

            $toSubscribe[] = [
                'Email' => $contact->getEmail()
            ];
        }

        // send request
        return $this->client->bulkManageContacts(
            $toSubscribe,
            [
                [
                    'ListID' => $this->id,
                    'Action' => $forceAdd ? 'addforce' : 'addnoforce'
                ]
            ]
        );
    }

    /**
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function persist(): void
    {
        $this->client->persistContactsList($this);
    }
}

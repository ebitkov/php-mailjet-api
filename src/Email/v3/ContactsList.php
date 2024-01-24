<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;
use InvalidArgumentException;
use Mailjet\Resources;

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
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function unsubscribeAllContacts(): int
    {
        $subscriber = $this->getSubscribedContacts();

        $contacts = [];
        foreach ($subscriber as $item) {
            $contacts[] = ['Email' => $item->getEmail()];
        }

        if (!empty($contacts)) {
            // send request
            $result = $this->client->post(
                Resources::$ContactManagemanycontacts,
                [
                    'body' => [
                        'Contacts' => $contacts,
                        'ContactsLists' => [
                            [
                                'ListID' => $this->id,
                                'Action' => 'unsub'
                            ]
                        ]
                    ]
                ],
                [
                    'version' => 'v3'
                ]
            );

            return $result->success() ? 1 : 0;
        }

        return -1;
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
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function subscribe(array $contacts, bool $forceAdd = false): int
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
        $result = $this->client->post(
            Resources::$ContactManagemanycontacts,
            [
                'body' => [
                    'Contacts' => $toSubscribe,
                    'ContactsLists' => [
                        [
                            'ListID' => $this->id,
                            'Action' => $forceAdd ? 'addforce' : 'addnoforce'
                        ]
                    ]
                ]
            ],
            [
                'version' => 'v3'
            ]
        );

        return $result->success() ? 1 : 0;
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

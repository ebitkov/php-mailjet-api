<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeInterface;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;

/**
 * Contacts need to be subscribed to a contact list,
 * in order to receive marketing emails directed to the respective list.
 * @see https://dev.mailjet.com/email/reference/contacts/subscriptions/
 */
final class Subscription implements Resource
{
    use ClientAware;

    private bool $isUnsubscribed;
    private int $contactId;
    private int $id;
    private int $listId;
    private string $listName;
    private ?DateTimeInterface $subscribedAt;
    private ?DateTimeInterface $unsubscribedAt;


    public function isUnsubscribed(): bool
    {
        return $this->isUnsubscribed;
    }

    public function setIsUnsubscribed(bool $isUnsubscribed): self
    {
        $this->isUnsubscribed = $isUnsubscribed;
        return $this;
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function setContactId(int $contactId): self
    {
        $this->contactId = $contactId;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getListId(): int
    {
        return $this->listId;
    }

    public function setListId(int $listId): self
    {
        $this->listId = $listId;
        return $this;
    }

    public function getListName(): string
    {
        return $this->listName;
    }

    public function setListName(string $listName): self
    {
        $this->listName = $listName;
        return $this;
    }

    public function getSubscribedAt(): ?DateTimeInterface
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(?DateTimeInterface $subscribedAt): self
    {
        $this->subscribedAt = $subscribedAt;
        return $this;
    }

    public function getUnsubscribedAt(): ?DateTimeInterface
    {
        return $this->unsubscribedAt;
    }

    public function setUnsubscribedAt(DateTimeInterface|string|null $unsubscribedAt): self
    {
        if ('' === $unsubscribedAt) {
            $this->unsubscribedAt = null;
        } else {
            $this->unsubscribedAt = $unsubscribedAt;
        }
        return $this;
    }


    /**
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContact(): ?Contact
    {
        return $this->client?->getContactById($this->contactId);
    }

    /**
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContactsList(): ?ContactsList
    {
        return $this->client?->getContactsListById($this->listId);
    }
}

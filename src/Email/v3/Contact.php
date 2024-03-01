<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\Email\v3\Filter\SubscriptionFilters;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;
use InvalidArgumentException;
use Mailjet\Resources;
use Mailjet\Response;
use Symfony\Component\Serializer\Attribute\Groups;

final class Contact implements Resource
{
    use ClientAware;


    private bool $propertiesFetched = false;


    private ?bool $isExcludedFromCampaign = null;

    private ?string $name = null;

    private ?DateTimeImmutable $createdAt = null;

    private ?int $deliveredCount = null;

    private ?string $email = null;

    private ?DateTimeImmutable $exclusionFromCampaignsUpdatedAt = null;

    private ?int $id = null;

    private ?bool $isOptInPending = null;

    private ?bool $isSpamComplaining = null;

    private ?DateTimeImmutable $lastActivityAt = null;

    private ?DateTimeImmutable $lastUpdateAt = null;

    #[Groups('properties')]
    private array $properties = [];


    public function isExcludedFromCampaign(): bool
    {
        return $this->isExcludedFromCampaign ?? false;
    }

    public function setIsExcludedFromCampaign(bool $isExcludedFromCampaign): self
    {
        $this->isExcludedFromCampaign = $isExcludedFromCampaign;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDeliveredCount(): ?int
    {
        return $this->deliveredCount;
    }

    public function setDeliveredCount(int $deliveredCount): self
    {
        $this->deliveredCount = $deliveredCount;
        return $this;
    }

    public function getExclusionFromCampaignsUpdatedAt(): ?DateTimeImmutable
    {
        return $this->exclusionFromCampaignsUpdatedAt;
    }

    public function setExclusionFromCampaignsUpdatedAt(DateTimeImmutable $exclusionFromCampaignsUpdatedAt): self
    {
        $this->exclusionFromCampaignsUpdatedAt = $exclusionFromCampaignsUpdatedAt;
        return $this;
    }

    public function isOptInPending(): ?bool
    {
        return $this->isOptInPending;
    }

    public function setIsOptInPending(bool $isOptInPending): self
    {
        $this->isOptInPending = $isOptInPending;
        return $this;
    }

    public function isSpamComplaining(): ?bool
    {
        return $this->isSpamComplaining;
    }

    public function setIsSpamComplaining(bool $isSpamComplaining): self
    {
        $this->isSpamComplaining = $isSpamComplaining;
        return $this;
    }

    public function getLastActivityAt(): ?DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(DateTimeImmutable $lastActivityAt): self
    {
        $this->lastActivityAt = $lastActivityAt;
        return $this;
    }

    public function getLastUpdateAt(): ?DateTimeImmutable
    {
        return $this->lastUpdateAt;
    }

    public function setLastUpdateAt(DateTimeImmutable $lastUpdateAt): self
    {
        $this->lastUpdateAt = $lastUpdateAt;
        return $this;
    }

    public function getProperties(): array
    {
        if (!$this->propertiesFetched) {
            $this->fetchProperties();
        }

        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function fetchProperties(): self
    {
        $this->client?->getContactDataById($this->getId(), $this);
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

    /**
     * Unsubscribes the contact from all its lists.
     *
     * @return int<-1, 1> 1 on success, 0 on error, -1 if nothing changed
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function unsubscribeFromAllLists(): int
    {
        // get all subscribed lists
        $subscribedLists = $this->getSubscribedLists();

        $lists = [];
        foreach ($subscribedLists as $item) {
            $lists[] = [
                'ListID' => $item->getListId(),
                'Action' => 'unsub'
            ];
        }

        if (!empty($lists)) {
            // send request
            $result = $this->client->post(
                Resources::$ContactManagemanycontacts,
                [
                    'body' => [
                        'Contacts' => [
                            [
                                'Email' => $this->getEmail()
                            ]
                        ],
                        'ContactsLists' => $lists
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
     * @return Result<Subscription>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getSubscribedLists(): Result
    {
        return $this->client->getContactsListsByContact($this->getId());
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param ContactsList[] $contactsLists
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function subscribeToLists(array $contactsLists, bool $force = false): ?Response
    {
        $lists = [];
        foreach ($contactsLists as $list) {
            if (!$list instanceof ContactsList) {
                throw new InvalidArgumentException(
                    sprintf(
                        'First parameter of %s::subscribeToLists($contactsLists, ...) must an array of %s items.',
                        self::class,
                        ContactsList::class
                    )
                );
            }
            $lists[] = [
                'ListID' => $list->id,
                'Action' => $force ? 'addforce' : 'addnoforce'
            ];
        }

        if (!empty($lists)) {
            return $this->client->manageContactsLists(
                $this->id,
                $lists
            );
        }
        return null;
    }

    /**
     * Checks, if a Contact is subscribed to a list.
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function isSubscribedToList(ContactsList $list): bool
    {
        $subs = $this->client->getListRecipients([
            SubscriptionFilters::CONTACT => $this->id,
            SubscriptionFilters::CONTACTS_LIST => $list->id,
            SubscriptionFilters::UNSUB => false
        ]);

        return $subs->count() === 1;
    }
}

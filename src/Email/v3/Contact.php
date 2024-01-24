<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\Email\Resource;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
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

    # todo: getSubscribedLists() (/contact/{contact_ID}/getcontactslists)
}

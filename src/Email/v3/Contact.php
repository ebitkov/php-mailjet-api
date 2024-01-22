<?php

namespace ebitkov\Mailjet\Email\v3;

use DateTimeImmutable;
use ebitkov\Mailjet\Email\Resource;

final class Contact implements Resource
{
    private bool $isExcludedFromCampaign;
    private string $name;
    private DateTimeImmutable $createdAt;
    private int $deliveredCount;
    private string $email;
    private DateTimeImmutable $exclusionFromCampaignsUpdatedAt;
    private int $id;
    private bool $isOptInPending;
    private bool $isSpamComplaining;
    private DateTimeImmutable $lastActivityAt;
    private DateTimeImmutable $lastUpdateAt;

    public function isExcludedFromCampaign(): bool
    {
        return $this->isExcludedFromCampaign ?? false;
    }

    public function setIsExcludedFromCampaign(bool $isExcludedFromCampaign): self
    {
        $this->isExcludedFromCampaign = $isExcludedFromCampaign;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDeliveredCount(): int
    {
        return $this->deliveredCount;
    }

    public function setDeliveredCount(int $deliveredCount): self
    {
        $this->deliveredCount = $deliveredCount;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getExclusionFromCampaignsUpdatedAt(): DateTimeImmutable
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

    public function isOptInPending(): bool
    {
        return $this->isOptInPending;
    }

    public function setIsOptInPending(bool $isOptInPending): self
    {
        $this->isOptInPending = $isOptInPending;
        return $this;
    }

    public function isSpamComplaining(): bool
    {
        return $this->isSpamComplaining;
    }

    public function setIsSpamComplaining(bool $isSpamComplaining): self
    {
        $this->isSpamComplaining = $isSpamComplaining;
        return $this;
    }

    public function getLastActivityAt(): DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(DateTimeImmutable $lastActivityAt): self
    {
        $this->lastActivityAt = $lastActivityAt;
        return $this;
    }

    public function getLastUpdateAt(): DateTimeImmutable
    {
        return $this->lastUpdateAt;
    }

    public function setLastUpdateAt(DateTimeImmutable $lastUpdateAt): self
    {
        $this->lastUpdateAt = $lastUpdateAt;
        return $this;
    }

    # todo: getSubscribedLists() (/contact/{contact_ID}/getcontactslists)
    # todo: getProperties()
}

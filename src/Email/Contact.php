<?php

namespace ebitkov\Mailjet\Email;

use DateTimeImmutable;

final class Contact implements Resource
{
    public bool $isExcludedFromCampaign;
    public string $name;
    public DateTimeImmutable $createdAt;
    public int $deliveredCount;
    public string $email;
    public DateTimeImmutable $exclusionFromCampaignsUpdatedAt;
    public int $id;
    public bool $isOptInPending;
    public bool $isSpamComplaining;
    public DateTimeImmutable $lastActivityAt;
    public DateTimeImmutable $lastUpdateAt;
}

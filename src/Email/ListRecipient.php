<?php

namespace ebitkov\Mailjet\Email;

use DateTimeInterface;

final class ListRecipient
{
    public bool $isUnsubscribed;
    public int $contactId;
    public int $id;
    public int $listId;
    public string $listName;
    public DateTimeInterface $subscribedAt;
    public DateTimeInterface $unsubscribedAt;
}

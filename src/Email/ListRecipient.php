<?php

namespace ebitkov\Mailjet\Email;

use DateTimeInterface;
use ebitkov\Mailjet\ClientAware;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;

final class ListRecipient implements Resource
{
    use ClientAware;

    public bool $isUnsubscribed;
    public int $contactId;
    public int $id;
    public int $listId;
    public string $listName;
    public DateTimeInterface $subscribedAt;
    public DateTimeInterface $unsubscribedAt;

    /**
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContact(): ?Contact
    {
        return $this->client?->getContactById($this->contactId);
    }

    # todo: getContactsList()
}

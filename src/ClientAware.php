<?php

namespace ebitkov\Mailjet;

trait ClientAware
{
    private ?Client $client = null;


    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}

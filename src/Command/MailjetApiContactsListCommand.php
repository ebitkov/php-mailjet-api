<?php

namespace ebitkov\Mailjet\Command;

use ebitkov\Mailjet\MailjetApiCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mailjet:api:contacts_list', description: 'Sends requests to the Mailjet API /contactslist endpoint.')]
final class MailjetApiContactsListCommand extends MailjetApiCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $client = $this->getClient();

        $filter = [];

        $res = $client->getContactsLists($filter);

        return $this->displayResult($res);
    }
}

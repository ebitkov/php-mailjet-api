<?php

namespace ebitkov\Mailjet\Command;

use ebitkov\Mailjet\MailjetApiCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mailjet:api:contact', description: 'Sends requests to the Mailjet API /contact endpoint.')]
final class MailjetApiContactCommand extends MailjetApiCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $client = $this->getClient();

        $filter = [];

        if (null !== ($contactsListId = $input->getOption('ContactsList'))) {
            $filter['ContactsList'] = $contactsListId;
        }

        $res = $client->getContacts($filter);

        return $this->displayResult($res);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'ContactsList',
                null,
                InputOption::VALUE_OPTIONAL,
                'Retrieves only contacts that are part of this Contact List ID.'
            );
    }
}

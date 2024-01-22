<?php

namespace ebitkov\Mailjet\Command;

use ebitkov\Mailjet\MailjetApiCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mailjet:api:contact', description: 'Sends requests to the Mailjet API /contact endpoint.')]
class MailjetApiContactsCommand extends Command
{
    use MailjetApiCommand;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = $this->getClient();

        $filter = [];

        if (null !== ($contactsListId = $input->getOption('ContactsList'))) {
            $filter['ContactsList'] = $contactsListId;
        }

        $res = $client->getContacts($filter);

        if ($input->getOption('raw')) {
            dump($res->rawData);
        } else {
            $this->resultToTable($io, $res);
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'ContactsList',
                null,
                InputOption::VALUE_OPTIONAL,
                'Retrieves only contacts that are part of this Contact List ID.'
            )
            ->addOption(
                'raw',
                'r',
                InputOption::VALUE_NONE,
                'If true, the raw JSON response is rendered.'
            );
    }
}

<?php

namespace ebitkov\Mailjet\Command;

use ebitkov\Mailjet\MailjetApiCommand;
use ebitkov\Mailjet\Result;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mailjet:api:contact_data', description: 'Sends requests to the Mailjet API /contactdata endpoint.')]
final class MailjetApiContactDataCommand extends MailjetApiCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $client = $this->getClient();

        $filter = [];

        if (null !== ($id = $input->getOption('ID'))) {
            $contact = $client->getContactDataById($id);
            $res = new Result(1, [$contact]);
        }

        return $this->displayResult($res);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'ID',
                null,
                InputOption::VALUE_OPTIONAL,
                'Unique numeric ID for the contact, whose properties you want to retrieve.'
            );
    }
}

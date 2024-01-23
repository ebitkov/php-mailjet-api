<?php

namespace ebitkov\Mailjet\Command;

use ebitkov\Mailjet\MailjetApiCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mailjet:api:list_recipient', description: 'Sends requests to the Mailjet API /listrecipient endpoint.')]
final class MailjetApiListRecipientCommand extends MailjetApiCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $client = $this->getClient();

        $filter = [];

        if (null !== ($isUnsubscribed = $input->getOption('Unsub'))) {
            $isUnsubscribed = $isUnsubscribed === 'true';
            $filter['Unsub'] = $isUnsubscribed;
        }

        $res = $client->getListRecipients($filter);

        return $this->displayResult($res);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'Unsub',
                null,
                InputOption::VALUE_OPTIONAL,
                'When true, will retrieve only unsubscribed list recipients (IsUnsubscribed=true). ' .
                'When false, unsubscribed recipients will be excluded from the response.'
            );
    }
}

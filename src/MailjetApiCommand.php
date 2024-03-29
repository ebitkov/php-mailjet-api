<?php

namespace ebitkov\Mailjet;

use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;

abstract class MailjetApiCommand extends Command
{
    protected InputInterface $input;
    protected OutputInterface $output;

    protected ?Client $client = null;


    protected function getClient(): Client
    {
        if (!$this->client) {
            (new Dotenv())->load(dirname(__DIR__) . '/.env');

            $mailjet = new \Mailjet\Client(
                $_ENV['MAILJET_API_KEY'],
                $_ENV['MAILJET_API_SECRET']
            );

            $this->client = new Client($mailjet);
        }

        return $this->client;
    }

    protected function displayResult(Result $result)
    {
        if ($this->input->getOption('raw')) {
            dump($this->client->lastResponse->getData());
        } else {
            $this->resultToTable($result);
        }

        return Command::SUCCESS;
    }

    /**
     * @throws ReflectionException
     */
    protected function resultToTable(Result $result): void
    {
        $io = new SymfonyStyle($this->input, $this->output);

        if ($result->count()) {
            // get headers
            $ref = new \ReflectionClass($result->first());
            $refProperties = $ref->getProperties();

            $properties = [];
            foreach ($refProperties as $property) {
                $properties[] = $property->getName();
            }

            $rows = [];
            foreach ($result as $item) {
                $row = [];
                foreach ($properties as $property) {
                    $getter = 'get' . ucfirst($property);
                    if (method_exists($item, $getter)) {
                        $val = $item->$getter();
                    } else {
                        if (method_exists($item, $property)) {
                            $val = $item->$property();
                        } else {
                            # todo: Remove not accessible properties completely from result table
                            # they are mostly internal and therefor not relevant in this context
                            $val = $item->$property ?? '< not accessible >';
                        }
                    }

                        if ($val instanceof \DateTimeInterface) {
                            $val = $val->format(\DateTimeInterface::RFC3339);
                        }

                        if (is_array($val)) {
                            $val = json_encode($val);
                        }

                        if (null === $val) {
                            $val = '< null >';
                        }

                        if (true === $val) {
                            $val = '< true >';
                        }

                        if (false === $val) {
                            $val = '< false >';
                        }

                        if ('' === $val) {
                            $val = '< empty string >';
                        }

                        $row[] = $val;
                }
                $rows[] = $row;
            }

            $io->table($properties, $rows);
        } else {
            $io->warning('Empty result received');
        }
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'raw',
                'r',
                InputOption::VALUE_NONE,
                'If true, the raw JSON response is rendered.'
            );
    }
}

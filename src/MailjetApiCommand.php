<?php

namespace ebitkov\Mailjet;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;

trait MailjetApiCommand
{
    private function getClient(): Client
    {
        (new Dotenv())->load(dirname(__DIR__) . '/.env');

        $mailjet = new \Mailjet\Client(
            $_ENV['MAILJET_API_KEY'],
            $_ENV['MAILJET_API_SECRET']
        );

        return new Client($mailjet);
    }

    /**
     * @throws \ReflectionException
     */
    private function resultToTable(SymfonyStyle $io, Result $result): void
    {
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
                            $val = $item->$property;
                        }
                    }

                    if ($val instanceof \DateTimeInterface) {
                        $val = $val->format(\DateTimeInterface::RFC3339);
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
}

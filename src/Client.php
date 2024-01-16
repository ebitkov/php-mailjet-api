<?php

namespace ebitkov\Mailjet;

use ebitkov\Mailjet\Email\Contact;
use ebitkov\Mailjet\Email\ListRecipient;
use ebitkov\Mailjet\Filter\ContactFilter;
use ebitkov\Mailjet\Filter\ListRecipientFilters;
use ebitkov\Mailjet\Serializer\NameConverter\UpperCamelCaseToLowerCamelCaseNameConverter;
use GuzzleHttp\Exception\ConnectException;
use InvalidArgumentException;
use Mailjet\Resources;
use Mailjet\Response;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Client
{
    private Serializer $serializer;

    public function __construct(
        private readonly \Mailjet\Client $mailjet,
        private readonly int $maxRetries = 3,
        private readonly int $secondsToWaitOnTooManyRequests = 10
    ) {
        $objectNormalizer = new ObjectNormalizer(
            new ClassMetadataFactory(new AttributeLoader()),
            nameConverter: new UpperCamelCaseToLowerCamelCaseNameConverter(),
            propertyTypeExtractor: new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()])
        );
        $this->serializer = new Serializer([
            $objectNormalizer,
            new DateTimeNormalizer()
        ]);
    }


    /**
     * Retrieve a list of all contacts.
     * Includes information about contact status and creation / activity timestamps.
     *
     * @see https://dev.mailjet.com/email/reference/contacts/contact#v3_get_contact
     *
     * @param array<ContactFilter::*, mixed> $filters
     * @return Result<Contact>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContacts(array $filters = []): Result
    {
        $response = $this->get(
            Resources::$Contact,
            [
                'filters' => (new ContactFilter())->resolve($filters)
            ]
        );

        return $this->serializeResult($response, Contact::class);
    }

    /**
     * Sends a GET request.
     *
     * @param array<int<0, 1>, string> $resource
     * @param array<string|int, mixed> $args
     * @param array<string|int, mixed> $options
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function get(array $resource, array $args = [], array $options = []): Response
    {
        return $this->sendRequest('get', $resource, $args, $options);
    }

    /**
     * Sends a request.
     * Additional logic to handle connection or "too many request" errors.
     *
     * @param "get"|"post"|"put"|"delete" $method
     * @param array<int<0, 1>, string> $resource
     * @param array<string|int, mixed> $args
     * @param array<string|int, mixed> $options
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    private function sendRequest(
        string $method,
        array $resource,
        array $args = [],
        array $options = [],
        int $currentTry = 0
    ): Response {
        if (!in_array($method, ['get', 'post', 'put', 'delete'])) {
            throw new InvalidArgumentException(
                'Invalid value for $method! Allowed are "get", "post", "put" or "delete".'
            );
        }

        do {
            try {
                /** @var Response $response */
                $response = $this->mailjet->$method($resource, $args, $options);

                if ($response->success()) {
                    return $response;
                } else {
                    /**
                     * Zu viele Anfragen
                     */
                    if ($response->getStatus() == 429) {
                        // Warte etwas ab
                        sleep($this->secondsToWaitOnTooManyRequests);
                        // und sende die Anfrage erneut
                        return $this->sendRequest($method, $resource, $args, $options, $currentTry++);
                    }

                    throw new RequestFailed($response);
                }
            } catch (ConnectException $connectException) {
                /**
                 * Fange eventuelle Verbindungsfehler ab und versuche es erneut, falls möglich.
                 */
                $currentTry++;
            }
        } while ($currentTry < $this->maxRetries);

        throw new RequestAborted($currentTry + 1, $response ?? null, $connectException);
    }

    /**
     * Serializes successful responses into objects.
     *
     * @template T of object
     *
     * @param class-string<T> $type
     * @return Result<T>
     */
    public function serializeResult(Response $response, string $type): Result
    {
        /** @var list<T> $data */
        $data = [];
        foreach ($response->getData() as $item) {
            $data[] = $this->serializer->denormalize(
                $item,
                $type,
                'object',
                [
                    AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true
                ]
            );
        }

        /** @var Result<T> $result */
        $result = new Result(
            $response->getTotal(),
            $data
        );
        return $result;
    }

    /**
     * @param array<ListRecipientFilters::*, mixed> $filters
     *
     * @return Result<ListRecipient>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getListRecipients(array $filters = []): Result
    {
        $response = $this->get(
            Resources::$Listrecipient,
            [
                'filters' => (new ListRecipientFilters())->resolve($filters)
            ]
        );

        return $this->serializeResult($response, ListRecipient::class);
    }
}

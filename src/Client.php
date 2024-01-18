<?php

namespace ebitkov\Mailjet;

use ebitkov\Mailjet\Email\EmailList;
use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Email\v3\ContactsList;
use ebitkov\Mailjet\Email\v3\Filter\ContactFilter;
use ebitkov\Mailjet\Email\v3\Filter\ContactsListFilters;
use ebitkov\Mailjet\Email\v3\Filter\SubscriptionFilters;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Email\v3\Subscription;
use ebitkov\Mailjet\Serializer\NameConverter\MailjetNameConverter;
use ebitkov\Mailjet\Serializer\Normalizer\MailjetEmailNormalizer;
use GuzzleHttp\Exception\ConnectException;
use InvalidArgumentException;
use Mailjet\Resources;
use Mailjet\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Client
{
    public const API_VERSION = 'version';
    public const MAX_RETRIES = 'max_retries';
    public const SECONDS_TO_WAIT_ON_TOO_MANY_REQUESTS = 'seconds_to_wait_on_too_many_requests';

    private Serializer $serializer;
    private ValidatorInterface $validator;

    /**
     * @var array{version: string, max_retries: int, seconds_to_wait_on_too_many_requests: int}
     */
    private array $settings;

    /**
     * @param \Mailjet\Client $mailjet
     * @param array<self::*, mixed> $settings
     */
    public function __construct(
        private readonly \Mailjet\Client $mailjet,
        array $settings = []
    ) {
        // configure
        $this->initSettings($settings);

        // setup serializer
        $objectNormalizer = new ObjectNormalizer(
            new ClassMetadataFactory(new AttributeLoader()),
            nameConverter: new MailjetNameConverter(),
            propertyTypeExtractor: new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]),
            defaultContext: [
                AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true,
                AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => false,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]
        );

        $this->serializer = new Serializer([
            new MailjetEmailNormalizer(),
            $objectNormalizer,
            new DateTimeNormalizer(),
        ]);

        // setup validator
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    /**
     * @param array<self::*, mixed> $settings
     * @return void
     */
    private function initSettings(array $settings): void
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            /*
             * The version is mainly used for the Send API.
             * All other endpoints will automatically fall back to v3, since they don't support v3.1,
             */
            self::API_VERSION => 'v3.1',
            self::MAX_RETRIES => 3,
            self::SECONDS_TO_WAIT_ON_TOO_MANY_REQUESTS => 10,
        ]);

        $resolver->setAllowedValues(self::API_VERSION, ['v3', 'v3.1']);
        $resolver->setAllowedTypes(self::MAX_RETRIES, 'int');
        $resolver->setAllowedTypes(self::SECONDS_TO_WAIT_ON_TOO_MANY_REQUESTS, 'int');

        $resolver->setIgnoreUndefined();

        $this->settings = $resolver->resolve($settings);
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
            ],
            [
                'version' => 'v3' # /contact only supports v3
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
     * @param array<int<0, 2>, string|array<string, string>> $resource
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
                        sleep($this->settings[self::SECONDS_TO_WAIT_ON_TOO_MANY_REQUESTS]);
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
        } while ($currentTry < $this->settings[self::MAX_RETRIES]);

        throw new RequestAborted($currentTry + 1, $response ?? null, $connectException);
    }

    /**
     * Serializes successful responses into objects.
     *
     * @template T of Resource
     *
     * @param class-string<T> $type
     * @return T|Result<T>|null
     */
    public function serializeResult(Response $response, string $type, bool $singleResult = false): Resource|Result|null
    {
        $data = $response->getData();

        // Special rule for Send API
        if (isset($data['Sent'])) {
            $data = $data['Sent'];
        }

        /** @var list<T> $resources */
        $resources = [];
        foreach ($data as $item) {
            $resources[] = $this->serialize($item, $type);
        }

        /** @var Result<T> $result */
        $result = new Result(
            $response->getTotal(),
            $resources
        );

        if ($singleResult) {
            if ($result->count() > 0) {
                return $result->first();
            }
            return null;
        }

        return $result;
    }

    /**
     * @template T of Resource
     *
     * @param array<string, mixed> $data
     * @param class-string<T> $type
     *
     * @return T
     */
    public function serialize(array $data, string $type): Resource
    {
        $object = $this->serializer->denormalize($data, $type, 'object');

        if (in_array(ClientAware::class, class_uses($object))) {
            $object->setClient($this);
        }

        return $object;
    }

    /**
     * @param array<SubscriptionFilters::*, mixed> $filters
     *
     * @return Result<Subscription>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getListRecipients(array $filters = []): Result
    {
        $response = $this->get(
            Resources::$Listrecipient,
            [
                'filters' => (new SubscriptionFilters())->resolve($filters)
            ],
            [
                'version' => 'v3' # /listrecipient only supports v3
            ]
        );

        return $this->serializeResult($response, Subscription::class);
    }

    /**
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContactById(int $id): ?Contact
    {
        $response = $this->get(
            Resources::$Contact,
            [
                'id' => $id
            ],
            [
                'version' => 'v3' # /contact/{ID} only supports v3
            ]
        );

        return $this->serializeResult($response, Contact::class, true);
    }

    /**
     * Retrieve details for all contact lists - name, subscriber count, creation timestamp, deletion status.
     *
     * @see https://dev.mailjet.com/email/reference/contacts/contact-list/#v3_get_contactslist
     *
     * @param array<ContactsListFilters::*, mixed> $filters
     * @return Result<ContactsList>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function getContactsLists(array $filters = []): Result
    {
        $response = $this->get(
            Resources::$Contactslist,
            [
                'filters' => (new ContactsListFilters())->resolve($filters)
            ],
            [
                'version' => 'v3' # /contactslist only supports v3
            ]
        );

        return $this->serializeResult($response, ContactsList::class);
    }

    /**
     * Retrieve details for a specific contact list.
     *
     * @see https://dev.mailjet.com/email/reference/contacts/contact-list/#v3_get_contactslist_list_ID
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getContactsListById(int $id): ?ContactsList
    {
        $response = $this->get(
            Resources::$Contactslist,
            [
                'id' => $id
            ],
            [
                'version' => 'v3' # /contactslist/{ID} only supports v3
            ]
        );

        return $this->serializeResult($response, ContactsList::class, true);
    }

    /**
     * Sends email messages via the Send API.
     * Automatically adjusts the request corresponding to the configured version (v3 / v3.1).
     *
     * @return Result<SentEmail>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     * @throws ExceptionInterface
     */
    public function sendEmail(EmailList $emailList): Result
    {
        // validate data
        $violations = $this->validate($emailList);

        if (0 !== count($violations)) {
            // return the first violation as an exception
            throw new InvalidArgumentException($violations->get(0)->getMessage());
        }

        $response = $this->post(
            Resources::$Email,
            [
                'body' => $this->normalize($emailList, 'send_api')
            ],
            [
                'version' => $this->settings[self::API_VERSION]
            ]
        );

        return $this->serializeResult($response, SentEmail::class);
    }

    public function validate(object $object): ConstraintViolationListInterface
    {
        return $this->validator->validate($object);
    }

    /**
     * Sends a POST request.
     *
     * @param array<int<0, 2>, string|array<string, string>> $resource
     * @param array<string, mixed> $args
     * @param array<string, mixed> $options
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function post(array $resource, array $args = [], array $options = []): Response
    {
        return $this->sendRequest('post', $resource, $args, $options);
    }

    /**
     * @return array<string, mixed>
     * @throws ExceptionInterface
     * @internal
     */
    public function normalize(object $data, string $format = null): array
    {
        return $this->serializer->normalize(
            $data,
            $format,
            [
                'mj_api_version' => $this->settings[self::API_VERSION]
            ]
        );
    }

    public function getApiVersion(): string
    {
        return $this->settings[self::API_VERSION];
    }
}

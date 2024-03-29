<?php

namespace ebitkov\Mailjet;

use ebitkov\Mailjet\Email\Resource;
use ebitkov\Mailjet\Email\v3\Body\BulkManageContactsBody;
use ebitkov\Mailjet\Email\v3\Body\BulkManageContactsListBody;
use ebitkov\Mailjet\Email\v3\Body\ManageContactsListsBody;
use ebitkov\Mailjet\Email\v3\Contact;
use ebitkov\Mailjet\Email\v3\ContactsList;
use ebitkov\Mailjet\Email\v3\Filter\ContactFilter;
use ebitkov\Mailjet\Email\v3\Filter\ContactsListFilters;
use ebitkov\Mailjet\Email\v3\Filter\SubscriptionFilters;
use ebitkov\Mailjet\Email\v3\Job;
use ebitkov\Mailjet\Email\v3\Message;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Email\v3\Subscription;
use ebitkov\Mailjet\Email\v3dot1\EmailList;
use ebitkov\Mailjet\Email\v3dot1\SentMessageList;
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
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
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
    public ?Response $lastResponse = null;
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
                AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => false,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            ]
        );

        $this->serializer = new Serializer([
            new MailjetEmailNormalizer(),
            $objectNormalizer,
            new ArrayDenormalizer(),
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
                $this->lastResponse = $response;

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
     * @param bool $singleResult Should the result stripped to a single object? Will always take the first object!
     * @param ?object $objectToPopulate An object to populate. Will force the return of a single result.
     *
     * @template T of Resource
     *
     * @param class-string<T> $type
     * @return T|Result<T>|null
     */
    public function serializeResult(
        Response $response,
        string $type,
        bool $singleResult = false,
        object $objectToPopulate = null
    ): Resource|Result|null {
        $data = $response->getData();

        // Special rule for Send API
        if (isset($data['Sent'])) {
            $data = $data['Sent'];
        } else {
            if (isset($data['Messages'])) {
                $data = $data['Messages'];
            }
        }

        /** @var list<T> $resources */
        $resources = [];
        foreach ($data as $item) {
            // configure context
            $context = [];
            if ($objectToPopulate) {
                $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $objectToPopulate;
            }

            // serialize
            $resource = $this->serialize($item, $type, $context);

            // return single result, if requested
            if ($singleResult || $objectToPopulate) {
                return $resource;
            }

            // collect serialized resources
            $resources[] = $resource;
        }

        /**
         * Empty result
         */
        if ($singleResult) {
            return null;
        }

        /** @var Result<T> $result */
        $result = new Result(
            $response->getTotal(),
            $resources
        );

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
    public function serialize(array $data, string $type, array $context = []): Resource
    {
        $object = $this->serializer->denormalize($data, $type, 'object', $context);

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
     * Creates a new contact list.
     * @see https://dev.mailjet.com/email/reference/contacts/contact-list/#v3_post_contactslist
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function createContactsList(string $name, bool $isDeleted = false): ContactsList
    {
        $list = new ContactsList($name, $isDeleted);
        $this->persistContactsList($list);
        return $list;
    }

    /**
     * Creates or updates a contacts list.
     * Auto-selects the corresponding method (POST or UPDATE).
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function persistContactsList(ContactsList $list): void
    {
        # todo: Support UPDATE

        $response = $this->post(
            Resources::$Contactslist,
            [
                'body' => [
                    'Name' => $list->name,
                    'IsDeleted' => $list->isDeleted ?? false,
                ]
            ],
            [
                'version' => 'v3'
            ]
        );

        $this->serializeResult(
            $response,
            ContactsList::class,
            objectToPopulate: $list
        );
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
     * Sends email messages via the Send API.
     * Automatically adjusts the request corresponding to the configured version (v3 / v3.1).
     *
     * @see https://dev.mailjet.com/email/reference/send-emails/
     *
     * @return Result<SentEmail|SentMessageList>
     *
     * @throws RequestFailed
     * @throws RequestAborted
     * @throws ExceptionInterface
     */
    public function send(EmailList|Message $messages): Result
    {
        // validate data
        $violations = $this->validate($messages);

        if (0 !== count($violations)) {
            // return the first violation as an exception
            throw new InvalidArgumentException($violations->get(0)->getMessage());
        }

        // detect API version to use
        $version = 'v3.1';
        $returnResource = SentMessageList::class;
        if ($messages instanceof Message) {
            $version = 'v3';
            $returnResource = SentEmail::class;
        }

        $response = $this->post(
            Resources::$Email,
            [
                'body' => $this->normalize($messages, 'send_api')
            ],
            [
                'version' => $version
            ]
        );

        return $this->serializeResult($response, $returnResource);
    }

    public function validate(object $object): ConstraintViolationListInterface
    {
        return $this->validator->validate($object);
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

    /**
     * Get the contact property values relating to a specific contact.
     * @see https://dev.mailjet.com/email/reference/contacts/contact-properties#v3_get_contactdata_contact_ID
     *
     * @param int $contactId Unique numeric ID for the contact, whose properties you want to retrieve.
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getContactDataById(int $contactId, Contact $contact = new Contact()): Contact
    {
        $response = $this->get(
            Resources::$Contactdata,
            [
                'id' => $contactId
            ],
            [
                'version' => 'v3', // contactdata/ only supports v3
            ]
        );

        if ($response->getCount() == 1) {
            // set contact id
            $contact->setId($contactId);

            // transform key-value pairs from result to assoc array
            $properties = [];
            foreach ($response->getData()[0]['Data'] as $property) {
                $properties[$property['Name']] = $property['Value'];
            }

            // set properties
            $contact->setProperties($properties);
        }

        return $contact;
    }

    /**
     * Retrieve all contact lists for a specific contact.
     * @see https://dev.mailjet.com/email/reference/contacts/subscriptions#v3_get_contact_contact_ID_getcontactslists
     *
     * @param int $contactId Unique numeric ID of the contact you want to retrieve the contact lists for.
     *
     * @return Result<Subscription>
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function getContactsListsByContact(int $contactId): Result
    {
        $response = $this->get(
            Resources::$ContactGetcontactslists,
            [
                'id' => $contactId
            ],
            [
                'version' => 'v3'
            ]
        );

        return $this->serializeResult($response, Subscription::class);
    }

    /**
     * Manage multiple contacts by adding, removing or unsubscribing them from multiple contact lists.
     * @see https://dev.mailjet.com/email/reference/contacts/bulk-contact-management/
     *
     * @throws RequestFailed
     * @throws RequestAborted
     */
    public function bulkManageContacts(array $contacts, array $lists)
    {
        $response = $this->post(
            Resources::$ContactManagemanycontacts,
            [
                'body' => (new BulkManageContactsBody())->resolve([
                    'Contacts' => $contacts,
                    'ContactsLists' => $lists
                ])
            ],
            [
                'version' => 'v3'
            ]
        );

        return $this->serializeResult($response, Job::class);
    }

    /**
     * Manage the presence and subscription status of a contact for multiple contact lists. Select the contact lists,
     * as well as the desired action to be performed on each one - add, remove or unsub.
     * The contact should already be present in the global contact list.
     *
     * @see https://dev.mailjet.com/email/reference/contacts/subscriptions/#v3_post_contact_contact_ID_managecontactslists
     *
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function manageContactsLists(int $contactId, array $lists): Response
    {
        return $this->post(
            Resources::$ContactManagecontactslists,
            [
                'id' => $contactId,
                'body' => (new ManageContactsListsBody())->resolve([
                    'ContactsLists' => $lists
                ])
            ],
            [
                'version' => 'v3'
            ]
        );
    }

    private function isV3(): bool
    {
        return $this->settings[self::API_VERSION] == 'v3';
    }
}

<?php

namespace CurrencyCloud\EntryPoint;

use CurrencyCloud\Model\Contact;
use CurrencyCloud\Model\Contacts;
use CurrencyCloud\Model\Pagination;
use DateTime;
use stdClass;

class ContactsEntryPoint extends AbstractEntryPoint
{

    /**
     * @param null|string $loginId
     */
    public function createResetToken($loginId = null)
    {
        $this->request(
            'POST',
            'contacts/reset_token/create',
            [],
            [
                'login_id' => $loginId
            ]
        );
    }

    /**
     * @param Contact $contact
     *
     * @return Contact
     */
    public function create(Contact $contact)
    {
        $response = $this->request('POST', 'contacts/create', [], $this->convertContactToRequest($contact));

        return $this->createContactFromResponse($response);
    }

    /**
     * @param Contact $contact
     * @param bool $convertForFind
     *
     * @return array
     */
    private function convertContactToRequest(Contact $contact, $convertForFind = false)
    {
        $dateOfBirth = $contact->getDateOfBirth();
        $common = [
            'account_id' => $contact->getAccountId(),
            'account_name' => $contact->getAccountName(),
            'first_name' => $contact->getFirstName(),
            'last_name' => $contact->getLastName(),
            'email_address' => $contact->getEmailAddress(),
            'phone_number' => $contact->getPhoneNumber(),
            'your_reference' => $contact->getYourReference(),
            'login_id' => $contact->getLoginId(),
            'status' => $contact->getLocale(),
            'timezone' => $contact->getTimezone(),
            'date_of_birth' => (null === $dateOfBirth) ? null : $dateOfBirth->format(DateTime::ISO8601)
        ];
        if ($convertForFind) {
            return $common;
        }
        return $common + [
            'mobile_phone_number' => $contact->getMobilePhoneNumber()
        ];
    }

    /**
     * @param stdClass $response
     *
     * @return Contact
     */
    private function createContactFromResponse(stdClass $response)
    {
        $contact = new Contact();
        $contact->setLoginId($response->login_id)
            ->setYourReference($response->your_reference)
            ->setFirstName($response->first_name)
            ->setLastName($response->last_name)
            ->setAccountId($response->account_id)
            ->setAccountName($response->account_name)
            ->setStatus($response->status)
            ->setPhoneNumber($response->phone_number)
            ->setMobilePhoneNumber($response->mobile_phone_number)
            ->setLocale($response->locale)
            ->setTimezone($response->time_zone)
            ->setDateOfBirth(new DateTime($response->date_of_birth))
            ->setCreatedAt(new DateTime($response->created_at))
            ->setUpdatedAt(new DateTime($response->updated_at));

        $this->setIdProperty($contact, $response->id);
        return $contact;
    }

    /**
     * @param Contact|null $contact
     * @param Pagination|null $pagination
     *
     * @return Contacts
     */
    public function find(Contact $contact = null, Pagination $pagination = null)
    {
        if (null === $contact) {
            $contact = new Contact();
        }
        if (null === $pagination) {
            $pagination = new Pagination();
        }
        $response =
            $this->request(
                'POST',
                'contacts/find',
                $this->convertContactToRequest($contact) + $this->convertPaginationToRequest($pagination)
            );

        $contacts = [];
        foreach ($response->contacts as $contact) {
            $contacts[] = $this->createContactFromResponse($contact);
        }
        return new Contacts($contacts, $this->createPaginationFromResponse($response));
    }

    /**
     * @param string $id
     *
     * @return Contact
     */
    public function retrieve($id)
    {
        $response = $this->request('GET', sprintf('contacts/%s', $id));

        return $this->createContactFromResponse($response);
    }

    /**
     * @param string $id
     * @param Contact $contact $contact
     *
     * @return Contact
     */
    public function update($id, Contact $contact)
    {
        $response = $this->request(
            'POST',
            sprintf('contacts/%s', $id),
            [],
            $this->convertContactToRequest($contact)
        );

        return $this->createContactFromResponse($response);
    }

    /**
     * @return Contact
     */
    public function current()
    {
        $response = $this->request('GET', 'contacts/current');

        return $this->createContactFromResponse($response);
    }
}

<?php

namespace Easychimp;

use Mailchimp\Mailchimp;

class Easychimp
{

    /** @var Mailchimp */
    protected $api;

    public function __construct($apiKey)
    {
        $this->api = new Mailchimp($apiKey);
    }

    /**
     * @param $listId
     * @param $email
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function isSubscribed($listId, $email)
    {
        try {
            $result = $this->api->get('lists/'.$listId.'/members/'.$this->hashEmail($email));

            return $result->get('status') == 'subscribed';
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * @param string    $listId
     * @param string    $email
     * @param string    $firstName
     * @param string    $lastName
     * @param array     $interests  Array of interest ids
     * @param array     $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function subscribe(
        $listId,
        $email,
        $firstName = null,
        $lastName = null,
        array $interests = null,
        array $extras = []
    ) {
        $mergeFields = [];
        if ($firstName !== null) {
            $mergeFields['FNAME'] = $firstName;
        }

        if ($lastName !== null) {
            $mergeFields['LNAME'] = $lastName;
        }
        $data = array_merge([
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => (object) $mergeFields
        ], $extras);

        if ($interests !== null) {
            $data['interests'] = (object) array_flip($interests);
        }

        $result = $this->api->post('lists/'.$listId.'/members', $data);

        return $result->has('id') && strlen($result->get('id')) > 0;
    }

    /**
     * @param $listId
     * @param $email
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return \Illuminate\Support\Collection Info about the subscriber
     */
    public function subscriberInfo($listId, $email)
    {
        try {
            return $this->api->get('lists/'.$listId.'/members/'.$this->hashEmail($email));
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                throw new EmailAddressNotSubscribed;
            }

            throw $e;
        }
    }

    /**
     * @param string    $listId
     * @param string    $email
     * @param string    $firstName
     * @param string    $lastName
     * @param array     $interests  Array of interest ids
     * @param array     $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return boolean
     */
    public function updateSubscriber(
        $listId,
        $email,
        $firstName = null,
        $lastName = null,
        array $interests = null,
        array $extras = []
    ) {
        try {
            $data = $extras;
            $mergeFields = [];
            if ($firstName !== null) {
                $mergeFields['FNAME'] = $firstName;
            }
            if ($lastName !== null) {
                $mergeFields['LNAME'] = $lastName;
            }

            if (count($mergeFields) > 0) {
                $data['merge_fields'] = (object) $mergeFields;
            }
            if ($interests !== null) {
                $data['interests'] = (object) array_flip($interests);
            }

            $result = $this->api->patch('lists/'.$listId.'/members/'.$this->hashEmail($email));

            return $result->has('id') && strlen($result->get('id')) > 0;
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                throw new EmailAddressNotSubscribed;
            }

            throw $e;
        }
    }

    /**
     * @param $listId
     * @param $email
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return boolean
     */
    public function unsubscribe($listId, $email)
    {
        try {
            $result = $this->api->patch('lists/'.$listId.'/members/'.$this->hashEmail($email), [
                'status' => 'unsubscribed'
            ]);

            return $result->has('id') && strlen($result->get('id')) > 0;
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                throw new EmailAddressNotSubscribed;
            }

            throw $e;
        }
    }

    /**
     * @param $listId
     *
     * @throws \Exception
     *
     * @return array
     */
    public function interestCategories($listId)
    {
        $result = $this->api->get('lists/'.$listId.'/interest-categories');

        return $result->get('categories');
    }

    /**
     * @param $listId
     * @param $interestCategoryId
     *
     * @throws \Exception
     *
     * @return array
     */
    public function interests($listId, $interestCategoryId)
    {
        $result = $this->api->get('lists/'.$listId.'/interest-categories/'.$interestCategoryId.'/interests');

        return $result->get('interests');
    }

    /**
     * @param $email
     * @return string
     */
    protected function hashEmail($email)
    {
        return md5(strtolower($email));
    }
}
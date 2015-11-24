<?php

namespace Easychimp;

use Mailchimp\Mailchimp;

class MailingList
{

    /** @var Mailchimp */
    protected $api;

    /** @var string */
    protected $id;

    /** @var string */
    protected $support;

    public function __construct(Mailchimp $api, Support $support, $id)
    {
        $this->api = $api;
        $this->support = $support;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param $email
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function isSubscribed($email)
    {
        try {
            $result = $this->api->get('lists/'.$this->id().'/members/'.$this->support->hashEmail($email));

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
     * @param string    $email
     * @param string    $firstName
     * @param string    $lastName
     * @param array     $interests  Array where keys are interest ids and values are boolean
     * @param array     $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function subscribe(
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
            $data['interests'] = (object) $interests;
        }

        $result = $this->api->post('lists/'.$this->id().'/members', $data);

        return $result->has('id') && strlen($result->get('id')) > 0;
    }

    /**
     * @param $email
     *
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return \Illuminate\Support\Collection Info about the subscriber
     */
    public function subscriberInfo($email)
    {
        try {
            return $this->api->get('lists/'.$this->id().'/members/'.$this->support->hashEmail($email));
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                throw new EmailAddressNotSubscribed;
            }

            throw $e;
        }
    }

    /**
     * @param string    $email
     * @param string    $firstName
     * @param string    $lastName
     * @param array     $interests  Array where keys are interest ids and values are boolean
     * @param array     $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return boolean
     */
    public function updateSubscriber(
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
                $data['interests'] = (object) $interests;
            }

            $result = $this->api->patch('lists/'.$this->id().'/members/'.$this->support->hashEmail($email));

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
     * @param $email
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     *
     * @return boolean
     */
    public function unsubscribe($email)
    {
        try {
            $result = $this->api->patch('lists/'.$this->id().'/members/'.$this->support->hashEmail($email), [
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
     * @throws \Exception
     *
     * @return array
     */
    public function interestCategories()
    {
        $result = $this->api->get('lists/'.$this->id().'/interest-categories');

        return $result->get('categories');
    }

    /**
     * @param $interestCategoryId
     *
     * @throws \Exception
     *
     * @return array
     */
    public function interests($interestCategoryId)
    {
        $result = $this->api->get('lists/'.$this->id().'/interest-categories/'.$interestCategoryId.'/interests');

        return $result->get('interests');
    }
}
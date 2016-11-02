<?php

namespace Easychimp;

use Illuminate\Support\Collection;
use Mailchimp\Mailchimp;

class MailingList
{

    /** @var Mailchimp */
    protected $api;

    /** @var string */
    protected $id;

    /** @var string */
    protected $support;

    public function __construct(Mailchimp $api, Support $support, string $id)
    {
        $this->api = $api;
        $this->support = $support;
        $this->id = $id;
    }

    public function id() : string
    {
        return $this->id;
    }

    /**
     * @throws \Exception
     */
    public function exists() : bool
    {
        try {
            $this->api->get('lists/'.$this->id());
        } catch (\Exception $e) {
            if (starts_with($e->getMessage(), '{')) {
                $json = json_decode($e->getMessage());
                if ($json->status == 404) {
                    return false;
                }
            }
            
            throw $e;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function isSubscribed(string $email) : bool
    {
        try {
            $result = $this->api->get('lists/'.$this->id().'/members/'.$this->support->hashEmail($email));

            // a "pending" subscriber has been added to the list but hasn't yet confirmed their memebership
            return $result->get('status') == 'subscribed' || $result->get('status') == 'pending';
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * @param string        $email
     * @param string        $firstName
     * @param string        $lastName
     * @param array|object  $interests  Properties/Keys are interest ids and values are boolean
     * @param array         $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws \Exception
     */
    public function subscribe(
        string $email,
        string $firstName = null,
        string $lastName = null,
        $interests = null,
        array $extras = []
    ) : bool {
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
     * @see http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members_subscriber_hash
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     */
    public function subscriberInfo(string $email) : Collection
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
     * Updates a subscriber if the email address is already
     * on the list, or create the subscriber
     *
     * @param string        $email
     * @param string        $firstName
     * @param string        $lastName
     * @param array|object  $interests  Properties/Keys are interest ids and values are boolean
     * @param array         $extras     Additional fields to be passed to the Mailchimp API
     *
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     */
    public function updateSubscriber(
        string $email,
        string $firstName = null,
        string $lastName = null,
        $interests = null,
        array $extras = []
    ) : bool {
        $data = array_merge([
            'status_if_new' => 'subscribed',
            'email_address' => $email
        ], $extras);
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

        $result = $this->api->put('lists/'.$this->id().'/members/'.$this->support->hashEmail($email), $data);

        return $result->has('id') && strlen($result->get('id')) > 0;
    }

    /**
     * @throws EmailAddressNotSubscribed
     * @throws \Exception
     */
    public function unsubscribe(string $email) : bool
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
     */
    public function interestCategories() : array
    {
        $result = $this->api->get('lists/'.$this->id().'/interest-categories');

        return $result->get('categories');
    }

    /**
     * @throws \Exception
     */
    public function interests(string $interestCategoryId) : array
    {
        $result = $this->api->get('lists/'.$this->id().'/interest-categories/'.$interestCategoryId.'/interests');

        return $result->get('interests');
    }
}

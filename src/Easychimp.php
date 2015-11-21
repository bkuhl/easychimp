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
     * @param $listId
     * @param $email
     * @param array $extras
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function subscribe($listId, $email, $firstName = null, $lastName = null)
    {
        $mergeFields = [];
        if (!is_null($firstName)) {
            $mergeFields['FNAME'] = $firstName;
        }

        if (!is_null($lastName)) {
            $mergeFields['LNAME'] = $lastName;
        }

        $result = $this->api->post('lists/'.$listId.'/members', [
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => (object) $mergeFields
        ]);

        return $result->has('id') && strlen($result->get('id')) > 0;
    }

    /**
     * @param $listId
     * @param $email
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function unsubscribe($listId, $email)
    {
        try {
            $result = $this->api->delete('lists/'.$listId.'/members/'.$this->hashEmail($email));

            return $result->count() == 0;
        } catch (\Exception $e) {
            # Email address isn't on this list
            if (str_contains($e->getMessage(), 'Resource Not Found')) {
                return true;
            }

            throw $e;
        }
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
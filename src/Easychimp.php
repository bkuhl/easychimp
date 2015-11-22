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
     * @param $firstName
     * @param $lastName
     * @param array $interests
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
        array $interests = null
    ) {
        $mergeFields = [];
        if ($firstName !== null) {
            $mergeFields['FNAME'] = $firstName;
        }

        if ($lastName !== null) {
            $mergeFields['LNAME'] = $lastName;
        }
        $data = [
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => (object) $mergeFields
        ];

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
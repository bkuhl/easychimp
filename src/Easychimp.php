<?php

namespace Easychimp;

use Mailchimp\Mailchimp;

class Easychimp
{

    /** @var Mailchimp */
    protected $api;

    public function __construct(Mailchimp $api)
    {
        $this->api = $api;
    }

    /**
     * @return \Easychimp\MailingList
     */
    public function mailingList(string $id)
    {
        return new MailingList($this->api, new Support(), $id);
    }

    /**
     * Determine if an API key is valid
     *
     * @throws InvalidApiKey
     * @throws \Exception
     */
    public function validateKey()
    {
        try {
            $this->api->get('');
        } catch (\Exception $e) {
            if (starts_with($e->getMessage(), '{')) {
                $json = json_decode($e->getMessage());
                if ($json->status == 401) {
                    throw new InvalidApiKey($json->detail);
                }
            }

            throw $e;
        }
    }
}